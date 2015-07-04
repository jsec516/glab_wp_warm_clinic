<?php

class glab_customer {

    private $customer_layer;
    private $prac_layer;
    private $is_loaded;

    function __construct() {
        require_once 'lists/glab_customer_table.php';
        $this->customer_layer = new glab_customer_layer();
        $this->prac_layer = new glab_practitioner_layer();
        $this->is_loaded = false;
    }

    function load() {
        $this->perform_action();
        if (!$this->is_loaded)
            $this->load_list();
    }

    function add() {
        $saved = null;
        if (isset($_POST['customer_add_nonce']) && wp_verify_nonce($_POST['customer_add_nonce'], 'customer_add')) {        	$is_valid=$this->customer_layer->validate();        	if($is_valid===true)
        		$saved = $this->customer_layer->save();
        	else
        		$errors = $is_valid;
        }                $message_flag='glab_success_message_'.get_current_blog_id();
        if ($saved) {
        	update_option($message_flag, "customer saved successfully !");
        	$location = $_SERVER['REQUEST_URI'];
        	echo "<script>window.location.assign('{$location}');</script>";
        	exit;
        }
        $data['practitioners'] = $this->prac_layer->all();
        require_once 'forms/glab_customer_form.php';
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Customer  - Add New </h3></div>';
    	$saved_msg=get_option($message_flag);        if ($saved_msg) {            echo '<div class="updated">';            echo '<p>' . __($saved_msg, 'my-text-domain') . '</p>			    </div>';            update_option($message_flag, "");        }                if($errors){        	echo '<div class="error">';        	echo '<p>' . __($errors, 'my-text-domain') . '</p>			    </div>';        }
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_customer_form::add($data);
        echo '</div>';
        echo '</div>';
    }

    function edit($id) {

        $customer_id = trim($id);
        $info = $this->customer_layer->single_info($customer_id);
        if (!is_numeric($customer_id) || !$info)
            return false;
        if (isset($_POST['customer_update_nonce']) && wp_verify_nonce($_POST['customer_update_nonce'], 'customer_update')) {        	$is_valid=$this->customer_layer->validate(true, $customer_id);
        	if($is_valid===true)
        		$saved = $this->customer_layer->update($customer_id);
        	else
        		$errors = $is_valid;        }                $info = $this->customer_layer->single_info($customer_id);
        require_once 'forms/glab_customer_form.php';        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Customer  - Edit</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Updated!', 'my-text-domain') . '</p>
			    </div>';
        }        if($errors){
        	echo '<div class="error">';
        	echo '<p>' . __($errors, 'my-text-domain') . '</p>
			    </div>';
        }        
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        $params = array(
            'data' => $info,
            'practitioners' => $this->prac_layer->all()
        );        echo '<div class="form-wrapper">';
        glab_customer_form::edit($params);        echo '</div>';
        echo '</div>';
        $this->is_loaded = true;
    }

    function delete() {
        $this->customer_layer->delete($_GET['cust']);
    }

    function load_list() {
        $data = $this->customer_layer->all();
        $list_table = new glab_customer_table();
        $list_table->set_glab_data($data);        $new_cust_url = admin_url( 'admin.php?page=glab_add_customer', 'http' );
        echo '<div class="wrap"><h2>Customer List <a style="color: grey;text-decoration: underline;font-size: 15px;" href="'.$new_cust_url.'">Add New</a></h2>';
        $list_table->prepare_items();
        $list_table->display();
        echo '<form method="post">
		<input type="hidden" name="page" value="glab_customer" />';
        $list_table->search_box('search', 'search_id');
        echo '</form>';
        echo '</div>';
    }

    function perform_action() {
        if (isset($_GET['action'])) {
            switch (trim($_GET['action'])) {
                case 'edit':
                    $this->edit($_GET['cust']);
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
        $this->customer_layer->activate($_GET['cust']);
    }

    function deactivate() {
        $this->customer_layer->deactivate($_GET['cust']);
    }

    function filter_based_on() {
        //  abstact code based on suggestion.php
        if (isset($_REQUEST['firstName'])) {
            if(empty($_REQUEST['firstName']))
                return '';
            $res = $this->customer_layer->fetch_by_suggestion(trim($_REQUEST['firstName']));
        } else {
            if(empty($_REQUEST['lastName']))
                return '';
            $res = $this->customer_layer->fetch_by_suggestion('', trim($_REQUEST['lastName']));
        }
        
        $content = "";
        foreach ($res as $row) {
            $content.='<li class="t suggested_customer" data-firstname="'.$row['first_name'].'" data-lastname="'.$row['last_name'].'" data-email="'.$row['email'].'" data-id="'.$row['id'].'" data-phone="'.$row['phone'].'" data-cell="'.$row['cell'].'" data-work="'.$row['work'].'" >' . $row['first_name'] .' '. $row['last_name'] .' '. '(' . $row['email'] . ')' . '</li>';
        }
        echo $content;
        exit;
    }

    function get_valid_user(){
        $user_info = $this->customer_layer->get_valid_user();
        return $user_info;
    }
}
