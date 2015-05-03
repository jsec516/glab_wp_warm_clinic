<?php

class glab_practitioner_layer {

	private $wpdb;
	private $table_name;
	private $service_table_name;
	private $service_name;
	private $prac_schedule_table_name;

	function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table_name = 'glab_cas_practitioners';
		$this->prac_schedule_table_name = 'glab_cas_practitioner_hours';
		$this->service_table_name = 'glab_cas_practitioner_services';
		$this->service_name = 'glab_cas_services';
	}

	function all($with_inactive = true) {
		if ($with_inactive)
			$status = "p.status IN ('0','1')";
		else
			$status = "p.status IN ('1')";
		$query = "SELECT p.id as id,CONCAT(p.first_name,' ',p.last_name) as name,
				email,p.description as description,GROUP_CONCAT(sn.name) as service_list,p.status as status
				 FROM ".$this->service_table_name." AS s
				 LEFT JOIN ".$this->table_name." AS p ON p.id=s.practitioner_id
				 LEFT JOIN ".$this->service_name." AS sn ON sn.id=s.service_id
				 WHERE s.practitioner_id IN (SELECT id FROM ".$this->table_name." WHERE $status) and p.blog_id='".get_current_blog_id()."' GROUP BY s.practitioner_id";
		$results = $this->wpdb->get_results($query, ARRAY_A);
		$output = array();
		foreach ($results as $row) {
			$output[$row['id']] = $row;
		}
		return $output;
	}

	function delete($id) {
		$safe_sql = $this->wpdb->prepare("UPDATE ".$this->table_name." SET
				status=%s WHERE id=%d", array(
			'-1',
			$id
		));
		$is_updated = $this->wpdb->query($safe_sql);
		return $is_updated;
	}

	function bulk_delete($id_array) {
		$ids = implode(",", $id_array);
		$safe_sql = $this->wpdb->prepare("UPDATE ".$this->table_name." SET
				status=%s WHERE id IN ({$ids}) and blog_id=%d", array(
			'-1',
			get_current_blog_id()
		));
		$is_updated = $this->wpdb->query($safe_sql);
		return $is_updated;
	}

	function validate($update=false, $prac_id='') {
		if(!$_POST['prac_treat_list']){
			return 'at least one service need to select';
		}
		$is_email_valid = $this->validate_email(trim($_POST['prac_email']), $update, $prac_id);
		if ($is_email_valid) {
			return true;
		} else {
			return 'email already taken or not in valid format';
		}
	}

	private function validate_email($email, $update = false, $prac_id = '') {
		if (!is_email($_POST['prac_email'])) {
			return false;
		}

		if ($prac_id) {
			$prac_info = $this->single_info($prac_id);
			if ($prac_info) {
				if ($prac_info['email'] == $email)
					return true;
			}
		}

		// check whether email is already exist or not
		$safe_sql = $this->wpdb->prepare("SELECT * FROM ".$this->table_name."
				WHERE email=%s and blog_id=%d", array(
			$email,
			get_current_blog_id()
		));
		$result = $this->wpdb->get_row($safe_sql, ARRAY_A);

		if (empty($result)) {
			return true;
		} else {
			return false;
		}
	}

	function save() {
		$is_created = false;
		$this->wpdb->query('START TRANSACTION');
		$safe_sql = $this->wpdb->prepare("
					INSERT INTO $this->table_name
					( first_name, last_name, email, description, status, blog_id, blog_user_id, create_at, update_at )
					VALUES ( %s, %s, %s, %s, %s, %d, %d, %d, %d  )
					", array(
			$_POST['prac_first_name'],
			$_POST['prac_last_name'],
			$_POST['prac_email'],
			$_POST['prac_description'],
			'1',
			get_current_blog_id(),
			get_current_user_id(),
			time(),
			time()
		));
		$is_basic_created = $this->wpdb->query($safe_sql);
		$id = $this->wpdb->insert_id;
		$is_service_created = true;
		
			foreach ($_POST['prac_treat_list'] as $service) {
				$ins_sql = $this->wpdb->insert($this->service_table_name, array(
					'practitioner_id' => $id,
					'service_id'      => $service,
					'create_at'       => time(),
					'update_at'       => time()
				), array(
					'%d',
					'%d',
					'%d',
					'%d'
				));
				if (!$ins_sql) {
					$is_service_created = false;
					break;
				}
			}

		if ($is_basic_created && $is_service_created) {
			$this->wpdb->query('COMMIT');
			$is_created = true;
		} else
			$this->wpdb->query('ROLLBACK');

		return $is_created;
	}

	function update($id) {
		$is_updated = false;
		$this->wpdb->query('START TRANSACTION');
		$safe_sql = $this->wpdb->prepare("
			UPDATE $this->table_name SET
			first_name = %s,
			last_name = %s,
			email = %s,
			description = %s,
			blog_user_id = %s,
			update_at = %d WHERE
			id = %d", array(
			$_POST['prac_first_name'],
			$_POST['prac_last_name'],
			$_POST['prac_email'],
			$_POST['prac_description'],
			get_current_user_id(),
			time(),
			$id
		));
		$is_basic_udpated = $this->wpdb->query($safe_sql);
		$is_deleted = $this->wpdb->delete($this->service_table_name, array('practitioner_id' => $id));
		$is_service_updated = true;
		foreach ($_POST['prac_treat_list'] as $service) {
			$ins_sql = $this->wpdb->insert($this->service_table_name, array(
				'practitioner_id' => $id,
				'service_id'      => $service,
				'create_at'       => time(),
				'update_at'       => time()
			), array(
				'%d',
				'%d',
				'%d',
				'%d'
			));
			if (!$ins_sql) {
				$is_service_updated = false;
				break;
			}
		}

		if ($is_basic_udpated && $is_deleted && $is_service_updated) {
			$this->wpdb->query('COMMIT');
			$is_updated = true;
		} else
			$this->wpdb->query('ROLLBACK');

		return $is_updated;
	}

	function single_info($id) {
		$safe_sql = $this->wpdb->prepare("SELECT * FROM ".$this->table_name."
				WHERE id=%d AND blog_id=%d AND status !=%s", array(
			$id,
			get_current_blog_id(),
			'-1'
		));
		$result = $this->wpdb->get_row($safe_sql, ARRAY_A);

		if (empty($result)) {
			return false;
		}

		return $result;
	}

	function get_appointments($prac_id) {

	}

	function get_schedule($prac_id, $only_result = false) {
		$query = "SELECT * FROM {$this->prac_schedule_table_name} WHERE {$this->prac_schedule_table_name}.practitioner_id='{$prac_id}'";
		$results = $this->wpdb->get_row($query, ARRAY_A);
		if ($only_result)
			return $results;
		$output = array(
			'mon' => array('value' => null, 'is_off_day' => true),
			'tue' => array('value' => null, 'is_off_day' => true),
			'wed' => array('value' => null, 'is_off_day' => true),
			'thu' => array('value' => null, 'is_off_day' => true),
			'fri' => array('value' => null, 'is_off_day' => true),
			'sat' => array('value' => null, 'is_off_day' => true),
			'sun' => array('value'      => null, 'is_off_day' => true)
		);
		$black_list = array("id", "blog_id", "blog_user_id", "create_at", "update_at", "practitioner_id");
		if (!empty($results)) {
			foreach ($results as $key => $value) {
				if (in_array($key, $black_list))
					continue;
				$output[$key] = array('value' => $value, 'is_off_day' => false);
				if (glab_convert_helper::is_off_day($value)) {
					$output[$key]['is_off_day'] = true;
				}
			}
		}
		return $output;
	}

	function update_schedule($prac_id) {
		$days = array('mon' => '', 'tue' => '', 'wed' => '', 'thu' => '', 'fri' => '', 'sat' => '', 'sun' => '');
		$post = array();

		foreach ($days as $key => $value) {
			if (!isset($_POST[$key.'_d_off']))
				$_POST[$key.'_d_off'] = '';
			$post[$key] = sprintf('%02d', $_POST[$key.'_s_h']).':'.sprintf('%02d', $_POST[$key.'_s_m']).':00:-'.sprintf('%02d', $_POST[$key.'_d_h']).':'.sprintf('%02d', $_POST[$key.'_d_m']).':00:'.$_POST[$key.'_d_off'];
		}
		$query = "SELECT * FROM {$this->prac_schedule_table_name} WHERE practitioner_id='{$prac_id}'";
		$results = $this->wpdb->get_row($query, ARRAY_A);
		$update = false;
		if (!empty($results)) {
			$update = true;
		}
		$safe_sql = $this->prepare_sql_for_schedule($update, $post, $prac_id);
		$is_updated = $this->wpdb->query($safe_sql);
		return $is_updated;
	}

	private function prepare_sql_for_schedule($will_update, $post, $prac_id) {
		$safe_sql = '';
		if ($will_update) {
			$safe_sql = $this->wpdb->prepare("
					UPDATE {$this->prac_schedule_table_name} SET
					mon=%s,
					tue=%s,
					wed=%s,
					thu=%s,
					fri=%s,
					sat=%s,
					sun=%s,
					update_at=%d
					WHERE practitioner_id=%d", array(
				$post['mon'],
				$post['tue'],
				$post['wed'],
				$post['thu'],
				$post['fri'],
				$post['sat'],
				$post['sun'],
				time(),
				$prac_id
			));
		} else {
			$safe_sql = $this->wpdb->prepare("
					INSERT INTO {$this->prac_schedule_table_name}
					( mon, tue, wed, thu, fri, sat, sun, blog_id, blog_user_id, practitioner_id, create_at, update_at )
					VALUES ( %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d  )
					", array(
				$post['mon'],
				$post['tue'],
				$post['wed'],
				$post['thu'],
				$post['fri'],
				$post['sat'],
				$post['sun'],
				get_current_blog_id(),
				get_current_user_id(),
				$prac_id,
				time(),
				time()
			));
		}
		return $safe_sql;
	}

	function get_services($prac_id) {
		$safe_sql = $this->wpdb->prepare("SELECT {$this->service_table_name}.*,{$this->service_name}.name as service_name, {$this->service_name}.status as service_status "."                     FROM ".$this->service_table_name."
                                LEFT JOIN {$this->service_name} ON {$this->service_name}.id = {$this->service_table_name}.service_id
				WHERE practitioner_id=%d", array(
			$prac_id
		));
		$result = $this->wpdb->get_results($safe_sql, ARRAY_A);

		if (empty($result)) {
			return false;
		}
		
		$output = array();
		foreach($result as $row){
			if($row['service_status']=='1')
				array_push($output, $row);	
		}
		
		return $output;
	}

	function get_breaks($prac_id) {

	}

	function activate($id) {
		$safe_sql = $this->wpdb->prepare("UPDATE ".$this->table_name." SET
				status=%s WHERE id=%d", array(
			'1',
			$id
		));
		$is_updated = $this->wpdb->query($safe_sql);
		return $is_updated;
	}

	function deactivate($id) {
		$safe_sql = $this->wpdb->prepare("UPDATE ".$this->table_name." SET
				status=%s WHERE id=%d", array(
			'0',
			$id
		));
		$is_updated = $this->wpdb->query($safe_sql);
		return $is_updated;
	}

	function get_specific_day_slot($day, $prac_id) {
		$query = "SELECT {$day} FROM {$this->prac_schedule_table_name} WHERE practitioner_id = '{$prac_id}'";
		$results = $this->wpdb->get_row($query, ARRAY_A);
		return $results[$day];
	}

	function get_prac_with_hour($prac_id) {
		$query = "SELECT p.first_name,p.last_name,phr.* FROM {$this->prac_schedule_table_name} AS phr LEFT JOIN {$this->table_name} AS p ON p.id=phr.practitioner_id WHERE phr.practitioner_id = '{$prac_id}' AND phr.blog_id='".get_current_blog_id()."'";
		$row = $this->wpdb->get_row($query, ARRAY_A);
		return $row;
	}

	function get_existing_practitioner_list() {
		$practitioner_emails = array();
		$result = $this->wpdb->get_results("SELECT id,email FROM {$this->table_name} WHERE blog_id='".get_current_blog_id()."'", ARRAY_A);
		foreach ($result as $row) {
			$practitioner_emails[$row['id']] = $row['email'];
		}
		return $practitioner_emails;
	}

	function create_new_practitioner($data) {
		$columns = array('email' => $data[4], 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id(), 'create_at' => time(), 'update_at' => time());
		$result = $this->wpdb->insert($this->table_name, $columns);
		$practitioner_id = $this->wpdb->insert_id;
		return $practitioner_id;
	}

}
