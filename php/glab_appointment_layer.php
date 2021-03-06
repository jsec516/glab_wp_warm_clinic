<?php

class glab_appointment_layer {

    private $wpdb;
    private $table_name;
    private $service_table_name;
    private $user_table_name;
    private $practitioner_table_name;
    private $room_table_name;
    private $sync_event_table_name;
    private $clinic_layer;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'glab_cas_appointments';
        $this->service_table_name = 'glab_cas_services';
        $this->user_table_name = 'glab_cas_users';
        $this->practitioner_table_name = 'glab_cas_practitioners';
        $this->room_table_name = 'glab_cas_rooms';
        $this->sync_event_table_name = 'glab_cas_synced_appointments';
        $this->clinic_layer=new glab_clinic_layer();
    }

    function single_info($id) {
        $query = "SELECT * from {$this->table_name} WHERE id='{$id}' and blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_row($query, ARRAY_A);
        return $results;
    }
    
    function get_appt_tooltip_info(){
        $query="SELECT  
			a.*,CONCAT(u.first_name,' ',u.last_name) as patient_name,
			CONCAT(pr.first_name,' ',pr.last_name) as practitioner_name,
			s.name as treat_name 
		 FROM {$this->table_name} as a  
		 LEFT JOIN {$this->user_table_name} as u on u.id=a.user_id 
		 left join {$this->practitioner_table_name} as pr on pr.id=a.practitioner_id  
		 left join {$this->service_table_name} as s on s.id=a.service_id  
		 where a.id='".$_GET['app_id']."'";
	$row = $this->wpdb->get_row($query, ARRAY_A);
        return $row;
    }

    function get_hour_appts($date, $half_an_hour, $full_hour) {
        /* $query = "SELECT service_id,app_time,app_date,{$this->table_name}.id,duration as service_duration  
          FROM {$this->table_name}
          LEFT JOIN {$this->service_table_name} ON {$this->service_table_name}.id={$this->table_name}.service_id
          WHERE app_date='" . date('Y-m-d', $date) . "'
          AND  (LOWER({$this->table_name}.app_time)='" . strtolower($half_an_hour) . "'
          OR LOWER({$this->table_name}.app_time)='" . strtolower($full_hour) . "')
          AND {$this->table_name}.status='1'
          AND {$this->table_name}.blog_id='" . get_current_blog_id() . "'"; */
        $doc_condition = '';
        if (isset($_COOKIE['filterDocId']) AND $_COOKIE['filterDocId'] != 'ALL')
            $doc_condition = " AND practitioner_id='{$_COOKIE['filterDocId']}' ";

        $query = "SELECT service_id,app_time,app_date,{$this->table_name}.id,service_duration,{$this->table_name}.status as app_status 
		    	FROM {$this->table_name}
		    	LEFT JOIN {$this->service_table_name} ON {$this->service_table_name}.id={$this->table_name}.service_id
		    	WHERE (app_date='" . date('Y-m-d', $date) . "')   
		    	AND app_type = '0' 
		    	AND  (LOWER({$this->table_name}.app_time)='" . strtolower($half_an_hour) . "'
		    	OR LOWER({$this->table_name}.app_time)='" . strtolower($full_hour) . "') 
		    	{$doc_condition} 
		    	AND ({$this->table_name}.status='1' or {$this->table_name}.status='5') 
    			AND {$this->table_name}.blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        return $results;
    }

    function all() {
        $query = "SELECT  a.*,CONCAT(a.first_name,a.last_name) as cus_name, CONCAT(p.first_name,p.last_name) as prac_name,sn.name as service FROM " . $this->table_name . " AS a  
				 LEFT JOIN " . $this->practitioner_table_name . " AS p ON p.id=a.practitioner_id
				 LEFT JOIN " . $this->service_table_name . " AS sn ON sn.id=a.service_id   
				 WHERE a.blog_id='" . get_current_blog_id() . "' AND a.app_type='0'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        return $results;
    }

    function get_room_based_appointment($date) {
        $query = "SELECT count(id) as num_appt,service_id,room_id 
                    FROM {$this->table_name} 
                    WHERE app_date='{$date}' AND 
                    app_status='1' AND 
                    app_type='0' AND 
                    blog_id = '" . get_current_blog_id() . "' 
                    GROUP BY room_id";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        $tmp_array = array();
        foreach ($results as $row) {
            $tmp_array['service_id'] = $row['service_id'];
            $tmp_array['appointments'] = $row['num_appt'];
            $tmp_array['room_id'] = $row['room_id'];
            $output[] = $tmp_array;
        }
        return $output;
    }

    public function get_room_interval_appts($room_id, $dates, $time1, $time2) {
        $doc_condition = '';

        if (isset($_POST['selected_prac']) AND $_POST['selected_prac']):
            $doc_condition = " AND practitioner_id = '{$_POST['selected_prac']}'";
        endif;

        $query = "SELECT a.*, p.*, s.*, r.*, u.*,a.id AS appt_id FROM {$this->table_name} AS a, {$this->practitioner_table_name} AS p, {$this->service_table_name} AS s, {$this->room_table_name} AS r, {$this->user_table_name} AS u WHERE a.app_date ='$dates' and a.app_type='0' AND a.room_id='$room_id' AND a.user_id=u.id AND r.id=a.room_id AND p.id=a.practitioner_id AND s.id=a.service_id AND a.app_time BETWEEN '$time1' AND '$time2' AND a.app_time!='$time2' AND (a.status='1' or a.status='5') {$doc_condition} ORDER BY a.app_time";
        $results = $this->wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    public function get_week_day_appointments($date = '') {
        if (isset($_POST['selected_prac']) AND $_POST['selected_prac']):
            $query = "SELECT id,service_id,app_time,service_duration,status 
                            FROM {$this->table_name} 
                            WHERE (app_date='{$date}') AND app_type='0' AND (status='1' or status='5') AND practitioner_id='{$_POST['selected_prac']}'";
        else:
            $query = "SELECT id,service_id,app_time,service_duration,status 
                            FROM {$this->table_name} 
                            WHERE (app_date='{$date}') AND app_type='0' AND (status='1' or status='5')";
        endif;

        $result = $this->wpdb->get_results($query, ARRAY_A);

        $treatArr = array();
        $treat = 0;
        $endArr = array();
        foreach ($result as $row) {
            $treatArr[$treat]['service_id'] = $row['service_id'];
            $startArr = explode(':', $row['app_time']);
            $startArr[2] = 'am';
            if ($startArr[0] > 11)
                $startArr[2] = 'pm';
            $end_time = glab_slot_helper::get_appt_end_time($row['app_time'], $row['service_duration']);
            $endArr = explode(':', $end_time);
            $endArr[2] = 'am';
            if ($endArr[0] > 11)
                $endArr[2] = 'pm';
            $treatArr[$treat]['id'] = $row['id'];
            $treatArr[$treat]['duration'] = $row['service_duration'];
            $treatArr[$treat]['begin_time'] = $startArr;
            $treatArr[$treat]['end_time'] = $endArr;
            $treatArr[$treat]['app_status'] = $row['status'];
            $treat++;
        }

        $appointments = $treatArr;
        return $appointments;
    }

    public function get_app_title_info($id) {
        $safe_sql = "SELECT flag,first_name,last_name 
    			FROM {$this->table_name}
				LEFT JOIN {$this->user_table_name} ON {$this->user_table_name}.id = {$this->table_name}.user_id 
				LEFT JOIN {$this->service_table_name} ON {$this->service_table_name}.id = {$this->table_name}.service_id  
				WHERE {$this->table_name}.id = '{$id}'";

        $row = $this->wpdb->get_row($safe_sql, ARRAY_A);
        if (!empty($row)) {
            return $row;
        } else {
            return false;
        }
    }

    public function delete($id) {
        $columns=array('status'=>'3','update_at'=>time());
        $where=array('id' => $id, 'blog_id' => get_current_blog_id());
        $is_deleted = $this->wpdb->update($this->table_name,$columns,$where);
        return $is_deleted;
    }

    public function get_specific_day_appt($date) {
        $query = "select * from {$this->table_name} where app_date='{$date}'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        return $results;
    }

    public function get_practitioner_blocked_time($expected_date, $selectedPrac, $only_result=false) {
        $blocked_array = array();
        $query = "SELECT * 
                        FROM {$this->table_name} 
                        WHERE app_date='{$expected_date}' AND ((app_type='1' AND practitioner_id='{$selectedPrac}') OR app_type='2') AND blog_id='".get_current_blog_id()."'";

        $res = $this->wpdb->get_results($query, ARRAY_A);
        if($only_result)
            return $res;
        $i = 0;		
        foreach ($res as $row) {
            $blocked_array[$i]['start_time'] = glab_convert_helper::get_numeric_time($row['app_time']); //18:3 for 06:30pm
            $blocked_array[$i]['end_time'] = glab_convert_helper::get_numeric_time($row['app_end_time']);
            $i++;
        }
        return $blocked_array;
    }

    public function get_practitioner_appts($date, $prac_id) {
        $query = "SELECT * 
                        FROM {$this->table_name} 
                        WHERE app_date='{$date}' AND practitioner_id = '{$prac_id}' AND app_type = '0' and blog_id='".get_current_blog_id()."'";
        $result = $this->wpdb->get_results($query, ARRAY_A);
        return $result;
    }

    public function save_break_practitioner() {
        require_once 'helper/glab_slot_helper.php';
        $date_parts = explode("/", $_REQUEST['date']);
        $app_date = $date_parts[2] . "-" . $date_parts[0] . "-" . $date_parts[1];

        $time = time(); //appointment update and create time

        if ($_POST['off_flag'] == 'YES') {
            $exp_day = date('D', mktime(0, 0, 0, $date_parts[0], $date_parts[1], $date_parts[2]));
            $exp_day = strtolower($exp_day);
            $interval = $this->clinic_layer->get_clinic_interval($exp_day);
            $app_start_time = $interval['start_time'];
            $app_end_time = $interval['end_time'];
            $app_duration = glab_slot_helper::get_app_duration($app_start_time, $app_end_time); //duration of that appointment
        } else {
            $app_start_time = $_REQUEST['break_from']; //start time of that appointment
            $app_end_time = $_REQUEST['break_to'];  //end time of that appointment
            $app_duration = glab_slot_helper::get_app_duration($app_start_time, $app_end_time); //duration of that appointment
        }
        $columns=array(
            'practitioner_id'=>$_REQUEST['practitioner'],
            'app_date'=>$app_date,
            'app_time'=>$app_start_time,
            'app_end_time'=>$app_end_time,
            'service_duration'=>$app_duration,
            'update_at'=>$time,
            'app_type'=>'1',
            'blog_id'=>get_current_blog_id(),
            'blog_user_id'=>get_current_user_id()
        );
        if(isset($_POST['app_id']) && is_numeric($_POST['app_id'])){
            $where=array('id'=>trim($_POST['app_id']), 'blog_id'=>get_current_blog_id());
            return $this->wpdb->update($this->table_name, $columns, $where);
        }else{
            $columns['create_at']=$time;
            return $this->wpdb->insert($this->table_name, $columns);
        }
//        $ins_sql = "INSERT INTO {$this->table_name}  SET
//				practitioner_id = '{$_REQUEST['practitioner']}',
//				app_date = '{$app_date}',
//				app_time = '{$app_start_time}',
//				app_end_time = '{$app_end_time}',
//				service_duration = '{$app_duration}',
//				app_type = '1',
//				create_at = '{$time}',
//				update_at = '{$time}',
//                                blog_id='" . get_current_blog_id() . "',
//                                blog_user_id='" . get_current_user_id() . "'";

//        $is_inserted = $this->wpdb->query($ins_sql);
//        return $is_inserted;
    }

    function update_event_status($eventId) {
        $columns = array('status' => '1');
        $where = array(
            'blog_id' => get_current_blog_id(),
            'id' => $eventId
        );
        $this->wpdb->update($this->sync_event_table_name, $columns, $where);
    }

    function save_block_clinic() {
        require_once 'helper/glab_slot_helper.php';
        $date_parts = explode("/", $_REQUEST['date']);
        $app_date = $date_parts[2] . "-" . $date_parts[0] . "-" . $date_parts[1];

        $time = time(); //appointment update and create time

        if ($_POST['off_flag'] == 'YES') {
            $exp_day = date('D', mktime(0, 0, 0, $date_parts[0], $date_parts[1], $date_parts[2]));
            $exp_day = strtolower($exp_day);
            $interval = $this->clinic_layer->get_clinic_interval($exp_day);
            $app_start_time = $interval['start_time'];
            $app_end_time = $interval['end_time'];
            $app_duration = glab_slot_helper::get_app_duration($app_start_time, $app_end_time); //duration of that appointment
        } else {
            $app_start_time = $_REQUEST['break_from']; //start time of that appointment
            $app_end_time = $_REQUEST['break_to']; //end time of that appointment
            $app_duration = glab_slot_helper::get_app_duration($app_start_time, $app_end_time); //duration of that appointment
        }
        
        $app_start_time=  str_pad($app_start_time, 5, '0', STR_PAD_LEFT);

        $columns=array(
            'app_date'=>$app_date,
            'app_time'=>$app_start_time,
            'app_end_time'=>$app_end_time,
            'service_duration'=>$app_duration,
            'update_at'=>$time,
            'app_type'=>'2',
            'blog_id'=>get_current_blog_id(),
            'blog_user_id'=>get_current_user_id()
        );
        if(isset($_POST['app_id']) && is_numeric($_POST['app_id'])){
            $where=array('id'=>trim($_POST['app_id']), 'blog_id'=>get_current_blog_id());
            return $this->wpdb->update($this->table_name, $columns, $where);
        }else{
            $columns['create_at']=$time;
            return $this->wpdb->insert($this->table_name, $columns);
        }
//        $ins_sql = "INSERT INTO {$this->table_name} SET
//				app_date = '{$app_date}',
//				app_time = '{$app_start_time}',
//				app_end_time = '{$app_end_time}',
//				service_duration = '{$app_duration}',
//				app_type = '2',
//				create_at = '{$time}',
//				update_at = '{$time}',
//                                blog_id='" . get_current_blog_id() . "',
//                                blog_user_id='" . get_current_user_id() . "'";
//
//        $is_inserted = $this->wpdb->query($ins_sql);
//        return $is_inserted;
    }

    public function get_room_appts($formated_codedate, $room_idd) {
        $query = "select * from {$this->table_name} where app_date ='$formated_codedate' AND room_id='$room_idd' AND blog_id='" . get_current_blog_id() . "'";
        $result = $this->wpdb->get_results($query, ARRAY_A);
        return $result;
    }

    public function get_user_history($user_id) {
        $query = "SELECT a.id as id,s.name as flag_name,CONCAT(p.first_name,' ',p.last_name) as doctors_name,app_date,app_time,a.status FROM {$this->table_name} as a "
                . "LEFT JOIN {$this->service_table_name} as s ON s.id=a.service_id "
                . "LEFT JOIN {$this->practitioner_table_name} as p ON p.id=a.practitioner_id "
                . "WHERE a.user_id='$user_id' AND a.blog_id='" . get_current_blog_id() . "'";
        $result = $this->wpdb->get_results($query, ARRAY_A);
        return $result;
    }

    public function save_regular_appt($params) {
        extract($params);
        $columns = array(
            'user_id' => $uid,
            'practitioner_id' => $doc_id,
            'first_name' => $fname,
            'last_name' => $lname,
            'email' => $cus_email,
            'service_id' => $treat,
            'service_duration' => $total_treat_dura,
            'app_date' => date('Y-m-d', strtotime($date)),
            'app_time' => $final_codHour,
            'app_end_time' => $end_time,
            'room_id' => $room_insert,
            'reminder_type' => $reminder,
            'status' => '1',
            'reminder_status' => 'N',
            'reminder_sent' => '',
            'pt_comments' => '',
            'dr_comments' => '',
            'is_synced' => '',
            'cancel_reason' => '',
            'app_type' => 0,
            'blog_id' => get_current_blog_id(),
            'blog_user_id' => get_current_user_id(),
            'create_at' => time(),
            'update_at' => time()
        );
        /* $this->wpdb->show_errors();
          $this->wpdb->insert($this->table_name, $columns);
          $this->wpdb->hide_errors();
          echo $this->wpdb->last_query; */
        if ($this->wpdb->insert($this->table_name, $columns)) {
            if (isset($_REQUEST['patternId'])) {
                $this->update_event_status($_REQUEST['patternId']);
            }
            return 'successfully';
        } else {
            return 'unsuccessfully';
        }
    }

    public function update_regular_appt($params) {
        extract($params);
        $columns = array(
            'user_id' => $uid,
            'practitioner_id' => $doc_id,
            'first_name' => $fname,
            'last_name' => $lname,
            'email' => $cus_email,
            'service_id' => $treat,
            'service_duration' => $total_treat_dura,
            'app_date' => date('Y-m-d', strtotime($date)),
            'app_time' => $final_codHour,
            'app_end_time' => $end_time,
            'room_id' => $room_insert,
            'reminder_type' => $reminder,
            'status' => '1',
            'reminder_status' => 'N',
            'reminder_sent' => '',
            'pt_comments' => '',
            'dr_comments' => '',
            'is_synced' => '',
            'cancel_reason' => '',
            'app_type' => 0,
            'blog_user_id' => get_current_user_id(),
            'update_at' => time()
        );

        $where_clause = array(
            'id' => $app_id, 'blog_id' => get_current_blog_id()
        );

        if ($this->wpdb->update($this->table_name, $columns, $where_clause)) {
            if (isset($_REQUEST['patternId'])) {
                $this->update_event_status($_REQUEST['patternId']);
            }
            return 'successfully';
        } else {
            return 'unsuccessfully';
        }
    }

    function save_app($columns) {
        $this->wpdb->insert($this->table_name, $columns);
    }

    function cancel_app($app_id, $reason, $user_comment, $user_id) {
        $where_clause = array('blog_id' => get_current_blog_id(), 'id' => $app_id, 'user_id' => $user_id);
        $columns = array('pt_comments' => $user_comment, 'cancel_reason' => $reason, 'status' => '3');
        $is_updated = $this->wpdb->update($this->table_name, $columns, $where_clause);
        return $is_updated;
    }

}
