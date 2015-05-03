<?php

class glab_practitioner {

    private $prac_layer;
    private $service_layer;
    private $is_loaded;

    function __construct() {
        require_once 'lists/glab_practitioner_table.php';
        $this->prac_layer = new glab_practitioner_layer();
        $this->service_layer = new glab_service_layer();
        $this->is_loaded = false;
    }

    function load() {
        $this->perform_action();
        if (!$this->is_loaded)
            $this->load_list();
    }

    function add() {
        $saved = null;
        $errors = null;
        if (isset($_POST['prac_add_nonce']) && wp_verify_nonce($_POST['prac_add_nonce'], 'prac_add')) {
        	$is_valid=$this->prac_layer->validate();
        	if($is_valid===true)
            	$saved = $this->prac_layer->save();
        	else 
        		$errors = $is_valid;
        }
		$message_flag='glab_success_message_'.get_current_blog_id();
		if ($saved) {
            update_option($message_flag, "practitioner saved successfully !");
            	$location = $_SERVER['REQUEST_URI'];
            	echo "<script>window.location.assign('{$location}');</script>";
            	exit;
        }
		
        require_once 'forms/glab_practitioner_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Practitioner  - Add New </h3></div>';

        $saved_msg=get_option($message_flag);
        if ($saved_msg) {
            echo '<div class="updated">';
            echo '<p>' . __($saved_msg, 'my-text-domain') . '</p>
			    </div>';
            update_option($message_flag, "");
        }
        
        if($errors){
        	echo '<div class="error">';
        	echo '<p>' . __($errors, 'my-text-domain') . '</p>
			    </div>';
        }
        
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        $params = array(
            'all_services' => $this->service_layer->get_active_services()
        );
        echo '<div class="form-wrapper">';
        glab_practitioner_form::add($params);
        echo '</div>';
        echo '</div>';
    }

    function edit($id) {
        $saved = null;
        $prac_id = trim($id);
        if (isset($_POST['prac_edit_nonce']) && wp_verify_nonce($_POST['prac_edit_nonce'], 'prac_edit')) {
        	$is_valid=$this->prac_layer->validate(true, $prac_id);
        	if($is_valid===true)
        		$saved = $this->prac_layer->update($prac_id);
        	else
        		$errors = $is_valid;
        }
        
        $info = $this->prac_layer->single_info($prac_id);
        $selected_services = $this->prac_layer->get_services($prac_id);
        if (!is_numeric($prac_id) || !$info)
            return false;
        require_once 'forms/glab_practitioner_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Practitioner  - Edit</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Saved!', 'my-text-domain') . '</p>
			    </div>';
        }
        
        if($errors){
        	echo '<div class="error">';
        	echo '<p>' . __($errors, 'my-text-domain') . '</p>
			    </div>';
        }
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        $params = array(
            'all_services' => $this->service_layer->get_active_services(),
            'data' => $info,
            'selected_services' => $selected_services
        );
        echo '<div class="form-wrapper">';
        glab_practitioner_form::edit($params);
        echo '</div>';
        echo '</div>';
        $this->is_loaded = true;
    }

    function set_schedule() {

        $prac_id = $_GET['prac'];
        $data['info'] = $this->prac_layer->single_info($prac_id);
		
        // if request doesn't have proper permission or not found in database then terminate the request
        if (!isset($data['info']['id']) || $data['info']['id'] != $prac_id) {
            die("Request not found");
        }
        require_once 'forms/glab_practitioner_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Practitioner  - Set Schedule (' . $data['info']['first_name'] . ' ' . $data['info']['last_name'] . ')</h3></div>';
        $saved = null;
        if (isset($_POST['prac_schedule_nonce']) && wp_verify_nonce($_POST['prac_schedule_nonce'], 'prac_schedule')) {

            $saved = $this->prac_layer->update_schedule($prac_id);
        }
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }


        $data['schedule'] = $this->prac_layer->get_schedule($prac_id);
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('schedule_script', plugins_url('glab_clinic/assets/js/jquery.schedule.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script', 'schedule_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_practitioner_form::display_schedule($data);
        glab_practitioner_form::set_schedule($data);
        echo '</div>';
        echo '</div>';
        $this->is_loaded = true;
    }

    function load_list() {
        $data = $this->prac_layer->all();
        $list_table = new glab_practitioner_table();
        $list_table->set_glab_data($data);
		$new_prac_url = admin_url( 'admin.php?page=glab_add_practitioner', 'http' );
        echo '<div class="wrap"><h2>Practitioner List <a style="color: grey;text-decoration: underline;font-size: 15px;" href="'.$new_prac_url.'">Add New</a></h2>';
        echo '<form id="glab-practitoner-list-table-form" method="post">';
        $list_table->prepare_items();
        $list_table->display();
        echo '</form>';
        /*echo '<form method="post">
		<input type="hidden" name="page" value="glab_prac" />';
        $list_table->search_box('search', 'search_id');
        echo '</form>';*/
        echo '</div>';
    }

    function delete() {
        $this->prac_layer->delete($_GET['prac']);
    }

    function perform_bulk_delete() {
        $this->prac_layer->bulk_delete($_POST['prac']);
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
                    $this->edit($_GET['prac']);
                    break;
                case 'set_schedule':
                    $this->set_schedule();
                    break;
                case 'delete':
                    $this->delete();
                    break;
                case 'activate':
                    $this->activate();
                    break;
                case 'deactivate':
                    $this->deactivate();
                    break;
            }
        }
    }

    function activate() {
        $this->prac_layer->activate($_GET['prac']);
    }

    function deactivate() {
        $this->prac_layer->deactivate($_GET['prac']);
    }

    function get_services($prac_id) {
        $services = $this->prac_layer->get_services($prac_id);
        echo glab_html_helper::get_services_option($services);
        exit;
    }

    

    

}
