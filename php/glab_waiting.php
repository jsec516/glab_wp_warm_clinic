<?php

class glab_waiting {

    private $waiting_layer;
    private $prac_layer;
    private $service_layer;
    private $is_loaded;
    private $message;

    function __construct() {
        require_once 'lists/glab_waiting_table.php';
        $this->waiting_layer = new glab_waiting_layer();
        $this->prac_layer = new glab_practitioner_layer();
        $this->service_layer = new glab_service_layer();
        $this->is_loaded = false;
        $this->message = '';
    }

    function load() {
        $this->perform_action();
        if (!$this->is_loaded) {
            $this->load_list();
        }
    }

    public function load_waiting_schedule($prac_id) {
        require_once 'forms/glab_waiting_form.php';
        $data['schedule'] = $this->prac_layer->get_schedule($prac_id);
        glab_waiting_form::load_schedule($data);
        exit;
    }

    public function add() {
        $saved = null;
        if (isset($_POST['wait_add_nonce']) && wp_verify_nonce($_POST['wait_add_nonce'], 'wait_add')) {
            $user_id = $_REQUEST['selected_user'];
            $saved = $this->waiting_layer->save($_POST);
        }
        $message_flag='glab_success_message_'.get_current_blog_id();
        if ($saved) {
        	update_option($message_flag, "Saved!");
        	$location = $_SERVER['REQUEST_URI'];
        	echo "<script>window.location.assign('{$location}');</script>";
        	exit;
        }
        $data['practitioners'] = $this->prac_layer->all(false);
        $data['services'] = $this->service_layer->all();
        $data['schedule'] = $this->prac_layer->get_schedule($data['practitioners'][1]['id']);


        require_once 'forms/glab_waiting_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Waiting - Add New </h3></div>';
        
        $saved_msg=get_option($message_flag);
        if ($saved_msg) {
        	echo '<div class="updated">';
        	echo '<p>' . __($saved_msg, 'my-text-domain') . '</p>
			    </div>';
        	update_option($message_flag, "");
        }

        $base_url = plugins_url('glab_clinic/glab_ajax_clinic.php');
        $asset_url = plugins_url('glab_clinic/assets');
        echo "<script type='text/javascript'>var glab_ajax_url='{$base_url}';
                                            var  glab_asset_url='{$asset_url}';
               </script>";
        wp_enqueue_script('alert_script', plugins_url('glab_clinic/assets/js/jquery.alerts.js'));
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('user_script', plugins_url('glab_clinic/assets/js/jquery.glab_user.js'));
        wp_enqueue_script('wait_script', plugins_url('glab_clinic/assets/js/jquery.glab_waiting.js'));
        wp_enqueue_script('schedule_script', plugins_url('glab_clinic/assets/js/jquery.schedule.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script', 'wait_script', 'schedule_script', 'user_script', 'alert_script'), false, true);
        wp_enqueue_style('wp-clinic-alert', plugins_url('glab_clinic/assets/css/jquery.alerts.css'));
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));

        echo '<div class="form-wrapper">';
        glab_waiting_form::add($data);
        echo '</div>';
        echo '</div>';
    }

    public function weekly() {
        
    }

    public function monthly() {
        
    }

    public function daily() {
        
    }

    function load_list() {

        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        $data = $this->waiting_layer->all();
        $list_table = new glab_waiting_table();
        $list_table->set_glab_data($data);
        echo '<div class="wrap"><h2>Waiting Appointment List</h2>';
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
		<input type="hidden" name="page" value="glab_wait_appointment" />';
       // $list_table->search_box('search', 'search_id');
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
                    $this->edit($_GET['waiting']);
                    break;
                case 'delete':
                    $this->delete($_GET['waiting']);
                    break;
            }
        }
    }

    function edit($id) {
    	
        $saved = null;
        if (isset($_POST['wait_edit_nonce']) && wp_verify_nonce($_POST['wait_edit_nonce'], 'wait_edit')) {
            $saved = $this->waiting_layer->update($_POST);
        }
        $wait_id = trim($id);
        $info = $this->waiting_layer->single_info($wait_id);

        $data['practitioners'] = $this->prac_layer->all();
        $data['services'] = $this->prac_layer->get_services($info['practitioner_id']);
        $data['schedule'] = $this->prac_layer->get_schedule($info['practitioner_id']);
        $data['info']=$info;

        if (!is_numeric($wait_id) || !$info)
            return false;
        require_once 'forms/glab_waiting_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Waiting  - Edit</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Saved!', 'my-text-domain') . '</p>
			    </div>';
        }
        $base_url = plugins_url('glab_clinic/glab_ajax_clinic.php');
        $asset_url = plugins_url('glab_clinic/assets');
        echo "<script type='text/javascript'>var glab_ajax_url='{$base_url}';
        var  glab_asset_url='{$asset_url}';
        </script>";
        wp_enqueue_script('alert_script', plugins_url('glab_clinic/assets/js/jquery.alerts.js'));
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('user_script', plugins_url('glab_clinic/assets/js/jquery.glab_user.js'));
        wp_enqueue_script('wait_script', plugins_url('glab_clinic/assets/js/jquery.glab_waiting.js'));
        wp_enqueue_script('schedule_script', plugins_url('glab_clinic/assets/js/jquery.schedule.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script', 'wait_script', 'schedule_script', 'user_script', 'alert_script'), false, true);
        wp_enqueue_style('wp-clinic-alert', plugins_url('glab_clinic/assets/css/jquery.alerts.css'));
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        
        echo '<div class="form-wrapper">';
        glab_waiting_form::edit($data);
        echo '</div>';
        echo '</div>';
        $this->is_loaded = true;
    }

    function delete($id) {
        $is_deleted = $this->waiting_layer->delete($id);
        if ($is_deleted) {
            $this->message = "Waiting appointment has been deleted successfully";
        }
    }

    function getWaitingList() {
        require_once 'w_indicator/index.php';
        $data = array();
        $availableSlotsArray = getWaitingListIndicationHtml();
        $scheduleArr = $availableSlotsArray;
        $waitDiv = '<ul>';
        $tot_slot = 0;
        foreach ($scheduleArr as $slot => $value) {
            if (isset($value['num_of_availability']) AND $value['num_of_availability']) {
                $waitDiv.='<li style="cursor:pointer;background:#FCFCFC;color:#000;border:5px solid #A1A1A1;border-top:0px;margin-bottom:0px;text-align:center;padding:5px;font-size:12px;font-weight:bolder;width:100%;"><a style="color:#000 !important;" href="' . site_url() . '/wp-admin/admin.php?page=glab_calendar&view_type=weekly&wait_request_day=' . strtolower($value['full_week_day']) . '&day=' . $value['day'] . '&month=' . $value['month'] . '&year=' . $value['year'] . '">' . $value['full_week_day'] . '-' . $value['num_of_availability'] . ' Matches</a></li>';
                $tot_slot += $value['num_of_availability'];
            }
        }
        $waitDiv.='</ul>';
        $data['list'] = $waitDiv;
        $data['total_slot'] = $tot_slot;
        return $data;
    }
    
    function getWaitingListMenu(){
        require_once 'w_indicator/index.php';
        $data = array();
		$data['lists']=array();
        $availableSlotsArray = getWaitingListIndicationHtml();
        $scheduleArr = $availableSlotsArray;
        $tot_slot = 0;
        foreach ($scheduleArr as $slot => $value) {
            if (isset($value['num_of_availability']) AND $value['num_of_availability']) {
                $data['lists'][]=array(
                    'href'=> site_url() . '/wp-admin/admin.php?page=glab_calendar&view_type=weekly&wait_request_day=' . strtolower($value['full_week_day']) . '&day=' . $value['day'] . '&month=' . $value['month'] . '&year=' . $value['year'],
                    'content'=>$value['full_week_day'] . '-' . $value['num_of_availability'] . ' Matches'
                );
                $tot_slot += $value['num_of_availability'];
            }
        }
        $data['total_slot'] = $tot_slot;
        return $data;
        
    }

    function load_app_confirmation() {
        $selected_date = $_REQUEST['selected_month'] . '/' . $_REQUEST['selected_day_bunch'] . '/' . $_REQUEST['selected_year'];
        $selected_time = $_REQUEST['time'];
        $expected_week_day = strtolower(date('D', mktime(0, 0, 0, $_REQUEST['selected_month'], $_REQUEST['selected_day_bunch'], $_REQUEST['selected_year'])));
        $resArr = $this->waiting_layer->get_waiting_with_user_info($expected_week_day);
        $validAppArr = $this->valid_appointment_list($resArr, $_REQUEST['time'], $expected_week_day);
        $data = array('resArr' => $resArr, 'validAppArr' => $validAppArr, 'expected_week_day' => $expected_week_day);
        require_once 'forms/glab_waiting_form.php';
        glab_waiting_form::confirmation_form($data);
    }

    public function load_waiting_info() {
        $info = $this->waiting_layer->waiting_info_with_user();
        return $info;
    }

    public function load_wait_time() {
        require_once 'helper/glab_wait_time_slot.php';
        $wait_slot = new glab_wait_time_slot();
        $wait_slot->get_slot_options();
    }

    public function finalize_waiting() {
        //  add appointment
        $wait_info = $this->waiting_layer->single_info($_POST['waiting_id']);
        $_POST['doc_name'] = $wait_info['practitioner_id'];
        $_POST['treat'] = $wait_info['service_id'];
        $_POST['date'] = str_pad($_POST['app_month'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($_POST['app_day'], 2, '0', STR_PAD_LEFT) . '/' . $_POST['app_year'];
        $_POST['slelected_time'] = $_POST['app_time'];
        $_POST['reminder'] = $wait_info['app_reminder'];
        $_POST['user_type'] = 2;
        $_REQUEST['uid'] = $wait_info['user_id'];
        $_POST['uid'] = $wait_info['user_id'];
        $_POST['app_id'] = '';
        $_POST['off_flag'] = '';
        $_POST['patternId'] = '';
        $_POST['ob_return']=true;
        $appointment_obj = new glab_appointment();
        $appointment_status = $appointment_obj->save_regular_app();
        if($appointment_status=='successfully'){
            //  remove waiting appointment
            $this->waiting_layer->delete($_POST['waiting_id']);
            return "finalized";
        }else{
            return "not_finalized app return msg: ".var_dump($appointment_status);
        }
    }

    private function valid_appointment_list($result_array, $proposed_time, $week_day) {
        $filtered_array = array();
        $inc = 0;
        if (is_array($result_array) AND ! empty($proposed_time)) {
            foreach ($result_array as $res) {
                $exp_time_array = explode('-', $res[$week_day]);
                $exp_start_array = explode(':', $exp_time_array[0]);
                $start_time = (strrpos($exp_time_array[0], '00') == '3') ? ((int) $exp_start_array[0]) : ($exp_start_array[0] + 0.5);

                $exp_end_array = explode(':', $exp_time_array[1]);
                $end_time = (strrpos($exp_time_array[1], '00') == '3') ? ((int) $exp_end_array[0]) : ($exp_end_array[0] + 0.5);
                //treat_duration should be considered
                $treat_duration = 0; //it should come from db
                $proposed_time_array = explode(':', $proposed_time);
                $proposed_time = (strrpos($proposed_time, '00') == '3') ? ((int) $proposed_time_array[0]) : ($proposed_time_array[0] + 0.5);
                $end_of_app = $proposed_time + $treat_duration;
                //echo ($start_time).' end of app:'.$end_of_app.' end time : '.$end_time;
                if ($start_time <= $proposed_time AND $end_of_app <= $end_time) {
                    $filtered_array[$inc]['doctor'] = $res['practitioner_id'];
                    $filtered_array[$inc]['id'] = $res['id'];
                    $filtered_array[$inc]['user_id'] = $res['user_id'];
                    $filtered_array[$inc]['patient_name'] = $res['first_name'] . ' ' . $res['last_name'];
                    $inc++;
                }
            }
            return $filtered_array;
        }
    }

}
