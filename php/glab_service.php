<?php

class glab_service {

    private $service_layer;
    private $is_loaded;

    function __construct() {
        require_once 'lists/glab_service_table.php';
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
        $message_flag='glab_success_message_'.get_current_blog_id();
        if (isset($_POST['service_add_nonce']) && wp_verify_nonce($_POST['service_add_nonce'], 'service_add')) {
            $saved = $this->service_layer->save();
            if($saved){
            	update_option($message_flag, "service saved successfully !");
            	$location = $_SERVER['REQUEST_URI'];
            	echo "<script>window.location.assign('{$location}');</script>";
            	exit;
            }
        }
        require_once 'forms/glab_service_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Add New Service</h3></div>';
        $saved=get_option($message_flag);
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __($saved, 'my-text-domain') . '</p>
			    </div>';
            update_option($message_flag, "");
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('wp-color-picker', 'valdation_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_service_form::add();
        echo '</div>';
        echo '</div>';
    }

    function edit($id) {
        $saved = null;
        $service_id = trim($id);
        $info = $this->service_layer->single_info($service_id);
        if (!is_numeric($service_id) || !$info)
            return false;
        if (isset($_POST['service_update_nonce']) && wp_verify_nonce($_POST['service_update_nonce'], 'service_update')) {
            $saved = $this->service_layer->update($id);
            $info = $this->service_layer->single_info($service_id);
        }
        require_once 'forms/glab_service_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Edit Service</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('wp-color-picker', 'valdation_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_service_form::edit($info);
        echo '</div>';
        echo '</div>';
        $this->is_loaded = true;
    }

    function delete() {
        $this->service_layer->delete($_GET['service']);
    }

    function perform_bulk_delete() {
        $this->service_layer->bulk_delete($_POST['service']);
    }

    function load_list() {

        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        $data = $this->service_layer->all();
        $list_table = new glab_service_table();
        $list_table->set_glab_data($data);
        echo '<div class="wrap"><h2>Service List</h2>';
        echo '<form id="glab-service-list-table-form" method="post">';
        $list_table->prepare_items();
        $list_table->display();
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
                    $this->edit($_GET['service']);
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
        $this->service_layer->activate($_GET['service']);
    }

    function deactivate() {
        $this->service_layer->deactivate($_GET['service']);
    }

}
