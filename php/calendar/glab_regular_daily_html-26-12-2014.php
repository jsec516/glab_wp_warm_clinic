<?php
class glab_regular_daily_html {

	static function generate_calendar($data) {
		require_once dirname(dirname(__FILE__)).'/helper/glab_html_helper.php';
		require_once dirname(dirname(__FILE__)).'/helper/glab_convert_helper.php';
		extract($data);
		$content='<table id="hours_div">';
		$content.='<tr><td>';
		$content.=self::generate_heading($data);
		$content.='</td></tr>';
		$content.='<tr><td>';
		$content.=self::draw_calendar($data);
		$content.='</td></tr></table>';
		return $content;
	}

	static function generate_bar_chart($room) {
		return '';
	}
	
	static function generate_heading($args){
		extract($args);
		$content='<table class="b_common" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td style="width:40px;">'.
					$selected_year.'</td><td colspan="'.count($room_data).'" style="text-align:center;">'.
					date("l,F d", mktime(0, 0, 0, $selected_month, $selected_day, $selected_year)).
					'<span style=" float:right;">&nbsp;week '.$selected_week.'</span>
					</td>
				</tr>
			</tbody>
		</table>';
		return $content;
	}
	
	static function draw_calendar($args){
		extract($args);
		/* draw table */
		$calendar='<table id="hours_table"  class="b_common" cellpadding="0" cellspacing="0">
						<tbody>
							<tr>
								<td style="border:0px;width:45px;padding:0px; background:none repeat scroll 0 0 #717171;">&nbsp;
								</td>';
		foreach($room_data as $room){
			$calendar.='<th class="room_head_title" align="center" >'.ucfirst($room['title']).'</th>';
		}
		$calendar.='</tr>';

		foreach($hours_data as $hours):
			$hours_part=explode(':',$hours['hour']);
			if($hours_part[1]=='00'):
				$calendar.='<tr>';
				if($hours_part[0]>=13)
					$hours_name=($hours_part[0]-12).':'.$hours_part[1];
				else
					$hours_name=$hours_part[0].':'.$hours_part[1];
				$calendar.='<td style="border:0px;vertical-align:middle;padding:0px;width:20px; background:none repeat scroll 0 0 #717171;"><span>'.
					glab_convert_helper::convert_to_standard_time($hours_part[0]).'</span></td>';
				self::get_all_hours_appt($selected_date,$hours_part[0]);
				foreach($room_data as $room):
					$bar=self::generate_bar_chart($room);
					$calendar.='<td style="height:22px;">'.$bar.'</td>';
				endforeach;
				$calendar.='</tr>';
			endif;
		endforeach;
		$calendar.='</tbody></table>';
		return $calendar;
	}

	static function get_all_hours_appt($date,$hours){
		if(trim($date)!='' && trim($hours)!=''):
			if($hours>12):
				$hours-=12;
				if($hours<10)
					$hours='0'.$hours;
				$half_an_hour=$hours.'-30-pm';
				$full_hour=$hours.'-00-pm';
			else:
				if($hours<10)
					$hours='0'.$hours;
				$half_an_hour=$hours.'-30-am';
				$full_hour=$hours.'-00-am';
			endif;
			
			$_SESSION['app']='';
			$appt_layer=new glab_appointment_layer();
			$result=$appt_layer->get_hour_appts($date,$half_an_hour,$full_hour);
			$inc=0;
			if($result){
				foreach($result as $row){
				 
					$apptime=explode('-',$row['app_time']);
					$app_time=$apptime[1];
				 	$durationArr=explode('-',$row['service_duration']);
					$durationTime=0;
					if($durationArr[1]==30){					   
						$durationTime=$durationArr[0]+.5;
					}else{					  
						$durationTime=$durationArr[0];
					}
					
					$_SESSION[$row['treatments'].'_occurance']+=1;
					$_SESSION[$row['treatments'].'_app_time']=$row['app_time'];
					$_SESSION[$row['treatments'].'_duration']=$durationTime;
					$_SESSION[$row['treatments'].'_apptime']=$app_time;
					$_SESSION[$row['treatments'].'_app_id_'.$inc]=$row['id'];
					$_SESSION[$row['treatments'].'_app_date_'.$inc]=$row['app_date'];
					
				}
			}
		endif;
	}
}
