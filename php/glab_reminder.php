<?php

class glab_reminder {

    private $prac_layer;
    private $service_layer;
    private $is_loaded;

    public function __construct() {
        require_once 'lists/glab_reminder_table.php';
        $this->prac_layer = new glab_practitioner_layer();
        $this->service_layer = new glab_service_layer();
        $this->reminder_layer = new glab_reminder_layer();
        $this->is_loaded = false;
    }

    public function load() {
        $this->perform_action();
        if (!$this->is_loaded)
            $this->load_list();
    }
    
    function load_list() {
        $data = $this->reminder_layer->all();
        $table_data=  $this->reminder_layer->prepare_table_data($data);
        $list_table = new glab_reminder_table();
        $list_table->set_glab_data($table_data);
        echo '<div class="wrap"><h2>Reminder List</h2>';
        echo '<form id="glab-reminder-list-table-form" method="post">';
        $list_table->prepare_items();
        $list_table->display();
        echo '</form>';
        echo '<form method="post">
		<input type="hidden" name="page" value="glab_reminder" />';
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
                    $this->edit($_GET['reminder']);
                    break;
                case 'delete':
                    $this->delete();
                    break;
            }
        }
    }
    

    function perform_bulk_delete() {
        $this->reminder_layer->bulk_delete($_POST['prac']);
    }

    function er_add() {
        $saved = null;
        if (isset($_POST['er_add_nonce']) && wp_verify_nonce($_POST['er_add_nonce'], 'er_add')) {
            $saved = $this->reminder_layer->save();
        }
        $data['practitioners'] = $this->prac_layer->all();
        if (count($data['practitioners']) > 0) {
            $practitioner = array_slice($data['practitioners'], 0, 1);
            $data['services'] = $this->reminder_layer->prac_avail_services($practitioner[0]['id'],'1');
        } else {
            $data['services'] = array();
        }
        $data['load_type']='email';
        require_once 'forms/glab_reminder_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Email Reminder - Add New </h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Saved!', 'my-text-domain') . '</p>
			    </div>';
        }
        $this->load_assets('email');
        echo '<div class="form-wrapper">';
        glab_reminder_form::add($data);
        echo '</div>';
        echo '</div>';
    }
    
    private function load_assets($reminder_type){
        $plugin_url = plugins_url('glab_clinic');
        $base_url = plugins_url('glab_clinic/glab_ajax_clinic.php');
        $asset_url = plugins_url('glab_clinic/assets');
        echo "<script type='text/javascript'>var glab_plugin_url='{$plugin_url}'; 
            var glab_ajax_url='{$base_url}';
            var  glab_asset_url='{$asset_url}';
        </script>";
        if($reminder_type=='call'){
            $dial_url= 'http://'.$_SERVER["HTTP_HOST"].'/call_script/index.php';
            echo "<script type='text/javascript'>
            var dial_url = '{$dial_url}';
                var cr_add = true;
            </script>";
        }
        
         wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'),array('jquery'));
        wp_enqueue_script('upload_script', plugins_url('glab_clinic/assets/tool/SimpleAjaxUploader/SimpleAjaxUploader.js'), array('valdation_script'));
        wp_enqueue_script('reminder_script', plugins_url('glab_clinic/assets/js/jquery.glab_reminder.js'), array('upload_script'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('reminder_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
    }
    
    public function cr_add(){
        $saved = null;
        if(isset($_POST['cr_add_nonce']) && wp_verify_nonce($_POST['cr_add_nonce'], 'cr_add')){
            $saved = $this->reminder_layer->save();
        }
        $data['practitioners'] = $this->prac_layer->all();
        if (count($data['practitioners']) > 0) {
            $practitioner = array_slice($data['practitioners'], 0, 1);
            $data['services'] = $this->reminder_layer->prac_avail_services($practitioner[0]['id'],'2');
        } else {
            $data['services'] = array();
        }
        $data['format_content'] = $this->reminder_layer->get_call_content();
        $data['load_type']='call';
        require_once 'forms/glab_reminder_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Call Reminder - Add New </h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Saved!', 'my-text-domain') . '</p>
			    </div>';
        }
        $this->load_assets('call');
        echo '<div class="form-wrapper">';
        glab_reminder_form::add($data);
        echo '</div>';
        echo '</div>';
    }
    
    public function get_prac_services($data){
    	$prac_id=$_POST['prac_id'];
    	$services = $this->reminder_layer->prac_avail_services($data['prac_id'],$data['reminder_type']);
    	echo glab_html_helper::prac_service_option($services);
    	exit;
    }
    
    /*
     * @deprecated : not using anymore
     */
    public function load_reminder_form($type = 1) {
        // load reminder form
        $data['practitioners'] = $this->prac_layer->all();
        if (count($data['practitioners']) > 0) {
            $practitioner = array_slice($data['practitioners'], 0, 1);
            $data['services'] = $this->service_layer->practitioners($practitioner[0]['id']);
        } else {
            $data['services'] = array();
        }
        $data['format_content'] = $this->reminder_layer->get_call_content();
        $data['reminder_type'] = $type;
        require_once 'forms/glab_reminder_form.php';
        if ($type == '1') {
            glab_reminder_form::add_email_reminder_form($data);
        } else {
            glab_reminder_form::add_call_reminder_form($data);
        }
        exit;
    }

    public function edit($reminder_id) {
        $reminder_info=$this->reminder_layer->single_info($reminder_id);
        if ($reminder_info['reminder_type'] == '1') {
            $this->edit_email_reminder($reminder_info);
        } else {
            $this->edit_call_reminder($reminder_info);
        }
        $this->is_loaded=true;
    }


    private function edit_email_reminder($reminder_info=  array()) {
        $saved = null;
        if (isset($_POST['er_edit_nonce']) && wp_verify_nonce($_POST['er_edit_nonce'], 'er_edit')) {
            $saved = $this->reminder_layer->update();
        }
        $data['info']=$reminder_info;
        $data['practitioners'] = $this->prac_layer->all();
        if (count($data['practitioners']) > 0) {
            $data['services'] = $this->reminder_layer->prac_avail_services($reminder_info['practitioner_id'],'1',$reminder_info['service_ids']);
        } else {
            $data['services'] = array();
        }
        $data['load_type']='email';
        require_once 'forms/glab_reminder_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Email Reminder - Add New </h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Saved!', 'my-text-domain') . '</p>
			    </div>';
        }
        $this->load_assets('email');
        echo '<div class="form-wrapper">';
        glab_reminder_form::edit($data);
        echo '</div>';
        echo '</div>';
    }

    private function edit_call_reminder($reminder_info=array()) {
        $saved = null;
        if(isset($_POST['cr_edit_nonce']) && wp_verify_nonce($_POST['cr_edit_nonce'], 'cr_edit')){
            $saved = $this->reminder_layer->update();
        }
        $data['info']=$reminder_info;
        $data['practitioners'] = $this->prac_layer->all();
        if (count($data['practitioners']) > 0) {
            $practitioner = array_slice($data['practitioners'], 0, 1);
            $data['services'] = $this->reminder_layer->prac_avail_services($practitioner[0]['id'],'2');
        } else {
            $data['services'] = array();
        }
        $data['load_type']='call';
        require_once 'forms/glab_reminder_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Call Reminder - Edit </h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }
        $this->load_assets('call');
        echo '<div class="form-wrapper">';
        glab_reminder_form::edit($data);
        echo '</div>';
        echo '</div>';
    }

    public function delete() {
        $this->reminder_layer->delete($_GET['reminder']);
    }

}
