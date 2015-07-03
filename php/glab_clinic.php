<?php

class glab_clinic {

    private $clinic_layer;
    private $service_layer;
    private $prac_layer;

    function __construct() {
        $this->clinic_layer = new glab_clinic_layer();
        $this->service_layer = new glab_service_layer();
        $this->prac_layer = new glab_practitioner_layer();
    }

    function load() {
        $saved = $this->perform_action();
		$message_flag='glab_success_message_'.get_current_blog_id();
        $this->load_settings_assets();
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Clinic Configuration</h3></div>';
        if ($saved) {
            update_option($message_flag, "room saved successfully !");
            	$location = $_SERVER['REQUEST_URI'];
            	echo "<script>window.location.assign('{$location}');</script>";
            	exit;
        }
		
		$saved_msg=get_option($message_flag);
        if ($saved_msg) {
            echo '<div class="updated">';
            echo '<p>' . __($saved_msg, 'my-text-domain') . '</p>
			    </div>';
            update_option($message_flag, "");
        }
        $rooms = $this->clinic_layer->get_rooms();
        $services = $this->service_layer->get_active_services();
        $active_services_id=array();
        foreach($services as $ser)
        	array_push($active_services_id, $ser['id']);
        $this->load_main_assets();
        glab_clinic_form::display_rooms($rooms, $services, $active_services_id);
        echo '<div class="generator_container">';
        glab_clinic_form::generate_make_room_html();
        glab_clinic_form::generate_available_service_html($services);
        echo '</div>';
        echo '</div>';
    }
    
    public function generate_duplicate_room($name, $duplicate_id){
        $_POST['glab_room_name']=$name;
        $room_id = $this->clinic_layer->save_room();
        if(is_numeric($room_id)){
            $room_services = $this->service_layer->get_room_services($duplicate_id);
            foreach($room_services as $service){
                $this->service_layer->add_room_service($room_id, $service['id']);
            }
            return true;
        }else{
            return false;
        }
    }

    private function load_main_assets() {
        $ajax_url = plugins_url('glab_clinic/glab_ajax_clinic.php');
        echo "<script>var glab_ajax_url='{$ajax_url}'; </script>";
        /*wp_enqueue_script('jquery-dradrop-plugin', plugins_url('glab_clinic/assets/js/jquery.glab_dragdrop.js'), array('jquery', 'jquery-ui-d
		raggable', 'jquery-ui-droppable'), false, true);*/
		wp_enqueue_style('jquery-fancy-style', plugins_url('glab_clinic/assets/tool/fancybox/jquery.fancybox.css?v=2.1.5'));
		wp_enqueue_script('jquery-fancy-script', plugins_url('glab_clinic/assets/tool/fancybox/jquery.fancybox.pack.js?v=2.1.5'), array('jquery'), false, true);
		wp_enqueue_script('jquery-clinic-settings', plugins_url('glab_clinic/assets/js/jquery.clinic-settings.js'), array('jquery','jquery-fancy-script'), false, true);
		
		
    }

    private function perform_action() {
        if (isset($_POST['glab_room_name']) &&
                trim($_POST['glab_room_name'])) {
            return $this->clinic_layer->save_room();
        }
		
		if (isset($_POST['save_services'])){
			$saved=false;
			$this->service_layer->delete_room_services($_POST['room_id']);
			foreach($_POST['services'] as $service){
				$service_saved = $this->service_layer->add_room_service(trim($_POST['room_id']), trim($service));
				if($service_saved && !$saved){
					$saved = true;
				}
			}
			return $saved;
		}
		
		if(isset($_POST['update_room_services']) && is_numeric($_POST['room_id'])){
			$saved=false;
			$this->service_layer->delete_room_services($_POST['room_id']);	
			foreach($_POST['services'] as $service){
				$service_saved = $this->service_layer->add_room_service(trim($_POST['room_id']), trim($service));
				if($service_saved && !$saved){
					$saved = true;
				}
			}
			return $saved;
		}

        return null;
    }

    private function load_settings_assets() {
        require_once 'forms/glab_clinic_form.php';
        $base_url = plugins_url("glab_clinic");
        echo "<script type='text/javascript'>var base_url='{$base_url}';</script>";
        /* wp_enqueue_style('glab-admin-ui-css',
          'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/smoothness/jquery-ui.css',
          false,
          PLUGIN_VERSION,
          false);
          wp_enqueue_script('glab-dragdrop-script', plugins_url('glab_clinic/assets/js/jquery.glab_dragdropevents.js'), array('jquery','jquery-ui-dialog','jquery-ui-draggable','jquery-ui-droppable','jquery-ui-sortable'), null, true);
          wp_enqueue_script('settings-script', plugins_url('glab_clinic/assets/js/jquery.glab_clinic_settings.js'), array('jquery','jquery-ui-dialog','jquery-ui-draggable','jquery-ui-droppable','jquery-ui-sortable'), null, true);
          echo '<link type="text/css" rel="stylesheet" href="' . $base_url . '/assets/css/glab_clinic_settings.css" />'; */
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
    }

    function set_schedule() {
        require_once 'forms/glab_clinic_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Clinic Schedule</h3></div>';
        $saved = null;
        if (isset($_POST['clinic_schedule_nonce']) && wp_verify_nonce($_POST['clinic_schedule_nonce'], 'clinic_schedule')) {
            $saved = $this->clinic_layer->update_schedule();
        }
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }
        $data['schedule'] = $this->clinic_layer->get_schedule();
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('schedule_script', plugins_url('glab_clinic/assets/js/jquery.schedule.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script', 'schedule_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_clinic_form::display_schedule($data);
        glab_clinic_form::set_schedule($data);
        echo '</div>';
        echo '</div>';
    }

    function cancellation_poll() {
        $saved = null;
        if (isset($_POST['cpoll_add_nonce']) && wp_verify_nonce($_POST['cpoll_add_nonce'], 'cpoll_add')) {
            $saved = $this->clinic_layer->update_cancel_poll();
        }
        $data['info'] = $this->clinic_layer->get_poll_option();
        $data['base_url'] = plugins_url('glab_clinic/assets');
        require_once 'forms/glab_clinic_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Reasons For Cancellation Poll</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }
        $asset_url = plugins_url('glab_clinic/assets');
        echo "<script type='text/javascript'>
            var  glab_asset_url='{$asset_url}';
        </script>";
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'), array('jquery'));
        wp_enqueue_script('poll_script', plugins_url('glab_clinic/assets/js/jquery.glab_poll.js'), array('valdation_script'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('poll_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_clinic_form::cancellation_poll($data);
        echo '</div>';
        echo '</div>';
    }

    function user_confirmation() {
        $saved = null;
        if (isset($_POST['cmail_add_nonce']) && wp_verify_nonce($_POST['cmail_add_nonce'], 'cmail_add')) {
            $saved = $this->clinic_layer->update_confirmation_mail();
        }
        $data['info'] = $this->clinic_layer->get_cfm_mail_format();
        require_once 'forms/glab_clinic_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Customer\'s Confirmation Mail</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_clinic_form::confirmation_email($data);
        echo '</div>';
        echo '</div>';
    }

    function import() {
        require_once 'forms/glab_clinic_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Import Data</h3></div>';
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_clinic_form::import();
        echo '</div>';
        echo '</div>';
    }
    
    function frontend_settings(){
        require_once 'forms/glab_clinic_form.php';
        $saved = null;
        if (isset($_POST['fs_add_nonce']) && wp_verify_nonce($_POST['fs_add_nonce'], 'fs_add')) {
            $saved = $this->clinic_layer->update_frontend_settings();
        }
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Frontend Settings</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Saved!', 'my-text-domain') . '</p>
			    </div>';
        }
        $data['info'] = $this->clinic_layer->get_frontend_settings();
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_clinic_form::frontend_settings($data);
        echo '</div>';
        echo '</div>';
    }
    
    
    function get_room_service_html(){
    	$room_services=$this->service_layer->get_room_services($_GET['room_id'], true);
    	$active_services=$this->service_layer->get_active_services();
    	require_once 'forms/glab_clinic_form.php';
    	glab_clinic_form::change_room_services($active_services, $room_services);
    	exit;
    }

}
