<?php

class glab_monthly_html {

    static function generate_calendar($data) {
        require_once dirname(dirname(__FILE__)) . '/helper/glab_html_helper.php';
        extract($data);
        if(!isset($_POST['from_calendar_ajax'])){
        echo '<link type="text/css" rel="stylesheet" href="' . plugins_url('glab_clinic/assets/css/calender.css') . '" />';
        echo '<link type="text/css" rel="stylesheet" href="' . plugins_url('glab_clinic/assets/css/barChart.css') . '" />';
        self::generate_heading($data);
        ?>
        <div id="calendar_view_main" style="visibility:visible;width:95%;  margin: 0pt 0pt 0pt 14px;">
        <?php } ?>
            <?php echo self::draw_calendar($data); ?>
        <?php if(!isset($_POST['from_calendar_ajax'])){ ?>
        </div>
        <div id="calendar_view_main1" style="display: none;width:95%;  margin: 0pt 0pt 0pt 14px;">
            <div class="app_error" id="app_error13"></div>
            <div id="divAppointment1" style="display:none; overflow:hidden;" ></div>
        </div>
        <?php } ?>
        <?php
    }

    static function generate_bar_chart($appointments, $daySlot, $rooms) {
        if (!isset($appointments) || empty($appointments))
            return '';
        
        $working_hour = glab_convert_helper::convert_to_working_hour($daySlot);
        $rooms_with_appts = self::prepare_rooms_appts($appointments, $rooms);
        
        $fromleft = self::calculate_bar_left_position(count($rooms_with_appts));
        $bar = '<div class="bargraph" style="width: 100%; position:relative; margin-top:31px;">';

        // generate bars
        $bar.='<div style="color:#000000;font-family:Impact,Arial, Helvetica, sans-serif;font-size:21px;width:40px; margin-top: 31px;margin-left:0px;font-weight:normal;position:absolute;text-align:center;">' . count($appointments) . '<div style="font-size:6px;font-weight:normal;">Appointments</label></div></div>
                <ul class="bars" style="right:148px;"><li  style="left:' . $fromleft . 'px;height:12px;background-color:#ffffff;font-size:6px;color:#000000;font-family: Impact,Arial,Helvetica,sans-serif;    font-size: 9px; font-weight: normal;">&#37Full</li>';
        $barCount = 1;
        $positionBar = $fromleft + 26;
        $Totpercentage = 0;

        foreach ($rooms_with_appts as $room) {
            if ($working_hour > 0) {
                $percentage = ($room['num_of_appts'] * 100) / $working_hour;
            } else {
                $percentage = 0;
                $Totpercentage = $Totpercentage + $percentage;
            }

            if (($percentage < 5) and ( $percentage > 0)) {
                $display_percentage = ceil($percentage / 5) * 5;
            } else if (($percentage > 95) and ( $percentage < 100)) {
                $display_percentage = floor($percentage / 5) * 5;
            } else {
                $display_percentage = round($percentage / 5) * 5;
            }

            if ($percentage > 0) {
                $bar.='<li  style="height: ' . (($percentage / 2) + 8) . 'px; _height: ' . (($percentage / 2) + 1) . 'px; left:' . $positionBar . 'px;background-color:#FAFAFA;font-size:6px;color:#000000">' . $display_percentage . '&#37;</li>';
            }

            if ($display_percentage > 5 AND $display_percentage <= 30) {
                $bar.='<li class="bar' . $barCount . ' bluebar" style="height: ' . ($percentage / 2) . 'px; _height: ' . (($percentage / 2) - 2) . 'px; left:' . $positionBar . 'px;">&nbsp;</li>';
            } elseif ($display_percentage > 30 AND $display_percentage <= 50) {
                $bar.='<li class="bar' . $barCount . ' greenbar" style="height: ' . ($percentage / 2) . 'px;left:' . $positionBar . 'px;">&nbsp;</li>';
            } elseif ($display_percentage > 50 AND $display_percentagee <= 75) {
                $bar.='<li class="bar' . $barCount . ' yellowbar" style="height: ' . ($percentage / 2) . 'px;left:' . $positionBar . 'px;">&nbsp;</li>';
            } elseif ($display_percentage > 75 AND $display_percentage <= 99) {
                $bar.='<li class="bar' . $barCount . ' orangebar" style="height: ' . ($percentage / 2) . 'px;left:' . $positionBar . 'px;">&nbsp;</li>';
            } elseif ($display_percentage == 100) {
                $bar.='<li class="bar' . $barCount . ' redbar" style="height: ' . ($percentage / 2) . 'px;left:' . $positionBar . 'px;">&nbsp;</li>';
            } else {
                $bar.='<li class="bar' . $barCount . ' " style="height: ' . ($percentage / 2) . 'px;left:' . $positionBar . 'px;">&nbsp;</li>';
            }

            $barCount++;
            $positionBar = $positionBar + 14;
        }

        // end of bar & label start
        $bar.='</ul><br/><ul class="label">';
        $barCount = count($rooms_with_appts);

        // calculate total hours for the day
        $totalhoursfortheday = 0;
        foreach ($rooms_with_appts as $room) {
            $bar.='<li style="font-family:Impact,Arial,Helvetica,sans-serif;font-weight:normal;">' . $barCount . '</li> ';
            $totalhoursfortheday += floatval($room['appt_service_duration']);
            $barCount--;
        }

        // calculate percentage of appointment
        $totalpercentage = self::calcuate_total_percentage($totalhoursfortheday, count($rooms_with_appts), $working_hour, $rooms_with_appts);
        $bar.='<li style="background-color:#696969;width:1px;">&nbsp;</li>
                <li style="font-size:3px;color:#696969;">&nbsp;</li></ul>
                <div style="background-color:#000000;color:#ffffff;position:absolute;width:73%;height:25px;font-family:Impact,Arial, Helvetica, sans-serif;font-size:21px;right:0;top:-36px;" ><div style="float:left;clear:right;width:70px;text-align:left;">&nbsp;'.$totalpercentage.'&#37;</div><div style="color:#ffffff;font-family:Impact,Arial, Helvetica, sans-serif;font-size:8px;line-height: 11px;height:21px;top:1px;position:absolute;right:2px;">Room <div style="">Capacity&nbsp;</div></div>
                </div>';

	return $bar; 
    }
    
    static function calcuate_total_percentage($totalhoursfortheday, $num_of_rooms, $working_hour, $rooms_with_appts){
        $totalpercentage = 0;
        //echo $totalhoursfortheday.'<br/>';
        if ($working_hour > 0){
            // TODO: conver working hour to minute
            $totalpercentage = ($totalhoursfortheday * 100) / (count($rooms_with_appts) * ($working_hour*60));
        }else{
            $totalpercentage = 0;
        }
        if(($totalpercentage<5) and ($totalpercentage>0)){
            $totalpercentage=ceil($totalpercentage/5)*5;
        }
        else if(($totalpercentage>90) and ($totalpercentage<100)){
            $totalpercentage=floor($totalpercentage/5)*5;
        }
        else {
            $totalpercentage=round($totalpercentage/5)*5;
        }
        
        return $totalpercentage;
    }

    static function calculate_bar_left_position($num_of_rooms) {
        $fromleft = 25;

        switch ($num_of_rooms) {
            case '1':
                $fromleft = 109;
                break;

            case '2':
                $fromleft = 95;
                break;

            case '3':
                $fromleft = 81;
                break;

            case '4':
                $fromleft = 67;
                break;

            case '5':
                $fromleft = 53;
                break;

            case '6':
                $fromleft = 39;
                break;

            case '7':
                $fromleft = 25;
                break;
        }

        return $fromleft;
    }

    static function prepare_rooms_appts($appointments, $rooms) {
        
        $room_array = array();
        foreach ($rooms as $room) {
            $total_room_appts = self::find_room_appts($room['id'], $appointments);
            $room['num_of_appts'] = $total_room_appts['number'];
            $room['appt_service_duration'] = $total_room_appts['appt_service_duration'];
            $room_array[] = $room;
        }
        return $room_array;
    }

    static function find_room_appts($room_id, $appointments) {
        $output = array();
        $num_of_appts = 0;
        $service_total_duration = 0;
        
        foreach ($appointments as $appt) {
            if ($room_id == $appt['room_id']) {
                $num_of_appts++;
                $service_total_duration+=$appt['service_duration'];
            }
        }
        $output['number'] = $num_of_appts;
        $output['appt_service_duration'] = $service_total_duration;
        return $output;
    }

    static function generate_heading($args) {
        extract($args);
        ?>
        <div class="cal_header_opt" id="calendar-head-option" >
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="26%" align="left">
                        <div id="cal_view_type">
                            <span class="view_left_cursor">
                                <a class="prev-calendar" href='<?php echo $_SERVER['PHP_SELF'] . '?page=gc_service_schedule'; ?>'  data-month="<?php echo $recent_month[0]; ?>" data-year="<?php echo $recent_month[1]; ?>" data-view_type="<?php echo $prev_view_type; ?>">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" /></a>
                            </span> 
                            <span id="calendar_view_type" style="display: inline-block; width: 75px; text-align: center; vertical-align: top;"><?= strtoupper($view_type); ?></span>
                            <span class="view_right_cursor">
                                <a class="next-calendar"  href="<?php echo $_SERVER['PHP_SELF'] . '?page=gc_service_schedule'; ?>" data-month="<?php echo $recent_month[0]; ?>" data-year="<?php echo $recent_month[1]; ?>" data-view_type="<?php echo $next_view_type; ?>">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" />
                                </a>
                            </span>
                        </div> 
                        <input type="hidden" name="slide_view_id" value="" id="slide_view_id" /> 
                        <span id="asd" style="float: right;"></span>
                        <span id="asdf" style="float: right; display: none;">
                            <div id="top_v_slider">
                                <a onclick="delete_appointment11(document.getElementById('slide_view_id').value), slide_s_view('top_v_slider', '3');">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" />
                                </a>
                                View Appointment
                                <a onclick="Appointment_view_slider(), slide_s_view('top_v_slider', '1');">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" /></a>
                            </div>
                            <div id="top_v_slider1" style="display: none;">
                                <a onclick="showAppDetails(document.getElementById('slide_view_id').value), slide_s_view('top_v_slider', '0');">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" />
                                </a>
                                Add New Appointment
                                <a onclick="edit_an_appointment(document.getElementById('slide_view_id').value), slide_s_view('top_v_slider', '2');">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" />
                                </a>
                            </div>
                            <div id="top_v_slider2" style="display: none;">
                                <a onclick="Appointment_view_slider(), slide_s_view('top_v_slider', '1');">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" />
                                </a>
                                Edit Appointment
                                <a onclick="delete_appointment11(document.getElementById('slide_view_id').value), slide_s_view('top_v_slider', '3');
                                        ;">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" />
                                </a>
                            </div>
                            <div id="top_v_slider3" style="display: none;">
                                <a onclick="edit_an_appointment(document.getElementById('slide_view_id').value), slide_s_view('top_v_slider', '2');">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" />
                                </a>
                                Delete Appointment
                                <a onclick="showAppDetails(document.getElementById('slide_view_id').value), slide_s_view('top_v_slider', '0');">
                                    <img src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" />
                                </a>
                            </div> 
                        </span>
                    </td>
                    <td width="50%" align="center">
                        <div id="cal_prac_view" style="display: inline-block; vertical-align: top;">
                            <table style="display: inline-block; margin-top: -10px;">
                                <td style="font-size: 17px;">&nbsp;</td>
                                <td style="vertical-align: top; padding-left: 40px;">
                                    <select class="filter-practitioner" style="font-size: 15px; width: 350px; text-align: center;">
                                        <option value="0">Select Practitioner</option>
                                        <?= glab_html_helper::prac_options($practitioners); ?>
                                    </select>
                                </td>
                            </table>
                        </div>
                    </td>
                    <td align="right" width="25%">
                        <div id="cal_opt">
                            <span class="cal_left_cursor">
                                <a class="prev-number" data-view_type="monthly" data-month="<?php echo $previous_month[0]; ?>" data-year="<?php echo $previous_month[1]; ?>">
                                    <img id="<?= $previous_month[0] . '-' . $previous_month[1]; ?>" src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" />
                                </a>
                            </span> 
                            <span id="curr_month_year" style="padding-left: 20px; padding-right: 20px; vertical-align: top;"><?= $recent_month_in_text[0] . ' ' . $recent_month_in_text[1]; ?></span>
                            <span class="cal_right_cursor">
                                <a class="next-number" data-view_type="monthly" data-month="<?php echo $next_month[0]; ?>" data-year="<?php echo $next_month[1]; ?>" >
                                    <img id="<?= $next_month[0] . '-' . $next_month[1]; ?>" src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" />
                                </a>
                            </span>
                        </div>
                        <div id="week_opt" style="display: none; float: right;">
                            <span class="cal_left_cursor">
                                <a class="prev-number" data-view_type="weekly" data-month="<?php echo $previous_month[0]; ?>" data-year="<?php echo $previous_month[1]; ?>" href="<?php echo $_SERVER['PHP_SELF'] . '?page=gc_service_schedule&month=' . $previous_month[0] . '&year=' . $previous_month[1]; ?>">
                                    <img id="<?= $previous_month[0] . '-' . $previous_month[1]; ?>" src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" />
                                </a>
                            </span> 
                            <span id="week_opt_number" style="padding-left: 20px; padding-right: 20px; vertical-align: top;"><?= $recent_month[0] . ' ' . $recent_month[1]; ?></span>
                            <span class="cal_right_cursor">
                                <a class="next-number" data-view_type="weekly" data-month="<?php echo $next_month[0]; ?>" data-year="<?php echo $next_month[1]; ?>" >
                                    <img id="<?= $next_month[0] . '-' . $next_month[1]; ?>" src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" />
                                </a>
                            </span>
                        </div>
                        <div id="daily_opt" style="display: none; float: right;">
                            <span class="cal_left_cursor">
                                <a class="prev-number"  data-view_type="daily" data-month="<?php echo $previous_month[0]; ?>" data-year="<?php echo $previous_month[1]; ?>" >
                                    <img id="<?= $previous_month[0] . '-' . $previous_month[1]; ?>" src="<?php echo plugins_url('glab_clinic/assets/images/nav-left.png'); ?>" alt="" />
                                </a>
                            </span> 
                            <span id="daily_opt_number" style="padding-left: 20px; padding-right: 20px; vertical-align: top;"><?= $recent_month[0] . ' ' . $recent_month[1]; ?></span>
                            <span class="cal_right_cursor">
                                <a class="next-number" data-view_type="daily" data-month="<?php echo $next_month[0]; ?>" data-year="<?php echo $next_month[1]; ?>" >
                                    <img id="<?= $next_month[0] . '-' . $next_month[1]; ?>" src="<?php echo plugins_url('glab_clinic/assets/images/nav-right.png'); ?>" alt="" />
                                </a>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>
            <input type="hidden" id="view_type" value="<?= $view_type; ?>" />
        </div>
        <?php
    }

    static function draw_calendar($args) {
        extract($args);        /* draw table */        $calendar = '<table cellpadding="0" cellspacing="0" class="calendar b_common">';        /* table headings */
        $headings = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');        $calendar.= '<tr class="calendar-row"><td class="calendar_week_head" style="width:50px;">Week</td><td class="calendar-day-head">' . implode('</td><td class="calendar-day-head">', $headings) . '</td></tr>';        /* days and weeks vars now ... */
        $running_day = date('N', mktime(0, 0, 0, $month, 1, $year)); //mdified_ w        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));        $days_in_this_week = 1;        $day_counter = 0;        $dates_array = array();        $prev_month_day = date('t', mktime(0, 0, 0, $month - 1, 1, $year));        $prev_day_print = $prev_month_day + 1 - $running_day;
        /* row for week one */        $calendar.= '<tr class="calendar-row">';        /* print "blank" days until the first of the current week */
        $recent_date_ = $year . '-' . $month . '-01';        $recent_week_date_ = $year . '-' . $month . '-' . date('d');
        $calendar.='<td class="calendar_week" style=""><input type="hidden" id="day" value="' . date('d', strtotime($recent_week_date_)) . '"  /><input type="hidden" id="week_num" value="' . date('W', strtotime($recent_week_date_)) . '" /><input type="hidden" id="month" value="' . date('n', strtotime($recent_date_)) . '" /><input type="hidden" id="dailyLabel" value="' . date('d-F', strtotime($recent_week_date_)) . '"/>			<input type="hidden" id="year" value="' . date('Y', strtotime($recent_date_)) . '" /><a href="" style="color:#fff;font-weight:bold;">' . date('W', strtotime($recent_date_)) . '</a></td>';
        for ($x = 1; $x < $running_day; $x++): //ordignal 0            $calendar.= '<td class="calendar-day-np">' . ++$prev_day_print . '</td>';            $days_in_this_week++;        endfor;
        for ($list_day = 1; $list_day <= $days_in_month; $list_day++):            $selectedHDate = $year . '-' . $month . '-' . $list_day;            $closedStatus = 0;            $calendar.= '<td id="' . $list_day . '" class="calendar-day" style="min-width:130px!important;overflow:hidden;">';            $selectedHDate = $year . '-' . str_pad($month,2,'0',STR_PAD_LEFT) . '-' . str_pad($list_day,2,'0',STR_PAD_LEFT);            $selector_date_format=$year . '-' . str_pad($month,2,'0',STR_PAD_LEFT) . '-' . str_pad($list_day,2,'0',STR_PAD_LEFT);            if (isset($appointments[$selector_date_format]))                $dailyAppt = count($appointments[$selector_date_format]);            else                $dailyAppt = 0;            if ($dailyAppt > 0) {                $calendar.= '<div class="day-number" style="font-weight:bold;">' . $list_day;                $calendar.='</div>';                $dayChar = strtolower(date('D', strtotime($selectedHDate)));                $calendar.=self::generate_bar_chart($appointments[$selector_date_format], $clinic_schedule[$dayChar]['value'], $rooms);            } else {                $dayChar = strtolower(date('D', strtotime($selectedHDate)));                $calendar.= '<div class="day-number">' . $list_day;
                if ((in_array($dayChar, $regular_off_days) != false) OR ( in_array($selectedHDate, $monthly_off_days) != false)) {
                    $calendar.='<div style="position:relative; width:80%; margin:auto; color:#FF0000;  font-weight:bold; text-align:center;  height:auto; Font-size:12px; border:0px solid black; clear:both; ">Closed</div>';
                }
                $calendar.='</div>';
            }
            $calendar.= '</td>';
            if ($running_day == 7)://original 6
                $calendar.= '</tr>';
                if (($day_counter + 1) != $days_in_month):
                    $calendar.= '<tr class="calendar-row">';
                endif;
                $running_day = 0; //original -1
                $days_in_this_week = 0;
                $next_day_ = $list_day + 1; //original +2
                $recent_date_ = $year . '-' . $month . '-' . $next_day_;
                $calendar.='<td class="calendar_week"><a href="" style="color:#fff;font-weight:bold;">' . date('W', strtotime($recent_date_)) . '</a></td>';
            endif;
            $days_in_this_week++;
            $running_day++;
            $day_counter++;
        endfor;
        /* finish the rest of the days in the week */
        if ($days_in_this_week < 8) {
            for ($x = 1; $x <= (8 - $days_in_this_week); $x++):
                $calendar.= '<td class="calendar-day-np">' . $x . '</td>';
            endfor;
        }
        /* final row */
        $calendar.= '</tr>';
        /* end the table */
        $calendar.= '</table>';
        /* all done, return result */
        return $calendar;
    }

}
