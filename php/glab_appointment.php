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
        $this->customer_layer = new glab_customer_layer();
        $this->sync_layer = new glab_sync_layer();
        $this->message = null;
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
        $appointment_form=null;
        if($data['app_id']){
            $appointment_form=  glab_appointment_form::edit($data);
        }else{
        $appointment_form = glab_appointment_form::add($data);
        }
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
        $data['patternInfo'] = (isset($_POST['targetPattern'])) ? $this->sync_layer->get_pattern_info(trim($_POST['targetPattern'])) : null;
        if (isset($_POST["app_id"]) && is_numeric($_POST["app_id"])) {
            $data['app_id'] = $_POST["app_id"];
            $data['selected_app_info'] = $this->appointment_layer->single_info($data['app_id']);
            $data['selected_app_services']=  $this->prac_layer->get_services($data['selected_app_info']['practitioner_id']);
            $_REQUEST['app_date']=$data['date'];
            $_REQUEST['services']=$data['selected_app_info']['service_id'];
            $_REQUEST['practitioners']=$data['selected_app_info']['practitioner_id'];
            $_POST['obj_return']=true;
            $data['free_slots']=  $this->get_service_based_slot();
        }

        $data['final_date'] = glab_convert_helper::convert_to_timeformatdb($data['date']);
        return $data;
    }

    function edit($id) {
        $saved = null;
        $app_id = trim($id);
        $info = $this->appointment_layer->single_info($app_id);
        if (!is_numeric($app_id) || !$info)
            return false;
        if (isset($_POST['app_update_nonce']) && wp_verify_nonce($_POST['app_update_nonce'], 'app_update')) {
            $params = array();
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
        wp_enqueue_script('calendar-slider', plugins_url('glab_clinic/assets/js/jquery.easing.1.3.js'), array('jquery', 'cookie-js'), null, true);
        wp_enqueue_script('ajax-script', plugins_url('glab_clinic/assets/js/jquery.glab_calendar.js'), array('jquery', 'calendar-slider', 'jquery-ui-datepicker', 'cookie-js'), null, true);
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script', 'ajax-script'), false, true);
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
        if ($is_deleted) {
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
        $exp_day_slot = null;
        $day_in_char = strtolower(date('D', mktime(0, 0, 0, $_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year'])));
        $exp_day_slot = $this->clinic_layer->get_specific_day_slot($day_in_char);
        glab_slot_helper::generate_time_options($exp_day_slot);
    }

    function get_date_based_slot($params) {
        $day = strtolower(date('D', mktime(0, 0, 0, $params['month'], $params['day'], $params['year'])));
        $prac_schedule = $this->prac_layer->get_schedule($params['id']);
        $exp_day_slot = $prac_schedule[$day];
        glab_slot_helper::generate_time_options($exp_day_slot['value']);
    }

    function get_service_based_slot() {
        require_once 'helper/glab_available_slot.php';
        $avail_slot_helper = new glab_available_slot($_REQUEST['services'], $_REQUEST['app_date'], $_REQUEST['practitioners']);
        if(isset($_POST['obj_return'])){
            return $avail_slot_helper->get_slots();
        }
        $avail_slot_helper->get_slots();
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
        $this->save_regular_app();
    }

    public function save_regular_app() {
        require_once 'helper/glab_slot_helper.php';
        require_once 'helper/glab_regular_app.php';
        $regular_app=new glab_regular_app($_POST['doc_name'], $_POST['treat'], $_POST["date"]);
        return $regular_app->save();
    }
    
    public function view_appt_info(){
        require_once 'forms/glab_appointment_form.php';
        $row = $this->appointment_layer->get_appt_tooltip_info();
	$data=array('row'=>$row);
        glab_appointment_form::toolTipInfo($data);
    }

    public function show_app_details(){
        require_once 'forms/glab_appointment_form.php';
        $_GET['app_id']=$_POST['id'];
        $row = $this->appointment_layer->get_appt_tooltip_info();
	$data=array('row'=>$row);
        glab_appointment_form::showAppDetails($data);
    }
    
    private function update_regular_app() {
        
    }

}
