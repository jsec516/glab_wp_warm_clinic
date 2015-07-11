<?php
class glab_daily_html 
{
	static function generate_calendar($args) 
	{
		extract ( $args );
		/* target date variation setup */
		$dt1 = $selected_month;
		$dt2 = $selected_day;
		$dt3 = $selected_year;
		$selected_date = mktime ( 0, 0, 0, $selected_month, $selected_day, $selected_year );
		$selected_week = ( int ) date ( 'W', $selected_date );
		$appointment_layer = new glab_appointment_layer ();
		$content = self::get_header_html ();
		/* can be done separate function, just ignore for minimize migration risk */
		$day_length = strlen ( $selected_day ); // 6
		$codHour0 = "0";
		
		if ($day_length == 1) {
			$day = $codHour0 . "" . $selected_day;
		} else {
			$day = $selected_day;
		}
		$month_length = strlen($selected_month);
		$codHour1 = "0";
		
		if ($month_length == 1) {
			$month = $codHour1 . "" . $selected_month;
		} else {
			$month = $selected_month;
		}
		$db_date = $selected_year . '-' . $month . '-' . $day;
		$recent_date_ = $selected_year . '-' . $selected_month . '-' . $selected_day;
		$content .= 
		'<div id="hours_div" style="">
			<div class="autoGrow">
				<input type="hidden" id="day" value="' . $selected_day . '"  />
				<input type="hidden" id="week_num" value="' . date ( 'W', strtotime ( $recent_date_ ) ) . '" />
				<input type="hidden" id="dailyLabel" value="' . date ( 'd-F', strtotime ( $recent_date_ ) ) . '"/>
				<input type="hidden" id="month" value="' . date ( 'n', strtotime ( $recent_date_ ) ) . '" />
				<input type="hidden" id="year" value="' . date ( 'Y', strtotime ( $recent_date_ ) ) . '" />
				<div class="b_common autoGrow" cellpadding="0" cellspacing="0" >
					<div  class="fl" style="width:41px;"></div>
					<div  class="fl" colspan="' . count ( $room_data ) . '" style="text-align:center;width:72%; margin:0 0 0 174px;"></div>
					<div style="float:right; width:5%; margin:0 35px 0 0;">&nbsp;</div>
				</div>
				<div class="autoGrow"></div>
			</div>
			<div class="autoGrow" style="margin-bottom:15px;">
				<div class="autoGrow">
					<div style="border:0px;width:5.3%; padding:0 0 0 0; background:none repeat scroll 0 0 #717171; color:#fff; height:28px; font-weight:bold; border-bottom: 1px solid #999999;border-right: 0px solid #999999;" class="fl">
						<div style="margin:5px 0 0 0;">TIME</div>
					</div>';
		$i = 1;
		
		foreach ( $room_data as $room ) {
			$content .= '<div class="room_head_title fl" style="width:' . (83.2 / count ( $room_data )) . '%;border-right:2px solid #E7E7E7; font-weight:bold;" >' . ucfirst ( $room ['title'] ) . '</div>';
		}
		$content .= '</div>';
		$content .= '<div class="autoGrow">
					<div style="border:0px;vertical-align:middle;padding:0px;width:5.3%; background:none repeat scroll 0 0 #717171;height: 33px; color:#fff;" class="fl"><span class="time2"></span></div>';
		
		foreach ( $room_data as $room ) {
			$content .= '<div style="width:' . (83.2 / count ( $room_data )) . '%;height:32px;border-right:1px solid #E7E7E6;padding-left:1px;" class="fl  bord" ></div>';
		}
		$content .= '</div>';
		
		foreach ( $hours_data as $hours ) {
			$hours_part = explode ( ':', $hours ['hour'] );
			if ($hours_part [1] == '00') {
				$content .= '<div class="autoGrow">';
				$hours_name = $hours_part [0] . ':' . $hours_part [1];
				$content .= '<div style="border:0px;vertical-align:middle;padding:0px;width:5.3%; background:none repeat scroll 0 0 #717171;height: 33px; color:#fff;  " class="fl"><span class="time2"style="font-weight:bold;text-decoration:underline;">' . glab_convert_helper::convert_to_standard_time ( $hours_part [0] ) . '</span></div>';
				self::get_all_hrs_app ( $selected_date, $hours_part [0] );
				$time1 = $hours_part [0] . ':' . $hours_part [1];
				
				if ($hours_part [0] < 9) {
					$time2 = "0" . ($hours_part [0] + 1) . ':' . $hours_part [1];
				} else {
					$time2 = ($hours_part [0] + 1) . ':' . $hours_part [1];
				}
				
				foreach ( $room_data as $room ) {
					$bar = self::get_daily_room_chart ( $room, $db_date, $time1, $time2 );
					$content .= '<div style="width:' . (83.2 / count ( $room_data )) . '%;height:32px;padding-left:2px;" class="fl  bord">' . $bar . '</div>';
				}
				$content .= '</div>';
			}
		}
		$content .= '</div></div>';
		$content .= '</div>';
		
		return $content;
	}
	
	static function get_daily_room_chart($roomArr, $dates, $time1, $time2) 
	{
		// load layer
		$appointment_layer = new glab_appointment_layer ();
		$room_id = $roomArr ['id'];
		$leftAlignment = 50;
		$barHeight = 10;
		$top = 0;
		$bottom = 0;
		$color = array (
			'#483D8B',
			'#228B22',
			'#CD2626',
			'#FFC125',
			'#FF4500' 
		);
		$g = 0;
		$bar = '<div class="verticalBar">';
		$min = Array (
			"00",
			"05",
			"10",
			"15",
			"20",
			"25",
			"30",
			"35",
			"40",
			"45",
			"50",
			"55" 
		);
		$toparr = Array (
			"0",
			"3",
			"6",
			"9",
			"12",
			"15",
			"18",
			"21",
			"24",
			"27",
			"30",
			"33" 
		);
		$room_appts = $appointment_layer->get_room_interval_appts ( $room_id, $dates, $time1, $time2 );
		$num_rows = count ( $room_appts );
		
		if ($num_rows > 0) {
			
			foreach ( $room_appts as $fetch_select ) {
				$app_time = $fetch_select ['app_time'];
				$time_explode = explode ( ":", $app_time );
				$time_explode1 = $time_explode [1];
				$min_pos = "";
				
				while ($minute = current($min)) {
					
					if ($minute == $time_explode1) {
						$min_pos = key ( $min );
					}
					next ( $min );
				}
				reset ( $min );
				$top = $toparr [$min_pos];
				$treat_dura = $fetch_select ['service_duration'];
				$barHeight = $treat_dura * 0.55;
				//$barHeight = '33';
				$treatcolor = $fetch_select ['color_value'];
				$bar .= '<span rel="' . plugins_url ( 'glab_clinic' ) . '/glab_ajax_clinic.php?app_id=' . $fetch_select ['appt_id'] . '&tooltipInfo=true" onClick="javascript:showAppDetails(' . $fetch_select ['appt_id'] . ')" class="barDim black_tips" style="display:block; height:' . ($barHeight - 4) . 'px; top:' . $top . 'px;  background:' . $treatcolor . '; width:94%; z-index:3; position:absolute; border: 1px solid">' . $fetch_select ['cod_flag'] . '' . ":" . '' . $fetch_select ['firstname'] . ' ' . $fetch_select ['lastname'] . '</span>';
			}
		} else {
			$bar .= '<span rel="" class="black_tipss"  style="display: block; height: 25px; bottom: 10px; top: 17.5px; width: 90%; left: 5%;">&nbsp;</span>';
		}
		$bar .= '</div>';
		
		return $bar;
	}
	
	static function get_all_hrs_app($date, $hours) 
	{
		$full_n_half = glab_slot_helper::get_full_n_half ( $hours );
		extract ( $full_n_half );
		$appointment_layer = new glab_appointment_layer ();
		$hours_appts = $appointment_layer->get_hour_appts ( $date, $half_an_hour, $full_hour );
		// set appts
		$_SESSION ['bar'] ['app'] = '';
		$inc = 0;
		$j = 0;
		
		foreach ( $hours_appts as $row ) {
			$apptime = explode ( '-', $row ['app_time'] );
			$app_time = $apptime [1];
			$durationArr = explode ( '-', $row ['service_duration'] );
			$durationTime = 0;
			
			if ($durationArr [1] == 30) {
				$durationTime = $durationArr [0] + .5;
			} else {
				$durationTime = $durationArr [0];
			}
			$_SESSION ['bar'] [$j] [$row ['service_id'] . '_occurance'] += 1;
			$_SESSION ['bar'] [$j] [$row ['service_id'] . '_app_time'] = $row ['app_time'];
			$_SESSION ['bar'] [$j] [$row ['service_id'] . '_duration'] = $durationTime;
			$_SESSION ['bar'] [$j] [$row ['service_id'] . '_apptime'] = $app_time;
			$_SESSION ['bar'] [$j] [$row ['service_id'] . '_app_id_' . $inc] = $row ['id'];
			$_SESSION ['bar'] [$j] [$row ['service_id'] . '_app_date_' . $inc] = $row ['app_date'];
			$_SESSION ['bar'] [$j] [$row ['service_id'] . '_app_status_' . $inc] = $row ['app_status'];
			$j ++;
		}
	}
	
	static function get_header_html() 
	{
		$content = '<div id="daily_calendar_view" style="margin:-25px 0 0 0">';
		$content .= 
		'<script type="text/javascript">
			jQuery(document).ready(function($){$("span.black_tips").cluetip();});
		</script>';
		$content .= 
		'<style type="text/css">
			#hours_div{
			/*	border:1px solid #000;*/
				margin-top:0px;
				text-align:center;
				width:100%;
			}
		
			#hours_table td
			{
				/*border:1px solid #98bf21;*/
				border-left:1.5px solid #000;
				border-top:1px solid gray;
			}
				
			#hours_table th{
				font-size:1em;
				padding:3px 7px 2px 7px;
			}
				
			#hours_table td{
				font-size:1em;
			}
				
			#hours_table th
			{
				font-size:1.1em;
				text-align:left;
				padding-top:1px;
				padding-bottom:1px;
				background-color:#C0C0C0;
				color:#ffffff;
			}

			#hours_div > div:nth-child(2) > div:nth-child(2) {
			  display: none;
			}
				
			.b_common{
				font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
				width:100%;
				/*border-collapse:collapse;*/
			}
				
			.room_head_title{
				 border-bottom: 1px solid #999999;
				 border-left: 1px solid #999999;
				 border-top: 1px solid #999999;
		         color:#fff;
				 height:20px;
				 padding:3px 0 3px;
			}
		
			/*== ANAND ==*/
			
			.autoGrow {
				height:auto;
			/*	overflow:hidden;*/
				clear:both;
			}
			
			.fl {
				float:left;
			}
			
			.fr {
				float:right;
			}
			
			.clear {
				clear:both;
			}
			
			.wi48per {
				width:48%;
			}
			
			.wi30per {
				width:22.80%;
			}
			
			.bord {
				border-left:1px solid #E7E7E6;
				border-bottom:1px solid #E7E7E6;
			}
			
			.time2 {
				display: inline-block;
	    		/*margin:-11px 0 0 -2px;*/
				margin-top: 7px;
			}
		
			@-moz-document url-prefix() {
			  .time2 {
					
			  }
			}
		</style>';
		
		return $content;
	}
}