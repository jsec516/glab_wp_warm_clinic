<?php

class glab_import {

    private $prac_layer;
    private $cust_layer;
    private $service_layer;
    private $clinic_layer;
    private $app_layer;

    function __construct() {
        $this->prac_layer = new glab_practitioner_layer();
        $this->cust_layer = new glab_customer_layer();
        $this->service_layer = new glab_service_layer();
        $this->clinic_layer = new glab_clinic_layer();
        $this->app_layer = new glab_appointment_layer();
    }

    function process() {
        if (is_uploaded_file($_FILES['csv_file']['tmp_name']) && $this->is_csv_file($_FILES['csv_file']['name'])) {
            $current_practitioner_list = $this->prac_layer->get_existing_practitioner_list();
            $current_customer_list = $this->cust_layer->get_existing_customer_list();
            $current_treatment_list = $this->service_layer->get_existing_treatment_list();
            $current_room_list = $this->clinic_layer->get_existing_room_list();
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
            $firstRow = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($firstRow) {
                    if (!$this->check_column_title_order($data)) {
                        die("invalid_order");
                        exit;
                    }
                    $firstRow = false;
                } else {
                    if (in_array($data[2], $current_customer_list)) {
                        $ids = array_keys($current_customer_list, $data[2]);
                        $user_id = $ids[0];
                    } else {
                        $user_id = $this->cust_layer->create_new_customer($data);
                        $current_customer_list = $this->cust_layer->get_existing_customer_list();
                    }

                    if (in_array($data[4], $current_practitioner_list)) {
                        $ids = array_keys($current_practitioner_list, $data[4]);
                        $prac_id = $ids[0];
                    } else {
                        $prac_id = $this->prac_layer->create_new_practitioner($data);
                        $current_practitioner_list = $this->prac_layer->get_existing_practitioner_list();
                    }

                    if (in_array($data[5], $current_treatment_list)) {
                        $ids = array_keys($current_treatment_list, $data[5]);
                        $treatment_id = $ids[0];
                    } else {
                        $treatment_id = $this->service_layer->create_new_treatment($data);
                        $current_treatment_list = $this->service_layer->get_existing_treatment_list();
                    }
                    if (in_array($data[10], $current_room_list)) {
                        $ids = array_keys($current_room_list, $data[10]);
                        $room_id = $ids[0];
                    } else {
                        $room_id = $this->clinic_layer->create_new_room($data);
                        $current_room_list = $this->clinic_layer->get_existing_room_list();
                    }

                    $duration = $this->get_duration($data[6], $data[7]);
                    $columns = array(
                        'first_name' => $data[0],
                        'last_name' => $data[1],
                        'email' => $data[2],
                        'user_id' => $user_id,
                        'practitioner_id' => $prac_id,
                        'service_id' => $treatment_id,
                        'app_date' => $data[8],
                        'app_time' => $data[6],
                        'app_end_time' => $data[7],
                        'service_duration' => $duration,
                        'reminder_type' => $data[9],
                        'reminder_status' => 'N',
                        'status' => '1',
                        'room_id' => $room_id,
                        'app_type' => '0',
                    	'blog_id' => get_current_blog_id(),
                    	'blog_user_id' => get_current_user_id(),
                        'create_at' => time(),
                        'update_at' => time()
                    );
                    $this->app_layer->save_app($columns);
                }
            }

            fclose($handle);

            print "Import done";
            exit;
        } else {
            print "invalid_file";
            exit;
        }
    }

    function check_column_title_order($user_input_columns) {
        $column_array = array('customer_firstname', 'customer_lastname', 'customer_email',
            'customer_phone', 'doctors_email', 'service_name', 'app_start_time',
            'app_end_time', 'app_date', 'app_reminder', 'room_name');
        $is_valid = true;
        for ($i = 0; $i < count($column_array); $i++) {
            $defined = strtolower(trim($column_array[$i]));
            $user_request = strtolower(trim($user_input_columns[$i]));
            $is_equal = strcmp($defined, $user_request);
            if ($is_equal) {
                $is_valid = false;
            }
        }
        return $is_valid;
    }

    function get_duration($app_start_time, $app_end_time) {
        $duration = 0;
        $start_time_parts = explode(':', trim($app_start_time));
        $end_time_parts = explode(':', trim($app_end_time));
        if (substr($start_time_parts[1], -2) == 'PM' && $start_time_parts[0] != '12') {
            $start_time_parts[0]+=12;
        }

        if (substr($end_time_parts[1], -2) == 'PM' && $end_time_parts[0] != '12') {
            $end_time_parts[0]+=12;
        }
        $start_in_minute = ($start_time_parts[0] * 60) + intval($start_time_parts[1]);
        $end_in_minute = ($end_time_parts[0] * 60) + intval($end_time_parts[1]);

        $duration = $end_in_minute - $start_in_minute;
        return $duration;
    }
	
	function is_csv_file($name){
		$ext = end((explode(".", $name))); # extra () to prevent notice
		if(strtolower($ext)=='csv')
			return true;
		else
			return false;
	}

}
