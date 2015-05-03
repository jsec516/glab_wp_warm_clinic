<?php

class glab_reminder_layer {

    private $wpdb;
    private $table_name;
    private $prac_table_name;
    
    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'glab_cas_reminders';
        $this->prac_table_name='glab_cas_practitioners';
    }

    function get_call_content() {
        $result = '';
        return $result;
    }

    function save() {
        $is_reminder_saved = false;
        if ($_POST['reminder_type'] == '1') {
            $is_reminder_saved = $this->save_email_reminder();
        } else {
            $is_reminder_saved = $this->save_call_reminder($_POST);
        }
        return $is_reminder_saved;
    }
    
    function update(){
        $is_reminder_updated = false;
        if ($_POST['reminder_type'] == '1') {
            $is_reminder_updated = $this->update_email_reminder();
        } else {
            $is_reminder_updated = $this->update_call_reminder($_POST);
        }
        return $is_reminder_saved;
    }

    function save_email_reminder() {
        $_POST['email']['reminder_type'] = $_POST['reminder_type'];
        $_POST['email']['service_ids'] = implode(',', $_POST['service_ids']);
        $_POST['email']['attach_file'] = $this->get_attached_file_url();
        $_POST['email']['blog_id'] = get_current_blog_id();
        $_POST['email']['blog_user_id'] = get_current_user_id();
        $_POST['email']['create_at'] = time();
        $_POST['email']['update_at'] = time();
        return $this->wpdb->insert($this->table_name, $_POST['email']);
    }
    
    function save_call_reminder($data=array()) {
        $call=array();
        $call['practitioner_id'] = trim($data['call']['practitioner']);
        $call['service_ids'] = implode(',',$data['services']);
        $attach_files=null;
        if (isset($data['attached_file_names']))
            $attach_files = implode(',', $data['attached_file_names']);
        $call['is_file_played'] = ($attach_files) ? 'Y' : 'N';
        $call['call_format'] = $data['saying_text_msg'];
        $call['voice_type'] = trim($data['call']['voice_type']);
        $call['reminder_day'] = trim($data['call']['day']);
        $call['call_element_format'] = trim($data['formatOrder']);
        $call['attach_file']=$attach_files;
        $call['create_at'] = time();
        $call['update_at'] = time();
        $call['blog_id'] = get_current_blog_id();
        $call['blog_user_id'] = get_current_user_id();
        return $this->wpdb->insert($this->table_name,$call);
    }

    public function get_attached_file_url() {
        // upload file and return url
        if (!empty($_FILES)) {
            if (isset($_FILES['attach_url'])) {
                $file = wp_upload_bits($_FILES['attach_url']['name'], null, @file_get_contents($_FILES['attach_url']['tmp_name']));
                if (FALSE === $file['error']) {
                    return $file['url'];
                } else {
                    return '';
                }
            }
        }
        return '';
    }

    function update_email_reminder() {
    	
        $service_ids = implode(',', $_POST['service_ids']);
        if (!empty($_FILES)) {
        	if (isset($_FILES['attach_url'])) {
        		$attach_file = $this->get_attached_file_url();
        	}
        }
        $_POST['email']['update_at'] = time();
        $update_columns=array('practitioner_id'=>$_POST['email']['practitioner_id'],'subject'=>$_POST['email']['subject'],'email_content'=>$_POST['email']['email_content'],'reminder_day'=>$_POST['email']['reminder_day'],'service_ids'=>$service_ids,'update_at'=>time());
        if(isset($attach_file)){
        	$update_columns['attach_file']=$attach_file;
        }
        
        $where_clause=array('id'=>$_POST['er_id'],'blog_id'=>get_current_blog_id());
        return $this->wpdb->update($this->table_name, $update_columns, $where_clause);
    }
    
    function update_call_reminder($data=array()) {
        $call=array();
        $call['practitioner_id'] = trim($data['call']['practitioner']);
        $call['service_ids'] = implode(',',$data['services']);
        $attach_files=null;
        if (isset($data['attached_file_names']))
            $attach_files = implode(',', $data['attached_file_names']);
        $call['is_file_played'] = ($attach_files) ? 'Y' : 'N';
        $call['call_format'] = $data['saying_text_msg'];
        $call['voice_type'] = trim($data['call']['voice_type']);
        $call['reminder_day'] = trim($data['call']['day']);
        $call['call_element_format'] = trim($data['formatOrder']);
        $call['attach_file']=$attach_files;
        $call['update_at'] = time();
        $call['blog_id'] = get_current_blog_id();
        $call['blog_user_id'] = get_current_user_id();
        $where_clause=array('blog_id'=>get_current_blog_id(), 'id'=>$data['cr_id']);
        return $this->wpdb->wpdate($this->table_name,$call,$where_clause);
    }
    
    function all() {
        $query = "SELECT r.*,CONCAT(p.first_name,' ',p.last_name) as prac_name 
				 FROM " . $this->table_name . " AS r 
				 LEFT JOIN " . $this->prac_table_name . " AS p ON p.id=r.practitioner_id   
				 WHERE r.blog_id='".get_current_blog_id()."' ORDER BY id DESC";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            $output[$row['id']] = $row;
        }
        return $output;
    }
    
    function prepare_table_data($data){
        $result=$data;
        $service_layer = new glab_service_layer();
        foreach($data as $row){
            $result[$row['id']]['services_name']=$service_layer->get_names($row['service_ids']);
            if($result[$row['id']]['reminder_type']=='1'){
                $result[$row['id']]['content']=  substr($result[$row['id']]['email_content'], 0,100);
            }else{
                $result[$row['id']]['content']=  substr($result[$row['id']]['call_format'], 0,100);
            }
        }
        return $result;
    }
    
    function single_info($id) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->table_name . "
				WHERE id=%d AND blog_id=%d", array(
            $id,
            get_current_blog_id(),
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }

        return $result;
    }
    
    public function prac_avail_services($prac_id, $reminder_type, $service_ids=''){
        $service_ids_array=explode(',',$service_ids);
        $service_layer = new glab_service_layer();
        $all_services = $service_layer->practitioners($prac_id);
        $blocked_services = $this->get_blocked_prac_services($prac_id,$reminder_type);
        $avail_services=array();
        foreach($all_services as $service){
            if(!empty($service_ids_array) && in_array($service['id'], $service_ids_array)){
                array_push($avail_services, $service);
            }elseif(!in_array($service['id'],$blocked_services)){
                array_push($avail_services, $service);
            }
        }
        return $avail_services;
    }
    
    public function get_blocked_prac_services($prac_id,$reminder_type){
       $query = "SELECT service_ids FROM {$this->table_name} WHERE practitioner_id='{$prac_id}' AND reminder_type='{$reminder_type}'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $blocked_services=array();
        foreach($results as $row){
            $service_array=  explode(',', $row['service_ids']);
            $blocked_services = array_merge($blocked_services, $service_array);
        }
        return $blocked_services;
    }

}
