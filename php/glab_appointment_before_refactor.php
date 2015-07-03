<?php

class glab_appointment {

    private $appointment_layer;
    private $clinic_layer;
    private $prac_layer;
    private $service_layer;
    private $customer_layer;
    private $sync_layer;
    private $message;
    private $is_loaded;

    function __construct() {
        require_once 'lists/glab_appointment_table.php';
        require_once 'glab_sync_layer.php';
        $this->appointment_layer = new glab_appointment_layer();
        $this->clinic_layer = new glab_clinic_layer();
        $this->prac_layer = new glab_practitioner_layer();
        $this->service_layer = new glab_service_layer();
        $this->customer_layer=new glab_customer_layer();
        $this->sync_layer=new glab_sync_layer();
        $this->message= null;
        $this->is_loaded = false;
    }

    function load() {
        $this->perform_action();
        if (!$this->is_loaded) {
            $this->load_list();
        }
    }

    function add($params = array()) {
        require_once 'forms/glab_appointment_form.php';
        $data = array();
        $data = $this->prepare_attributes();
        $appointment_form = glab_appointment_form::add($data);
        echo $appointment_form;
        exit;
    }

    private function prepare_attributes() {
        $data['month'] = $_POST["selected_month"];
        $data['year'] = $_POST["selected_year"];
        $data['day'] = $_POST["selected_day"];
        $data['date'] = $data['month'] . "/" . $data['day'] . "/" . $data['year'];
        $data['app_id'] = '';
        $data['selected_app_info'] = '';
        $data['patternInfo']=(isset($_POST['targetPattern']))?$this->sync_layer->get_pattern_info(trim($_POST['targetPattern'])):null;
        if (isset($_POST["app_id"]) && is_numeric($_POST["app_id"])) {
            $data['app_id'] = $_POST["app_id"];
            $data['selected_app_info'] = $this->appointment_layer->single_info($data['app_id']);
        }

        $data['final_date'] = glab_convert_helper::convert_to_timeformatdb($data['date']);
        return $data;
    }

    function edit($id) {
        $saved=null;
        $app_id = trim($id);
        $info = $this->appointment_layer->single_info($app_id);
        if (!is_numeric($app_id) || !$info)
            return false;
        if (isset($_POST['app_update_nonce']) && wp_verify_nonce($_POST['app_update_nonce'], 'app_update')) {
            $params=array();
            //@TODO: needs to create params array
            $saved = $this->appointment_layer->update_regular_appt($params);
            $info = $this->appointment_layer->get_single_info($app_id);
        }
        require_once 'forms/glab_appointment_form.php';
        echo '<div class="wrap"><h2>Edit Appointment</h2>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }
        $base_url = plugins_url('glab_clinic/glab_ajax_clinic.php');
        $asset_url = plugins_url('glab_clinic/assets');
        echo "<script type='text/javascript'>var glab_ajax_url='{$base_url}';
                                            var  glab_asset_url='{$asset_url}';
               </script>";
        
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
         wp_enqueue_script('cookie-js', plugins_url('glab_clinic/assets/js/jquery.cookie.js'), array('jquery'), null, true);
        wp_enqueue_script('calendar-slider', plugins_url('glab_clinic/assets/js/jquery.easing.1.3.js'), array('jquery','cookie-js'), null, true);
        wp_enqueue_script('ajax-script', plugins_url('glab_clinic/assets/js/jquery.glab_calendar.js'), array('jquery', 'calendar-slider', 'jquery-ui-datepicker', 'cookie-js'), null, true);
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script','ajax-script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        $params = array(
            'data' => $info,
        );
        glab_appointment_form::edit($params);
        echo '</div>';
        $this->is_loaded = true;
    }

    function delete($id) {
        $is_deleted = $this->appointment_layer->delete($id);
        if($is_deleted){
            $this->message = "Appointment Deleted Successfully";
        }
    }

    function load_list() {

        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        $data = $this->appointment_layer->all();
        $list_table = new glab_appointment_table();
        $list_table->set_glab_data($data);
        echo '<div class="wrap"><h2>Appointment List </h2>';
        if ($this->message) {
            echo '<div class="updated">';
            echo '<p>' . __($this->message, 'my-text-domain') . '</p>
			    </div>';
        }
        echo '<form id="glab-appointment-list-table-form" method="post">';
        $list_table->prepare_items();
        $list_table->display();
        echo '</form>';
        echo '<form method="post">
		<input type="hidden" name="page" value="glab_appointment" />';
        $list_table->search_box('search', 'search_id');
        echo '</form>';
        echo '</div>';
    }

    function perform_action() {
        // this is the top bulk action!!
        $action = ( isset($_POST['action']) ) ?
                filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRIPPED) : 'default_top_bulk_action';

        // this is the bottom bulk action!!
        $action2 = ( isset($_POST['action2']) ) ?
                filter_input(INPUT_POST, 'action2', FILTER_SANITIZE_STRIPPED) : 'default_bottom_bulk_action';

        switch ($action) {
            case 'delete':
                $this->perform_bulk_delete();
                break;
            default:
                break;
        }
        switch ($action2) {
            case 'delete':
                $this->perform_bulk_delete();
                break;
            default:
                break;
        }

        if (isset($_GET['action'])) {
            switch (trim($_GET['action'])) {
                case 'edit':
                    $this->edit($_GET['app']);
                    break;
                case 'delete':
                    $this->delete($_GET['app']);
                    break;
            }
        }
    }

    function date_available_slots() {
        $month = $_REQUEST['month'];
        $day = $_REQUEST['day'];
        $year = $_REQUEST['year'];
        $day = strtolower(date('D', mktime(0, 0, 0, $month, $day, $year)));
        $exp_day_slot = $this->clinic_layer->get_specific_day_slot($day);
        $exp_day_arr = explode('-', $exp_day_slot);
        $option = "<option value=''>--select time--</option>";
        if (!glab_slot_helper::is_weekly_off_day($exp_day_arr[1])) {
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
        echo $option;
        exit;
    }

    function get_date_based_slot($params) {
        $month = $params['month'];
        $day = $params['day'];
        $year = $params['year'];
        $prac_id = $params['id'];
        $day = strtolower(date('D', mktime(0, 0, 0, $month, $day, $year)));
        $prac_schedule = $this->prac_layer->get_schedule($prac_id);
        $exp_day_slot = $prac_schedule[$day];
        $exp_day_arr = explode('-', $exp_day_slot);
        echo $slots = glab_slot_helper::get_prac_with_date_filter($exp_day_arr);
        exit;
    }

    function get_service_based_slot() {
        $services = $_REQUEST['services'];
        $selectedDate = $_REQUEST['app_date'];
        $selectedPrac = $_REQUEST['practitioners'];
        $dt = date("D", strtotime($selectedDate));
        $day_name = strtolower($dt);
        //Converting (-) date format to db format
        $replaced_date = date('Y-m-d', strtotime($selectedDate)); // outputs 2006-01-24
        $prac_blocked_hours = $this->appointment_layer->get_practitioner_blocked_time($replaced_date, $selectedPrac);
        //select the starting and ending time of prac time
        $dr_first_end = $this->prac_layer->get_specific_day_slot($day_name, $selectedPrac);
        $split_time = explode(":-", $dr_first_end);
        $dr_first_time = $split_time[0];
        $dr_end_time = $split_time[1];
        $dr_end_time1 = substr($dr_end_time, 0, 8);
        //end of selected the starting and ending time of prac time
        //picking the service time
        $service_info = $this->service_layer->get_service_duration($services);
        $service_type = $service_info['service_type'];
        $treatment_hour = $service_info['service_hours'];
        $treatment_min = $service_info['service_minutes'];
        $service_min_time = $service_info['service_duration'];
        //picked treatment time
        $htomin = glab_convert_helper::to_minute($treatment_hour);
        $treatment_min_total = $htomin + $treatment_min;
        $treat_total = glab_convert_helper::to_minute($treatment_hour, $treatment_min);
        $dr_start_time = glab_convert_helper::to_minute($dr_first_time);    // prac starting time convert to minutes
        $sr_end_time = glab_convert_helper::to_minute($dr_end_time1);                      // prac Ending time convert to minutes	
        $time_gap = $sr_end_time - $dr_start_time;                                          //difference b/w end start time
        $divided_gap = $time_gap / $treat_total;                                            //Deviding the difference time interval with total treat
        // date and practitioner fetching from appointment table 
        // picking inserted values
        $treat_time = array();
        $appointments = $this->appointment_layer->get_practitioner_appts($replaced_date, $selectedPrac);
        foreach ($appointments as $date_doc_fetch) {
            $select_treat_dura = $date_doc_fetch['service_duration'];
            $select_treat_dura1 = $select_treat_dura . " " . "minutes";
            $select_app_time = $date_doc_fetch['app_time'];
            $str = $select_app_time;
            $str[5] = ' ';
            $str;
            $stre = $str;
            $stre[2] = ':';
            $stre;
            $start_12_hour_format = date("H:i", strtotime($stre)); // db satrt time to 24 hour format
            $timestamp = strtotime($stre . " +" . $select_treat_dura1);
            $endTime = date("H:i", $timestamp); // Adding first time and treatment dura
            $time_in_12_hour_format = DATE("g:i a", STRTOTIME($endTime));
            // covert to db format
            $Hour12_length = strlen($time_in_12_hour_format);
            $Hout_hard_value = "0";
            if ($Hour12_length == 7) {
                $coverted_value = $Hout_hard_value . "" . $time_in_12_hour_format;
            } else {
                $coverted_value = $time_in_12_hour_format;
            }

            $time_in_24_hour_format = date("H:i", strtotime($coverted_value));
            $db_format = $coverted_value;
            $db_format[5] = '-';
            $db_format1 = $db_format;
            $db_format1[2] = '-';
            $pushing_total_time = $start_12_hour_format . "&" . $time_in_24_hour_format;
            array_push($treat_time, $pushing_total_time);
        }
        $booking_array_count = count($treat_time);
        // picked inserted values time interval
        $options = '<option>----Select Time----</option>';
        for ($interval_start = $dr_start_time; $interval_start < $sr_end_time; $interval_start = $interval_start + $treatment_min_total) {
            $interval_start_hour = glab_convert_helper::convert_minutes_2_hours($interval_start);
            $time_in_12_hour_format = DATE("g:ia", strtotime($interval_start_hour));
            $am_splitter = $time_in_12_hour_format;
            $finally = $am_splitter;
            $finally_split = explode(":", $finally);
            $one = $finally_split[0];
            $two = $finally_split[1];
            $theminute = substr($two, 0, 2);
            $theampm = substr($two, 2, 4);
            $last_time = $one . "-" . $theminute . "-" . $theampm;
            // zero adding left side of last_time
            $last_time0 = "0";
            $last_time_len = strlen($last_time);
            if ($last_time_len == 7) {
                $last_time1 = $last_time0 . "" . $last_time;
            } else {
                $last_time1 = $last_time;
            }
            $avaialable = glab_slot_helper::booked_or_not($finally, $booking_array_count, $treat_time, $prac_blocked_hours, $service_type);
            if (($avaialable != "booked") and ( ($interval_start + $service_min_time) <= $sr_end_time)) {
                $represting_time = glab_convert_helper::format_valid_time($finally);
                $options.="<option value='{$finally}'>{$represting_time}</option>";
            }
        }
        if(isset($_POST['obj_return'])){
            return $options;
        }
        echo $options;
        exit;
    }

    public function break_practitioner() {

        $this->appointment_layer->save_break_practitioner();
        if (isset($_REQUEST['patternId'])) {
            $this->appointment_layer->update_event_status($_REQUEST['patternId']);
        }
        echo "success";
        exit;
    }

    public function block_clinic() {
        $this->appointment_layer->save_block_clinic();
        if (isset($_REQUEST['patternId'])) {
            $this->appointment_layer->update_event_status($_REQUEST['patternId']);
        }
        echo "success";
        exit;
    }

    public function submit_regular_app() {
        if ($_POST['request_type'] == 'add_appointment') {
            echo 'here';
            $this->save_regular_app();
        } else {
            $this->update_regular_app();
        }
    }

    public function save_regular_app() {
        require_once 'helper/glab_slot_helper.php';
        $codDr = $_POST["doc_name"];
        $codSpe = $_POST["treat"];
        $date3 = $_POST["date"];
        if($_POST["app_id"]){
           $app_id=$_POST["app_id"];
        }
        $date1 = explode("/", $date3);
        $formated_codedate = $date1[2] . "-" . $date1[0] . "-" . $date1[1];
        $slelected_time1 = $_POST["slelected_time"];
        $strlen_time = strlen($slelected_time1);
        if ($strlen_time == 6) {
            $slelected_time1 = "0" . $slelected_time1;
        } else {
            $slelected_time1;
        }
        
        $slelected_time1[2] = '-';
        echo $slelected_time1;
        $slelected_time3 = substr($slelected_time1, 0, 5);
        $slelected_time4 = substr($slelected_time1, 5, 5);
        $slelected_time2 = $slelected_time3 . "-";
        $codHour1 = $slelected_time2 . $slelected_time4;
        // get service duration
        $service_info = $this->service_layer->single_info($codSpe);
        $total_treat_dura = $service_info['duration'];

        $treatment_id_array = $this->service_layer->get_service_specific_rooms($codSpe);
        //sorted the room id's in ascending order
        sort($treatment_id_array);
        $room_count = count($treatment_id_array);
        // find appropriate room
        for ($rm_count = 0; $rm_count < $room_count; $rm_count++) {
            $room_insert = $treatment_id_array[$rm_count];
            $room_occupied = glab_slot_helper::checkroom($treatment_id_array[$rm_count],$formated_codedate,$codSpe,$codHour1);
            echo $room_occupied;
            if ($room_occupied == "yes") {
                if ($rm_count == $room_count) {
                    echo "<div>hai</div>";
                }
            } else {
                if (isset($_REQUEST["username"])) {
                    $fname = $_POST["firstName"];
                    $lname = $_POST["lastName"];
                    $uname = $_POST["username"];
                    $upass = $_POST["userpass"];
                    $cus_email = $_POST["cus_email"];
                    $cus_phone = $_POST["cus_phone"];
                    $fullname = $fname . " " . $lname;
                    $doc_id = $_POST["doc_name"];
                    $treat = $_POST["treat"];
                    $date3 = $_POST["date"];
                    $date1 = explode("/", $date3);
                    $date = $date1[2] . "-" . $date1[0] . "-" . $date1[1];
                    $slelected_time1 = $_POST["slelected_time"];
                    $strlen_time = strlen($slelected_time1);
                    if ($strlen_time == 6) {
                        $slelected_time1 = "0" . $slelected_time1;
                    } else {
                        $slelected_time1;
                    }
                    $slelected_time1[2] = '-';
                    $slelected_time1;
                    $slelected_time3 = substr($slelected_time1, 0, 5);
                    $slelected_time4 = substr($slelected_time1, 5, 5);
                    $slelected_time2 = $slelected_time3 . "-";
                    $db_time = $slelected_time2 . $slelected_time4;
                    $db_time1 = $db_time;
                    $db_time1[2] = ':';
                    $db_time1;
                    $db_time2 = $db_time1;
                    $db_time2['5'] = ' ';
                    $db_time2;
                    $final_codHour = date("H:i", strtotime("$db_time2"));
                    $selected_time = $_POST["slelected_time"];
                    if (strpos($selected_time, 'pm') !== false) {
                        $selected_time_parts = explode(':', $selected_time);
                        if ($selected_time_parts[0] != 12)
                            $selected_time_parts[0] = $selected_time_parts[0] + 12;
                        $selected_time = implode(':', $selected_time_parts);
                    }
                    if (strlen($selected_time) == 6) {
                        $selected_time = '0' . $selected_time;
                    }
                    $final_codHour = substr($selected_time, 0, 5);
                    $reminder = $_POST["reminder"];
                    $user_type = $_POST["user_type"];
                    // save customer and return id
                    $cust_info=array();
                    $cust_info['fname']=$fname;
                    $cust_info['lname']=$lname;
                    $cust_info['cus_email']=$cus_email;
                    $cust_info['upass']=$upass;
                    $uid = $this->customer_layer->save_n_inserted_id($cust_info);
                    if ($uid) {
                        $appts_info = array(
                            'uid' => $uid,
                            'doc_id' => $doc_id,
                            'fname' => $fname,
                            'lname' => $lname,
                            'cus_email' => $cus_email,
                            'treat' => $treat,
                            'total_treat_dura' => $total_treat_dura,
                            'date' => $date,
                            'final_codHour' => $final_codHour,
                            'end_time' => glab_slot_helper::get_appt_end_time($final_codHour, $total_treat_dura),
                            'room_insert' => $room_insert,
                            'reminder' => $reminder
                        );
                        if(isset($app_id)){
                            $appts_info['app_id']=$app_id;
                            $msg = $this->appointment_layer->update_regular_appt($appts_info);
                        }else{
                           $msg = $this->appointment_layer->save_regular_appt($appts_info); 
                        }
                        
                        echo $msg;
                    }
                } else {
                    $check_user=null;
                    if(isset($_POST['uid'])){
                        $check_user=  $this->customer_layer->single_info($_POST['uid']);
                    }else{
                        $firstname = $_POST["firstName"];
                        $lastname = $_POST["lastName"];
                        $check_user = $this->customer_layer->fetch_user_for($firstname, $lastname);
                    }
                    if ($check_user != '') {
                        $uid = $check_user['id'];
                        $fname = $check_user['first_name'];
                        $last = $check_user['last_name'];
                        $email = $check_user['email'];
                        $doc_id = $_POST["doc_name"];
                        $treat = $_POST["treat"];
                        $date3 = $_POST["date"];
                        $date1 = explode("/", $date3);
                        $date = $date1[2] . "-" . $date1[0] . "-" . $date1[1];
                        $slelected_time1 = $_POST["slelected_time"];
                        $strlen_time = strlen($slelected_time1);
                        if ($strlen_time == 6) {
                            $slelected_time1 = "0" . $slelected_time1;
                        } else {
                            $slelected_time1;
                        }
                        $slelected_time1[2] = '-';
                        $slelected_time1;
                        $slelected_time3 = substr($slelected_time1, 0, 5);
                        $slelected_time4 = substr($slelected_time1, 5, 5);
                        $slelected_time2 = $slelected_time3 . "-";
                        $db_time = $slelected_time2 . $slelected_time4;
                        $db_time1[2] = ':';
                        $db_time1;
                        $db_time2 = $db_time1;
                        $db_time2['5'] = ' ';
                        $db_time2=implode('',$db_time2);
                        $final_codHour = date("H:i", strtotime($db_time2));
                        $selected_time = $_POST["slelected_time"];
                        if (strpos($selected_time, 'pm') !== false) {
                            $selected_time_parts = explode(':', $selected_time);
                            if ($selected_time_parts[0] != 12)
                                $selected_time_parts[0] = $selected_time_parts[0] + 12;
                            $selected_time = implode(':', $selected_time_parts);
                        }
                        if (strlen($selected_time) == 6) {
                            $selected_time = '0' . $selected_time;
                        }
                        $final_codHour = substr($selected_time, 0, 5);
                        $end_time = glab_slot_helper::get_appt_end_time($final_codHour,$total_treat_dura);
                        $reminder = $_POST["reminder"];
                        $user_type = $_POST["user_type"];
                        $appts_info = array(
                            'uid' => $uid,
                            'doc_id' => $doc_id,
                            'fname' => $fname,
                            'lname' => $last,
                            'cus_email' => $email,
                            'treat' => $treat,
                            'total_treat_dura' => $total_treat_dura,
                            'date' => $date,
                            'final_codHour' => $final_codHour,
                            'end_time' => $end_time,
                            'room_insert' => $room_insert,
                            'reminder' => $reminder
                        );
                        if(isset($app_id)){
                            $appts_info['app_id']=$app_id;
                            $msg = $this->appointment_layer->update_regular_appt($appts_info);
                        }else{
                           $msg = $this->appointment_layer->save_regular_appt($appts_info); 
                        }
                        if(isset($_POST['ob_return'])){
                            return $msg;
                        }
                        echo $msg;
                    } else {
                        if(isset($_POST['ob_return'])){
                            return "user_not";
                        }
                        echo "user_not";
                    }
                }
                exit;
            }
        }
    }

    private function update_regular_app() {
        
    }

}
