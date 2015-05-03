<?php

class glab_waiting_layer {

	private $wpdb;
	private $table_name;
	private $service_table_name;
	private $user_table_name;
	private $practitioner_table_name;

	function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table_name = 'glab_cas_waiting_appointments';
		$this->service_table_name = 'glab_cas_services';
		$this->user_table_name = 'glab_cas_users';
		$this->practitioner_table_name = 'glab_cas_practitioners';
	}

	function single_info($id) {
		$query = "SELECT {$this->table_name}.*, u.first_name, u.last_name, u.email, u.phone, u.cell, u.work, u.contact_me, u.primary_phone from {$this->table_name}		left join {$this->user_table_name} as u ON u.id={$this->table_name}.user_id WHERE {$this->table_name}.id='{$id}' and {$this->table_name}.blog_id='" . get_current_blog_id() . "'";
		$row = $this->wpdb->get_row($query, ARRAY_A);
		return $row;
	}

	function all() {
		$query = "SELECT  w.*,CONCAT(u.first_name,' ',u.last_name) as cus_name, CONCAT(p.first_name,p.last_name) as prac_name,s.name as service FROM " . $this->table_name . " AS w
				LEFT JOIN " . $this->practitioner_table_name . " AS p ON p.id=w.practitioner_id
						LEFT JOIN " . $this->service_table_name . " AS s ON s.id=w.service_id
								LEFT JOIN " . $this->user_table_name . " AS u ON u.id=w.user_id
										WHERE w.blog_id='" . get_current_blog_id() . "'";
		$results = $this->wpdb->get_results($query, ARRAY_A);
		return $results;
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

	//    public function get_specific_day_appt($date) {
	//        $query = "select * from {$this->table_name} where app_date='{$date}'";
	//        $results = $this->wpdb->get_results($query, ARRAY_A);
	//        return $results;
	//    }

	public function get_specific_day_waiting($day) {
		$query = "SELECT {$day} FROM {$this->table_name} where $day!='' AND blog_id='".get_current_blog_id()."'";
		$results = $this->wpdb->get_results($query, ARRAY_A);
		return $results;
	}

	public function get_waiting_with_user_info($expected_week_day){
		$query = "SELECT w.*,u.first_name,u.last_name FROM {$this->table_name} as w LEFT JOIN {$this->user_table_name} as u on u.id=w.user_id where w.$expected_week_day!='' AND w.blog_id='".get_current_blog_id()."'";
		$results = $this->wpdb->get_results($query, ARRAY_A);
		return $results;
	}

	public function waiting_info_with_user(){
		$query="SELECT pr.first_name,pr.last_name,s.name as service,w.app_reminder,w.{$_REQUEST['exp_day']} as waiting_duration,w.id as waiting_id,w.practitioner_id as practitioner,w.user_id as patient,w.service_id as treatId,u.first_name as patient_fname,u.last_name as patient_lname FROM {$this->table_name} as w LEFT JOIN {$this->user_table_name} as u ON u.id=w.user_id LEFT JOIN {$this->practitioner_table_name} as pr on pr.id=w.practitioner_id LEFT JOIN {$this->service_table_name} as s on s.id=w.practitioner_id where w.id='{$_POST['waiting_id']}' and w.blog_id='".get_current_blog_id()."'";
		$row = $this->wpdb->get_row($query, ARRAY_A);
		return $row;

	}

	public function get_practitioner_blocked_time($expected_date, $selectedPrac) {
		$blocked_array = array();
		$query = "SELECT *
		FROM {$this->table_name}
		WHERE app_date='{$expected_date}' AND ((app_type='1' AND practitioner_id='{$selectedPrac}') OR app_type='2')";

		$res = $this->wpdb->get_results($query, ARRAY_A);
		$i = 0;
		foreach ($res as $row) {
			$blocked_array[$i]['start_time'] = get_numeric_time($row->app_time);
			$blocked_array[$i]['end_time'] = get_numeric_time($row->app_end_time);
			$i++;
		}
		return $blocked_array;
	}

	public function update($data) {		$mon = NULL;		$tue = NULL;		$wed = NULL;		$thu = NULL;		$fri = NULL;		$sat = NULL;		$sun = NULL;		$selected_user = 0;		$week_days = array(				"mon" => "mon",				"tue" => "tue",				"wed" => "wed",				"thu" => "thu",				"fri" => "fri",				"sat" => "sat",				"sun" => "sun"		);		if ($data) {			if (!isset($data['days']) OR ! isset($data['wait_prac'])) {				return false;			} else {				foreach ($data['days'] as $day) {					$interval = glab_convert_helper::prepare_wait_interval($data, $day, $week_days);					$$day = $interval;				}			}			if ($data['selected_user']) {				$selected_user = $data['selected_user'];			} else {				$customer_layer = new glab_customer_layer();				$cust_info = array(						'fname' => $data['first_name'],						'lname' => $data['last_name'],						'cus_email' => $data['email'],						'upass' => $data['new_password'],						'phone' => $data['phone'],						'is_detail' => true,						'phone_cell' => $data['phone_cell'],						'phone_work' => $data['phone_work'],						'contact_with' => $data['contact'],						'primary_phone' => $data['primary_phone'],						'primary_doctor' => $data['wait_prac']				);				$selected_user = $customer_layer->save_n_inserted_id($cust_info);			}			$contact = $data['contact'];			$treatments = $data['service'];			$columns = array(					'user_id' => $selected_user,					'mon' => $mon,					'tue' => $tue,					'wed' => $wed,					'thu' => $thu,					'fri' => $fri,					'sat' => $sat,					'sun' => $sun,					'practitioner_id' => $data['wait_prac'],					'app_reminder' => $data['contact'],					'service_id' => $data['service'],					'blog_id' => get_current_blog_id(),					'blog_user_id' => get_current_user_id(),					'create_at' => time(),					'update_at' => time()			);			$where=array('id'=>$_GET['waiting'], 'blog_id'=>get_current_blog_id());			$this->wpdb->update($this->table_name, $columns, $where);			return true;		} else {			return false;		}	}

	/*
	* save_waiting: will save waiting appointment
	*/

	public function save($data) {
		//Copy & paste code
		$mon = NULL;
		$tue = NULL;
		$wed = NULL;
		$thu = NULL;
		$fri = NULL;
		$sat = NULL;
		$sun = NULL;
		$selected_user = 0;
		$week_days = array(
				"mon" => "mon",
				"tue" => "tue",
				"wed" => "wed",
				"thu" => "thu",
				"fri" => "fri",
				"sat" => "sat",
				"sun" => "sun"
		);
		if ($data) {

			if (!isset($data['days']) OR ! isset($data['wait_prac'])) {
				return false;
			} else {
				foreach ($data['days'] as $day) {
					$interval = glab_convert_helper::prepare_wait_interval($data, $day, $week_days);
					$$day = $interval;
				}
			}
			if ($data['selected_user']) {
				$selected_user = $data['selected_user'];
			} else {
				$customer_layer = new glab_customer_layer();
				$cust_info = array(
						// need to define parameters
						'fname' => $data['first_name'],
						'lname' => $data['last_name'],
						'cus_email' => $data['email'],
						'upass' => $data['new_password'],
						'phone' => $data['phone'],
						'is_detail' => true,
						'phone_cell' => $data['phone_cell'],
						'phone_work' => $data['phone_work'],
						'contact_with' => $data['contact'],
						'primary_phone' => $data['primary_phone'],
						'primary_doctor' => $data['wait_prac']
				);
				$selected_user = $customer_layer->save_n_inserted_id($cust_info);
			}

			$contact = $data['contact'];
			$treatments = $data['service'];
			$columns = array(
					'user_id' => $selected_user,
					'mon' => $mon,
					'tue' => $tue,
					'wed' => $wed,
					'thu' => $thu,
					'fri' => $fri,
					'sat' => $sat,
					'sun' => $sun,
					'practitioner_id' => $data['wait_prac'],
					'app_reminder' => $data['contact'],
					'service_id' => $data['service'],
					'blog_id' => get_current_blog_id(),
					'blog_user_id' => get_current_user_id(),
					'create_at' => time(),
					'update_at' => time()
			);
			$this->wpdb->insert($this->table_name, $columns);
			//$waiting_id = $this->wpdb->insert_id;
			return true;
		} else {
			return false;
		}
		//TODO: refactor code
	}

	function delete($id) {
		$where = array('id' => $id, 'blog_id' => get_current_blog_id());
		$is_deleted = $this->wpdb->delete($this->table_name, $where);
		return $is_deleted;
	}

	function get_user_history($user_id) {
		$query = "SELECT w.*,s.name as flag_name,CONCAT(p.first_name,' ',p.last_name) as doctors_name FROM {$this->table_name} as w "
		. "LEFT JOIN {$this->service_table_name} as s ON s.id=w.service_id "
		. "LEFT JOIN {$this->practitioner_table_name} as p ON p.id=w.practitioner_id "
		. "WHERE w.user_id='$user_id' AND w.blog_id='" . get_current_blog_id() . "'";
		$result = $this->wpdb->get_results($query, ARRAY_A);
		return $result;
	}

}
