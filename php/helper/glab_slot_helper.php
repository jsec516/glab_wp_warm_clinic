<?php

class glab_slot_helper {

    static function get_appt_end_time($start_time, $appt_duration) {
        $start_array = explode(':', $start_time);
        $start_time_in_minute = ($start_array[0] * 60) + $start_array[1];
        $end_time_in_minute = $start_time_in_minute + $appt_duration;
        $end_hour = floor($end_time_in_minute / 60);
        $end_minute = $end_time_in_minute % 60;
        $end_time = str_pad($end_hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($end_minute, 2, '0', STR_PAD_LEFT);
        return $end_time;
    }

    static function get_full_n_half($hours) {
        $data = array();
        $half_an_hour = null;
        $full_hour = null;

        if ($hours > 11) {
            $hours-=12;
            if ($hours < 10) {
                $hours+=0;
                $hours = '0' . $hours;
            }
            $half_an_hour = "0" . ($hours + 1) . '-00-pm';

            if ($hours == 1) {
                $full_hour = '01-30-pm';
            } else {
                $full_hour = $hours . '-30-pm';
            }
        } elseif ($hours == 12) {
            $half_an_hour = '1-00-pm';
            $full_hour = $hours . '-30-am';
        } else {
            if ($hours < 9) {
                $hours+=0;
                $hours = '0' . $hours;
            }


            if ($hours == 11) {
                $half_an_hour = ($hours + 1) . '-00-pm';
            } else if ($hours == 9 || $hours == 10) {
                $half_an_hour = ($hours + 1) . '-00-am';
            } else {
                $half_an_hour = "0" . ($hours + 1) . '-00-am';
            }

            if ($hours == 1) {
                $full_hour = '00-30-pm';
            } else {
                $full_hour = $hours . '-30-am';
            }
        }

        $data['half_an_hour'] = $half_an_hour;
        $data['full_hour'] = $full_hour;

        return $data;
    }

    static function is_weekly_off_day($second_slot_part) {
        $part = strtolower($second_slot_part);
        if (strpos($part, 'off')) {
            return true;
        } else {
            return false;
        }
    }

    static function is_sattle_off_day($date) {
        
    }

    static function get_prac_with_date_filter($exp_day_arr) {
        $option = "<option value=''>--select time--</option>";
        if (!self::is_weekly_off_day($exp_day_arr[1])) {
            $start_arr = explode(':', $exp_day_arr[0]);
            $start_hour = $start_arr[0];
            $end_arr = explode(':', $exp_day_arr[1]);
            $end_hour = $end_arr[0];
            $tmp_hour = $start_hour;
            while ($tmp_hour < $end_hour) {
                $rep_string = $tmp_hour . ':00 AM';
                if ($tmp_hour > 12) {
                    if ($tmp_hour - 12 <= 9)
                        $rep_string = '0' . ($tmp_hour - 12) . ':00 PM';
                    else
                        $rep_string = ($tmp_hour - 12) . ':00 PM';
                }elseif ($tmp_hour == 12) {
                    $rep_string = $tmp_hour . ':00 PM';
                } elseif ($tmp_hour <= 9 && strlen($tmp_hour) < 2) {
                    $rep_string = '0' . $tmp_hour . ':00 AM';
                }
                $tmp_hour = (int) $tmp_hour;
                $option.="<option value='{$tmp_hour}:00'>{$rep_string}</option>";
                $tmp_hour++;
            }
        }
        return $option;
    }
    
    static function generate_time_options($exp_day_slot){
        $option = "<option value=''>--select time--</option>";
         if (self::is_weekly_off_day($exp_day_slot)) {
            echo $option;exit;
        }
        
        $start_hour = substr($exp_day_slot, 0, 2);
        $end_hour = substr($exp_day_slot, 10, 2);
        while ($start_hour <= $end_hour) {
            $rep_string = self::slot_with_zero_prefix($start_hour);
            $start_hour = (int) $start_hour;
            $option.="<option value='{$start_hour}:00'>{$rep_string}</option>";
            $start_hour++;
        }
        echo $option;
        exit;
    }

    static function check_date_is_within_range($allow_multiple, $start_date, $end_date, $bookdate) {
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $book_timestamp = strtotime($bookdate);
        if ($allow_multiple == 1)
            return ($book_timestamp == $start_timestamp);
        else
            return (($book_timestamp >= $start_timestamp) && ($book_timestamp < $end_timestamp));
    }

    static function booked_or_not($time, $booking_array_count, $treat_time, $prac_blocked_hours, $allow_multiple) {
        $time_in_24 = DATE("H:i", strtotime($time));        // if there is no regular appointment but blocked clinic or practitioner        if($booking_array_count<=0 && count($prac_blocked_hours)){        	for ($i = 0; $i < count($prac_blocked_hours); $i++) {        		$pieces = explode("&", $treat_time[$i]);
        		$start_time = $pieces[0];
        		$end_time = $pieces[1];
        		$BookingTime = $time_in_24;        		if (self::check_date_is_within_range($allow_multiple, $start_time, $end_time, $BookingTime) || self::is_in_blocked_time($time, $prac_blocked_hours)) {
        			return "booked";
        		}        	}        }                // regular case
        for ($i = 0; $i < $booking_array_count; $i++) {			
            $pieces = explode("&", $treat_time[$i]);
            $start_time = $pieces[0];
            $end_time = $pieces[1];
            $BookingTime = $time_in_24;
            if (self::check_date_is_within_range($allow_multiple, $start_time, $end_time, $BookingTime) || self::is_in_blocked_time($time, $prac_blocked_hours)) {
                return "booked";
            }
        }                
    }

    static function is_in_blocked_time($expected_timeslot, $blocked_array) {
        $exp_hour= explode(':', $expected_timeslot);
        if((strlen($expected_timeslot)>=6) && $exp_hour[0]>=12){
            $time_without_meridian=  $exp_hour[0].':'.substr($exp_hour[1], 0, 2);
            $numeric_timeslot = self::get_numeric_time($time_without_meridian);
        }else{
            $numeric_timeslot = self::get_numeric_time($expected_timeslot);
        }
        $is_blocked = false;
        foreach ($blocked_array as $elem) {
            if ($is_blocked)
                return true;
            if ($numeric_timeslot >= $elem['start_time'] && $numeric_timeslot <= $elem['end_time']) {
                $is_blocked = true;
            }
        }
        return $is_blocked;
    }

    static function get_numeric_time($app_time) {

        $numeric_time = 0;
        // process here
        $minutes = 0;
        $hours = $app_time;
        if (strpos($hours, ':') !== false) {
            // Split hours and minutes.
            list($hours, $minutes) = explode(':', $hours);
        }
        $meridian = substr($minutes, 2, 2);
        $minutes = substr($minutes, 0, 2);
        if ($meridian == 'pm' && $hours != '12') {
            $hours+=12;
        }
        $minutes = $hours * 60 + $minutes;
        if ($minutes < 0) {
            $Min = abs($minutes);
        } else {
            $Min = $minutes;
        }
        $iHours = floor($Min / 60);
        $Minutes = ($Min - ($iHours * 60)) / 100;
        $numeric_time = $iHours + $Minutes;
        return $numeric_time;
    }

    static function get_app_duration($start_time, $end_time) {
        $duration = '';
        if(strlen($start_time)>=6){
            $start_time=date('H:i',  strtotime($start_time));
        }
        $start_array = explode(":", $start_time);
        $end_array = explode(":", $end_time);
        $start_in_minutes = ($start_array[0] * 60) + $start_array[1];
        $end_in_minutes = ($end_array[0] * 60) + $end_array[1];
        $duration_in_minutes = $end_in_minutes - $start_in_minutes;

        return $duration_in_minutes;
    }
    
    
    /* 
     * upcoming version of checkroom to check whether this room 
     * already occupied for expected slot (ex: 19:20) 
     * in expected date (ex: 2014-12-08) and expected room (ex : 2) 
     * (service type is ignored in that case)
     * it'll be merged when glab_regular_app is refactored and implmented
     */
    
    static function checkroom_beta($room_id, $exp_date, $exp_slot){
        $app_layer_obj = new glab_appointment_layer();
        $existing_appointments = $app_layer_obj->get_room_appts($exp_date, $room_id);
        foreach($existing_appointments as $appointment){
            if (self::check_date_range($appointment['app_time'], $appointment['app_end_time'], $exp_slot)) {
                return "yes";
            }
        }
        return 'no';
    }
    
    static function checkroom($room_idd, $formated_codedate, $codSpe, $codHour1) {
        $codHour2 = $codHour1;
        $codHour2[2] = ':';
        $codHour_space = $codHour2;
        $codHour_space[5] = ' ';
        $codHour_space;
        $codHour_space_24 = date("H:i", strtotime($codHour_space));
        $app_layer_obj = new glab_appointment_layer();
        $ro = $app_layer_obj->get_room_appts($formated_codedate, $room_idd);
        $start_end_array = array();
        foreach ($ro as $ro_fetch_room) {
            $ro_start_time = $ro_fetch_room['app_time'];
            $ro_treat_dura = $ro_fetch_room['service_duration'];
            $ro_treat_dura1 = $ro_treat_dura . " " . "minutes";
            $ro_start_time1 = $ro_start_time;
            $ro_start_time1[2] = ':';
            $ro_start_time1;
            $ro_start_time2 = $ro_start_time1;
            $ro_start_time2[5] = ' ';
            $ro_start_time2;
            $start_time_24 = date("H:i", strtotime($ro_start_time2));
            $timestamp = strtotime($start_time_24 . " +" . $ro_treat_dura1);
            $endTime = date("H:i", $timestamp);
            $push_total = $start_time_24 . "&" . $endTime;
            array_push($start_end_array, $push_total);
        }

        $start_end_array_count = count($start_end_array);
        for ($i = 0; $i < $start_end_array_count; $i++) {
            $pieces_array = explode("&", $start_end_array[$i]);
            $start_time = $pieces_array[0];
            $end_time = $pieces_array[1];
            if (self::check_date_range($start_time, $end_time, $codHour_space_24)) {
                return "yes";
            }
        }
        // if nothing returned then, return it, added 2/11/2014
        return "no";
    }

    static function check_date_range($start_date, $end_date, $bookdate) {
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $book_timestamp = strtotime($bookdate);

        return (($book_timestamp >= $start_timestamp) && ($book_timestamp < $end_timestamp));
    }

    static function slot_with_zero_prefix($tmp_hour) {
        $rep_string = $tmp_hour . ':00 AM';
        $meridian = ($tmp_hour>=12)?'PM':'AM';
        $hour=($tmp_hour>12)?($tmp_hour-12):$tmp_hour;
        $formatted_hour=  str_pad($hour, 2, '0', STR_PAD_LEFT);
        $rep_string = $formatted_hour.':00 '.$meridian;
        return $rep_string;
    }

}
