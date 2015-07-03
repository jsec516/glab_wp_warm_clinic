<?php

class glab_available_slot {

    function __construct($service, $date, $prac_id) {
        $this->service = $service;
        $this->prac_id = $prac_id;
        $this->day_in_char = strtolower(date("D", strtotime($date)));
        $this->date = date('Y-m-d', strtotime($date)); // outputs 2006-01-24
        $this->appointment_layer = new glab_appointment_layer();
        $this->clinic_layer = new glab_clinic_layer();
        $this->prac_layer = new glab_practitioner_layer();
        $this->service_layer = new glab_service_layer();
    }

    private function set_clinic_attributes(){
    	$day_slot = $this->clinic_layer->get_specific_day_slot($this->day_in_char);
    	$this->is_clinic_off = (strpos($day_slot, 'OFF')===FALSE)?false:true;
    }
    
    private function set_prac_start_end_time() {
        //select the starting and ending time of prac time
        $day_schedule_of_prac = $this->prac_layer->get_specific_day_slot($this->day_in_char, $this->prac_id);
        $this->is_prac_off = (strpos($day_schedule_of_prac, 'OFF')===FALSE)?false:true;
        $dr_first_time = substr($day_schedule_of_prac, 0, 8);
        $dr_end_time = substr($day_schedule_of_prac, 10, 8);
        $this->prac_start_time = glab_convert_helper::to_minute($dr_first_time);    // prac starting time convert to minutes
        $this->prac_end_time = glab_convert_helper::to_minute($dr_end_time);                      // prac Ending time convert to minutes
    }
    
    

    private function set_service_attributes() {
        //picking the service time
        $service_info = $this->service_layer->get_service_duration($this->service);
        $this->service_type = $service_info['service_type'];
        $treatment_hour = $service_info['service_hours'];
        $treatment_min = $service_info['service_minutes'];
        $this->service_min_time = $service_info['service_duration'];
        //picked treatment time
        $htomin = glab_convert_helper::to_minute($treatment_hour);
        $this->service_minute_total = $htomin + $treatment_min;
    }

    private function set_existing_app_attributes() {
        $this->app_blocked_times = array();
        $appointments = $this->appointment_layer->get_practitioner_appts($this->date, $this->prac_id);
        // get blocked time of practitioner in app_type 1 or 2
        $this->prac_blocked_hours = $this->appointment_layer->get_practitioner_blocked_time($this->date, $this->prac_id); // outputs array(0=>array('start_time'=>18.3,'end_time'=>18.8),1=>....)
        foreach ($appointments as $app) {
            $pushing_total_time = $app['app_time'] . "&" . $app['app_end_time'];
            array_push($this->app_blocked_times, $pushing_total_time);
        }
       
        $this->app_blocked_times_count = count($this->app_blocked_times);
    }

    private function generate_slots() {
        $options = '<option value="">----Select Time----</option>';
        
        if($this->is_clinic_off OR $this->is_prac_off){
        	echo $options;
        	exit;
        }
        
        for ($interval_start = $this->prac_start_time; $interval_start < $this->prac_end_time; $interval_start = $interval_start + $this->service_minute_total) {
            $hour = floor($interval_start / 60);
            $minute = $interval_start % 60;
            $meridian = ($hour < 12) ? 'am' : 'pm';
            $finally = $hour . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT) . $meridian;
            $avaialable = glab_slot_helper::booked_or_not($finally, $this->app_blocked_times_count, $this->app_blocked_times, $this->prac_blocked_hours, $this->service_type);
            if (($avaialable != "booked") and ( ($interval_start + $this->service_min_time) <= $this->prac_end_time)) {
                //$represting_time = glab_convert_helper::format_valid_time($finally);
                $represting_time=date('h:i A',  strtotime(substr($finally,0,strlen($finally)-2)));
                $options.="<option value='{$finally}'>{$represting_time}</option>";
            }
        }
        if (isset($_POST['obj_return'])) {
            return $options;
        }
        echo $options;
        exit;
    }

    function get_slots() {
        $this->set_prac_start_end_time();
        $this->set_service_attributes();
        $this->set_existing_app_attributes();
         if (isset($_POST['obj_return'])) {
            return $this->generate_slots();
        }
        $this->generate_slots();
    }

}
