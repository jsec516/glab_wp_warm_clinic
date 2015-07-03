<?php

class glab_weekly_html {
	static function generate_calendar($args) {
		extract($args);
		// object for layers
		$appt_layer = new glab_appointment_layer();
		$clinic_layer = new glab_clinic_layer();

		// generate header
		$calendar = '<div style="padding:0px 0px 20px 0px;">';
		$headings = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
		$calendar .= '<div id="week_table"  class="b_common calendar">';
		$calendar .= '<div>';
		$temp_day = $current_day;
		$current_day = date('d', mktime(0, 0, 0, $month, $temp_day, $year));
		$month = date('n', mktime(0, 0, 0, $month, $temp_day, $year));
		$year = date('Y', mktime(0, 0, 0, $month, $temp_day, $year));
		$recent_week_date = $year.'-'.$month.'-'.$current_day;
		$calendar .= '<div class="calendar-row"  style="height:auto;overflow:hidden;">
	<div class="calendar-day-head" style="width:6.5%;padding:0px;background:none repeat scroll 0 0 #717171;">
	<input type="hidden" id="day" value="'.date('d', strtotime($recent_week_date)).'"  />
	<input type="hidden" id="week_num" value="'.date('W', strtotime($recent_week_date)).'" />
	<input type="hidden" id="month" value="'.date('n', strtotime($recent_week_date)).'" />
	<input type="hidden" id="dailyLabel" value="'.date('d-F', strtotime($recent_week_date)).'"/>
	<input type="hidden" id="year" value="'.date('Y', strtotime($recent_week_date)).'" />
	<span style="width:100%;padding-top:5px;padding-bottom:5px;display:block;background:none repeat scroll 0 0 #717171;">TIME</span>
	</div>';

		$loop_start = $current_day - (date('N', mktime(0, 0, 0, $month, $current_day, $year)) - 1); //lets start loop from first day of week
		$loop_end = $current_day + (7 - (date('N', mktime(0, 0, 0, $month, $current_day, $year)))); //lets end loop to last day of week
		$week_fetch = 0;
		$dayCounter = 1;
		$calAppArr = array();

		for ($i = $loop_start; $i <= $loop_end; $i++) {
			$calendar .= '<div class="calendar-day-head">';
			$day_of_the_week = date('N', mktime(0, 0, 0, $month, $i, $year)); //current day number 1-7 of week
			$loop_date = date('d', mktime(0, 0, 0, $month, $i, $year)); //current day in calendar
			$loop_month = date('m', mktime(0, 0, 0, $month, $i, $year));
			$loop_year = date('Y', mktime(0, 0, 0, $month, $i, $year));
			$_SESSION[$dayCounter] = $loop_year.'-'.$loop_month.'-'.$loop_date;
			$calAppArr[$dayCounter] = $appt_layer->get_week_day_appointments($_SESSION[$dayCounter]);

			$dayCounter++;

			$calendar .= date('D', mktime(0, 0, 0, $month, $i, $year)).'  <span style="color:#B5B5B5;vertical-align:text-bottom;font-size:.7em;padding-left:20px;">'.date('F d', mktime(0, 0, 0, $month, $i, $year)).'</span>';
			$calendar .= '</div>';
		}

		$calendar .= '</div>';

		// get all possible hours to display left column of calendar
		$hours_data = $clinic_layer->get_hours();
		$hours_part = array();

		foreach ($hours_data as $hours) :

			$hours_part = explode(':', $hours['hour']);
			if ($hours_part[1] == '00') :
				$calendar .= '<div style="height:auto;overflow:hidden;">';
				$hours_name = glab_convert_helper::convert_24_to_12($hours_part, false);

				// split week dates in array
				$day1 = explode('-', $_SESSION[1]);
				$day2 = explode('-', $_SESSION[2]);
				$day3 = explode('-', $_SESSION[3]);
				$day4 = explode('-', $_SESSION[4]);
				$day5 = explode('-', $_SESSION[5]);
				$day6 = explode('-', $_SESSION[6]);
				$day7 = explode('-', $_SESSION[7]);

				// if it's first hour slot
				if ($hours_part[0] == 6) {

					$calendar .= '<div class="time_box"><span class="time"></span></div>';
					$calendar .= '<div class="calendar_week_day" >&nbsp;</div>';
					$calendar .= '<div class="calendar_week_day" >&nbsp;</div>';
					$calendar .= '<div class="calendar_week_day" >&nbsp;</div>';
					$calendar .= '<div class="calendar_week_day" >&nbsp;</div>';
					$calendar .= '<div class="calendar_week_day" >&nbsp;</div>';
					$calendar .= '<div class="calendar_week_day">&nbsp;</div>';
					$calendar .= '<div class="calendar_week_day">&nbsp;</div>';
					$calendar .= '</div>';
					$calendar .= '<div style="height:auto;overflow:hidden;">';
					$calendar .= '<div class="time_box"><span class="time">'.glab_convert_helper::convert_to_standard_time($hours_part[0]).'</span></div>';
					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[1], $calAppArr[1], $hours_part[0], $hours_part[1]) == "") {

					}
					$asd1 = "'".$day1[2]."'";
					$asd2 = "'".$day1[1]."'";
					$asd3 = "'".$day1[0]."'";
					$asd4 = "'".$hours_name."'";

					$calendar .= ' onClick="javascript:Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[1], $calAppArr[1], $hours_part[0], $hours_part[1]).'</div>';
					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[2], $calAppArr[2], $hours_part[0], $hours_part[1]) == "") {

					}
					$asd1 = "'".$day2[2]."'";
					$asd2 = "'".$day2[1]."'";
					$asd3 = "'".$day2[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[2], $calAppArr[2], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[3], $calAppArr[3], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day3[2]."'";
					$asd2 = "'".$day3[1]."'";
					$asd3 = "'".$day3[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[3], $calAppArr[3], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[4], $calAppArr[4], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day4[2]."'";
					$asd2 = "'".$day4[1]."'";
					$asd3 = "'".$day4[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= 'onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[4], $calAppArr[4], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[5], $calAppArr[5], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day5[2]."'";
					$asd2 = "'".$day5[1]."'";
					$asd3 = "'".$day5[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[5], $calAppArr[5], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[6], $calAppArr[6], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day6[2]."'";
					$asd2 = "'".$day6[1]."'";
					$asd3 = "'".$day6[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[6], $calAppArr[6], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[7], $calAppArr[7], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day7[2]."'";
					$asd2 = "'".$day7[1]."'";
					$asd3 = "'".$day7[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[7], $calAppArr[7], $hours_part[0], $hours_part[1]).'</div>';
					$calendar .= '</div>';

				} elseif ($hours_part[0] - 12 == 8) {

					$calendar .= '<div class="time_box"><span class="time">'.glab_convert_helper::convert_to_standard_time($hours_part[0]).'</span></div>';
					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[1], $calAppArr[1], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day1[2]."'";
					$asd2 = "'".$day1[1]."'";
					$asd3 = "'".$day1[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[1], $calAppArr[1], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[2], $calAppArr[2], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day2[2]."'";
					$asd2 = "'".$day2[1]."'";
					$asd3 = "'".$day2[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[2], $calAppArr[2], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[3], $calAppArr[3], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day3[2]."'";
					$asd2 = "'".$day3[1]."'";
					$asd3 = "'".$day3[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[3], $calAppArr[3], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[4], $calAppArr[4], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day4[2]."'";
					$asd2 = "'".$day4[1]."'";
					$asd3 = "'".$day4[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[4], $calAppArr[4], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[5], $calAppArr[5], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day5[2]."'";
					$asd2 = "'".$day5[1]."'";
					$asd3 = "'".$day5[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[5], $calAppArr[5], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[6], $calAppArr[6], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day6[2]."'";
					$asd2 = "'".$day6[1]."'";
					$asd3 = "'".$day6[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[6], $calAppArr[6], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[7], $calAppArr[7], $hours_part[0], $hours_part[1]) == "") {

					}
					$asd1 = "'".$day7[2]."'";
					$asd2 = "'".$day7[1]."'";
					$asd3 = "'".$day7[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[7], $calAppArr[7], $hours_part[0], $hours_part[1]).'</div>';
					$calendar .= '</div>';

				} else {

					$calendar .= '<div class="time_box"><span class="time">'.glab_convert_helper::convert_to_standard_time($hours_part[0]).'</span></div>';
					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[1], $calAppArr[1], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day1[2]."'";
					$asd2 = "'".$day1[1]."'";
					$asd3 = "'".$day1[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[1], $calAppArr[1], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[2], $calAppArr[2], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day2[2]."'";
					$asd2 = "'".$day2[1]."'";
					$asd3 = "'".$day2[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[2], $calAppArr[2], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[3], $calAppArr[3], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day3[2]."'";
					$asd2 = "'".$day3[1]."'";
					$asd3 = "'".$day3[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[3], $calAppArr[3], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[4], $calAppArr[4], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day4[2]."'";
					$asd2 = "'".$day4[1]."'";
					$asd3 = "'".$day4[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[4], $calAppArr[4], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[5], $calAppArr[5], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day5[2]."'";
					$asd2 = "'".$day5[1]."'";
					$asd3 = "'".$day5[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[5], $calAppArr[5], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[6], $calAppArr[6], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day6[2]."'";
					$asd2 = "'".$day6[1]."'";
					$asd3 = "'".$day6[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[6], $calAppArr[6], $hours_part[0], $hours_part[1]).'</div>';

					$calendar .= '<div class="calendar_week_day"';

					if (self::create_week_chart($_SESSION[7], $calAppArr[7], $hours_part[0], $hours_part[1]) == "") {

					}

					$asd1 = "'".$day7[2]."'";
					$asd2 = "'".$day7[1]."'";
					$asd3 = "'".$day7[0]."'";
					$asd4 = "'".$hours_name."'";
					$calendar .= ' onClick="Appointment_view_slider12('.$asd1.','.$asd2.','.$asd3.','.$asd4.');"';
					$calendar .= '>'.self::create_week_chart($_SESSION[7], $calAppArr[7], $hours_part[0], $hours_part[1]).'</div>';
					$calendar .= '</div>';
				}

			endif;
		endforeach;

		$calendar .= '</div>';
		$calendar .= '</div>';
		$calendar .= '</div>';

		return $calendar;
	}

	static function create_week_chart($date = '', $appArr = '', $currentHour = '', $currentMinute = '') {
		if (!$date || empty($appArr))
			return '';

		$service_layer = new glab_service_layer();
		$app_layer = new glab_appointment_layer();
		$hoursStatus = array();
		$leftAlignment = 0;
		$barHeight = 0;
		$bottom = 0;
		$content1 = "";
		$content2 = "";
		$content = '<div align="center" class="verticalBar" style="width:100%;margin:0 auto;">';
		$matched = FALSE;
		
		if (count($appArr) > 0) {
			$margin_left = 10;
			$appArray = array();
			$st = "";
			$count = 0;
			$th = 5;
			$f = 0;
			$s = 0;
			$totalSlot = 0;
			
			foreach ($appArr as $row) {
				$appTime = 0;
				// convert them into 24 hour format
                                //var_dump($row);
				$time_in_24 = glab_convert_helper::convert_12_to_24($row,false);
				// will produce $appTime, $appTime1
				extract($time_in_24);
				
				if ($appTime == $currentHour) {
					$totalSlot++;
					if ($appTime1 == 30) {
						$f = $f + 1;
					} else {
						$s = $s + 1;
					}
				}
			}
			
			$celll_height = 15;
			if ($f > 1) {
				$usable_height = 15;
				$bar_height = $usable_height / $f;
			} else {
				$bar_height = 15;
			}
			if ($s > 1) {
				$usable_height = 15;
				$bar_height1 = $usable_height / $s;
			} else {
				$bar_height1 = 15;

			}
			$is_first_bar = true;
			foreach ($appArr as $row) {
				$appTime = 0;
				if (strtolower($row['begin_time'][2]) == 'pm' AND $row['begin_time'][0] != 12) {
					$appTime = $row['begin_time'][0] + 12;
					$appTime1 = $row['begin_time'][1];
				} else {
					$appTime = $row['begin_time'][0];
					$appTime1 = $row['begin_time'][1];
				}
				if ($appTime == $currentHour) {
					if ($appTime1 == 30) {
						$app_id = $row['id'];
						$numberofBar = count(array_keys($appArray, $currentHour));
						
						if (isset($hoursStatus[$currentHour])) {
							$hoursStatus[$currentHour] += 1;
							$bar_height;
							$margin_top = .5;
						} else {
							$hoursStatus[$currentHour] = 1;
							$bar_height;
							if ($f == 1 && $s == 0) {
								$margin_top = 11.5;
							} else {
								$margin_top = .5;
							}
							$margin_bottom = 2;
						}

						$treatColor = '';
						if ($row['app_status'] == '5') {
							$treatColor = "#BEBEBE";
						} else {
							$treatColor = getTreatColor($row['treatmentsId']);
						}
						$width = '108';
						$t = $service_layer->get_gaps_betn_appts($row['service_id']);
						$appTitleArr = $app_layer->get_app_title_info($app_id);
						$content1 .= '<a style="text-decoration:none;display:inline-block;" data-action="tooltipInfo" data-app_id="'.$app_id.'" class="tips_'.$app_id.' black_tips" onmouseover="showAppointmentInfo('.$app_id.')"><div align="center"; onClick="javascript:showAppDetails('.$app_id.')" class="barDim" style="'.$st.'background:'.$treatColor.';width:'.$width.'px; height:'.$bar_height.'px;margin-top:'.$margin_top.'px; margin-bottom:'.$margin_bottom.'px;">'.$appTitleArr['flag'].'</div></a>';

					} else {
						$app_id = $row['id'];
						$numberofBar = count(array_keys($appArray, $currentHour));
						if (isset($hoursStatus[$currentHour])) {
							$hoursStatus[$currentHour] += 1;
							$bar_height1;
							$margin_top = -5;
						} else {
							$hoursStatus[$currentHour] = 1;
							$bar_height1;
							$margin_top = -5;
							$margin_bottom = 2;
						}

						$treatColor = '';
						if ($row['app_status'] == '5') {
							$treatColor = "#BEBEBE";
						} else {
							$treatColor = $service_layer->get_treat_color($row['service_id']);
						}

						$width = '108';
						$t = $service_layer->get_gaps_betn_appts($row['service_id']);
						$appTitleArr = $app_layer->get_app_title_info($app_id);
						$width = '145';
						$total_margin = ($totalSlot) * 7;
						$width -= $total_margin;
						$bar_height1 = '32';
						$margin_top = '-5';
						$margin_left = '-10';
						if ($is_first_bar)
							$content2 .= '<a style="text-decoration:none;display:inline-block;" rel="http://anandahealth.ishiftmedia.com/tooltipInfo.php?app_id='.$app_id.'" class="tips_'.$app_id.' black_tips" onmouseover="showAppointmentInfo('.$app_id.')"><div align="center"; onClick="javascript:showAppDetails('.$app_id.',event)" class="barDim" style="'.$st.'background:'.$treatColor.';width:'.($width / $totalSlot).'px; height:'.$bar_height1.'px;margin-top:'.$margin_top.'px; margin-left:'.$margin_left.'px;">'.$appTitleArr['flag'].'</div></a>';
						else
							$content2 .= '<a style="text-decoration:none;display:inline-block;" rel="http://anandahealth.ishiftmedia.com/tooltipInfo.php?app_id='.$app_id.'" class="tips_'.$app_id.' black_tips" onmouseover="showAppointmentInfo('.$app_id.')"><div align="center"; onClick="javascript:showAppDetails('.$app_id.',event)" class="barDim" style="'.$st.'background:'.$treatColor.';width:'.($width / $totalSlot).'px; height:'.$bar_height1.'px;margin-top:'.$margin_top.'px; margin-left:5px;">'.$appTitleArr['flag'].'</div></a>';
						$is_first_bar = false;
					}
					$f = $f + 1;
				}
			}

			$content .= $content2.$content1;
		}
		$content .= "</div>";
		return $content;
	}
}
