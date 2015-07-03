<?php

class glab_regular_app {

    private $user_info, $selected_room;

    //$_POST['doc_name'], $_POST['treat'], $_POST["date"]
    function __construct($prac_id, $service_id, $date) {
        $this->appointment_layer = new glab_appointment_layer();
        $this->service_layer = new glab_service_layer();
        $this->customer_layer = new glab_customer_layer();
        $this->prac_id = $prac_id;
        $this->service_id = $service_id;
        $this->date = $date;
        $date_array = explode("/", $this->date);
        $this->db_format_date = $date_array[2] . "-" . $date_array[0] . "-" . $date_array[1];
        $selected_time=trim($_POST['slelected_time']);        $time_slot_arr = explode(":",$selected_time);        $selected_time = ($time_slot_arr[0]>12)?substr($selected_time, 0, strlen($selected_time)-2):$selected_time;         $this->exp_slot = date('H:i', strtotime($selected_time));
        $this->reminder_type = trim($_POST["reminder"]);
        $this->is_edit_request = (isset($_POST["app_id"]) && $_POST["app_id"]) ? true : false;
        $this->edit_app_id = (isset($_POST["app_id"]) && $_POST["app_id"]) ? trim($_POST["app_id"]) : 0;
        $this->is_registered_user = ($_REQUEST["uid"] && is_numeric($_REQUEST["uid"])) ? true : false;
        $service_info = $this->service_layer->single_info($this->service_id);
        $this->service_duration = $service_info['duration'];
        $this->user_info = array();
        $this->selected_room = null;
        $this->return_msg=null;
    }

    function save() {
        $room_array = $this->service_layer->get_service_specific_rooms($this->service_id);
        sort($room_array); //sorted the room id's in ascending order
        $room_count = count($room_array);
        for ($index = 0; $index < $room_count; $index++) { // find appropriate room
            $room_id = $room_array[$index];
            $room_occupied = glab_slot_helper::checkroom($room_id, $this->db_format_date, $this->service_id, $this->exp_slot);
            if ($room_occupied == "yes") {
                if ($index == $room_count) {
                    echo "all_rooms_occupied";
                }
            } else {
                $this->selected_room = $room_id;
                $this->return_msg = $this->save_appointment();
                break;
            }
        }
        
        if(isset($_POST['ob_return'])){
            return $this->return_msg;
        }
    }

    private function save_appointment() {
        if ($this->is_registered_user) {
            return $this->save_app_with_existing_user();
        } else {
            return $this->save_app_with_new_user();
        }
    }

    private function insert_app() {
        $appts_info = array(
            'uid' => $this->user_info['id'],
            'doc_id' => $this->prac_id,
            'fname' => $this->user_info['first_name'],
            'lname' => $this->user_info['last_name'],
            'cus_email' => $this->user_info['email'],
            'treat' => $this->service_id,
            'total_treat_dura' => $this->service_duration,
            'date' => $this->db_format_date,
            'final_codHour' => $this->exp_slot,
            'end_time' => glab_slot_helper::get_appt_end_time($this->exp_slot, $this->service_duration),
            'room_insert' => $this->selected_room,
            'reminder' => $this->reminder_type
        );
        if ($this->is_edit_request) {
            $appts_info['app_id'] = $this->edit_app_id;
            $msg = $this->appointment_layer->update_regular_appt($appts_info);
        } else {
            $msg = $this->appointment_layer->save_regular_appt($appts_info);
        }
        return $msg;
    }

    private function save_app_with_new_user() {
        $is_unique_email = $this->customer_layer->is_unique_data('email', trim($_POST["cus_email"]));
        if (!$is_unique_email) {
            echo 'already_registered_email';
            return;
        }
        $cust_info = array();        $cust_info['fname'] = trim($_POST["firstName"]);        $cust_info['lname'] = trim($_POST["lastName"]);        $cust_info['cus_email'] = trim($_POST["cus_email"]);        $cust_info['phone'] = trim($_POST['cus_phone']);        $cust_info['upass'] = trim($_POST["userpass"]);
        $uid = $this->customer_layer->save_n_inserted_id($cust_info);
        if ($uid) {
            $this->user_info = $this->customer_layer->single_info($uid);
            $msg = $this->insert_app();
            echo $msg;
        }
    }

    private function save_app_with_existing_user() {
        $this->user_info = $this->customer_layer->single_info($_POST['uid']);
        if (!$this->user_info) {
            if (isset($_POST['ob_return'])) {
                $this->return_msg="user_not";
                return "user_not";
            }
            echo "user_not";
        }
        $msg = $this->insert_app();
        if (isset($_POST['ob_return'])) {
            $this->return_msg=$msg;
            return $msg;
        }
        echo $msg;
    }

}
