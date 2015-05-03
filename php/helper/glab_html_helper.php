<?php

class glab_html_helper {

    static function service_hour_option($selected = '0') {
        $hour = 1;
        $options = "<option value='0'>HH</option>";
        while ($hour <= 12) {
            $selected_html = ($hour == $selected) ? "selected" : "";
            $current_value = sprintf('%02d', $hour);
            $options .= "<option value='$hour' $selected_html>$current_value</option>";
            $hour++;
        }
        return $options;
    }

    static function service_minute_option($selected = '0') {
        $minute = 15;
        $options = "<option value='0'>MM</option>";
        while ($minute < 60) {
            $selected_html = ($minute == $selected) ? "selected" : "";
            $current_value = sprintf('%02d', $minute);
            $options .= "<option value='$minute' $selected_html>$current_value</option>";
            $minute += 15;
        }
        return $options;
    }

    static function service_interval_option($selected = '0') {
        $minute = 5;
        $options = "<option value='0'>MM</option>";
        while ($minute <= 60) {
            $selected_html = ($minute == $selected) ? "selected" : "";
            $current_value = sprintf('%02d', $minute);
            $options .= "<option value='$minute' $selected_html>$current_value</option>";
            $minute += 5;
        }
        return $options;
    }

    static function prac_service_option($services, $selected = array()) {
        $selected_ids = array();
        foreach ($selected as $item) {
            array_push($selected_ids, $item['service_id']);
        }
        $options = '';
        foreach ($services as $key => $value) {
            $selected_html = in_array($value['id'], $selected_ids) ? "selected" : "";
            $current_value = strtolower($value['name']);
            $options .= "<option value='{$value['id']}' $selected_html>$current_value</option>";
        }
        return $options;
    }

    static function prac_options($practitioners, $selected = '') {
        $options = '';
        foreach ($practitioners as $key => $value) {
            $selected_html = ($selected == $key) ? "selected" : "";
            $current_value = strtolower($value['name']);
            $options .= "<option value='$key' $selected_html>$current_value</option>";
        }
        return $options;
    }

    static function get_schedule_hours($current_schedule, $position) {
        $options = '';
        $start = 6;
        $end = 22;
        $selected = 6;
        if ($current_schedule && (!glab_convert_helper::is_off_day($current_schedule))) {
            if ($position == 'start')
                $selected = substr($current_schedule, 0, 2);
            else
                $selected = substr($current_schedule, 10, 2);
        }

        while ($start <= $end) {
            if ($start == $selected)
                $selected_html = "selected";
            else
                $selected_html = '';
            if ($start > 12) {
                $options .= "<option value='$start' $selected_html>" . sprintf('%02d', $start - 12) . "</option>";
            } else {
                $options .= "<option value='$start' $selected_html>" . sprintf('%02d', $start) . "</option>";
            }
            $start++;
        }

        return $options;
    }

    static function get_sliced_schedule($current_schedule, $position, $selected_value=0) {
        $options = '';
        $start = 0;
        $end = 0;
        $selected = 0;
        if ($current_schedule && (!glab_convert_helper::is_off_day($current_schedule))) {
            $start = substr($current_schedule, 0, 2);
            $end = substr($current_schedule, 10, 2);
        }

        if($position!='start'){
            $start+=1;            $selected_value=($selected_value)?$selected_value:($end);            
        }else{
            $end=$end-1;
        }
        		echo $end;
        while($start<=$end){			$selected_html=($start==$selected_value)?'selected="selected"':'';
            if ($start > 12) {
                $options .= "<option value='$start' {$selected_html} >" . sprintf('%02d', $start - 12) . "</option>";
            } else {
                $options .= "<option value='$start' {$selected_html} >" . sprintf('%02d', $start) . "</option>";
            }
            $start++;
        }
        
        return $options;
    }

    static function get_schedule_minutes($current_schedule, $position) {
        $options = '';
        $start = 0;
        $end = 30;
        $selected = 0;
        if ($current_schedule && (!glab_convert_helper::is_off_day($current_schedule))) {
            if ($position == 'start')
                $selected = substr($current_schedule, 3, 2);
            else
                $selected = substr($current_schedule, 13, 2);
        }
        while ($start <= $end) {
            if ($start == $selected)
                $selected_html = "selected";
            else
                $selected_html = '';
            $options .= "<option value='$start' $selected_html>" . sprintf('%02d', $start) . "</option>";
            $start += 30;
        }

        return $options;
    }

    static function get_meridian($current_schedule, $position) {
        $meridian = '';
        if ($position == 'start')
            $selected = substr($current_schedule, 0, 2);
        else
            $selected = substr($current_schedule, 10, 2);
        if ($selected >= 12)
            $meridian = "PM";
        else
            $meridian = "AM";
        return $meridian;
    }

    static function reminder_day_option($selected = 1) {
        $start = 1;
        $options = '';
        while ($start <= 5) {
            if ($start == $selected)
                $selected_html = "selected='selected'";
            else
                $selected_html = "";
            $options.="<option value='$start' $selected_html >$start</option>";
            $start++;
        }
        return $options;
    }

    static function get_services_option($services, $selected='') {
        $options = "<option value=''>Select Treatment</option>";
        foreach ($services as $service) {
            $selectedHtml='';
            if($selected==$service['service_id']){
                $selectedHtml="selected='selected'";
            }
            $current_value = strtolower($service['service_name']);
            $options .= "<option value='{$service['service_id']}' {$selectedHtml} >$current_value</option>";
        }
        return $options;
    }

    static function only_active_room_services($room_services, $active_services){
    	
    	$valid_services=array();
    	foreach($room_services as $service){
    		if(in_array($service, $active_services)){
    			array_push($valid_services, $service);
    		}
    	}
    	return $valid_services;
    }
}
