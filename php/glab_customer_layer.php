<?php

class glab_customer_layer {

    private $wpdb;
    private $table_name;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'glab_cas_users';
    }

    function all() {		$condition='';    	if(isset($_POST['s']) and $_POST['s']){    		$condition=" and email='{$_POST['s']}'";    	}
        $query = "SELECT * FROM " . $this->table_name . " where status IN ('0','1') and blog_id='" . get_current_blog_id() . "' $condition";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            $output[$row['id']] = $row;
        }
        return $output;
    }

    function delete($id) {
        $safe_sql = $this->wpdb->prepare("UPDATE " . $this->table_name . " SET
				status=%s WHERE id=%d", array(
            '-1',
            $id
        ));
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }

    function save() {
    	$_POST['cust_password']=($_POST['cust_password'])?$_POST['cust_password']:$this->generateRandomString();
        $safe_sql = $this->wpdb->prepare("
				INSERT INTO $this->table_name
				( first_name, last_name, email, password, primary_doctor, street, province, city, postal_code, address1, address2, phone, contact_me, cell, work, primary_phone, status, blog_id, blog_user_id, create_at, update_at )
				VALUES ( %s, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %d, %d, %d, %d  )
				", array(
            $_POST['cust_first_name'],
            $_POST['cust_last_name'],
            $_POST['cust_email'],
            $_POST['cust_password'],
            $_POST['cust_primary_doctor'],
            $_POST['cust_street'],
            $_POST['cust_province'],
            $_POST['cust_city'],
            $_POST['cust_postal_code'],
            $_POST['cust_address1'],
            $_POST['cust_address2'],
            $_POST['cust_phone'],
            $_POST['cust_contact_me'],
            $_POST['cust_cell'],
            $_POST['cust_work'],
            $_POST['cust_primary_phone'],
            '1',
            get_current_blog_id(),
            get_current_user_id(),
            time(),
            time()
        ));
        $is_created = $this->wpdb->query($safe_sql);		
        return $is_created;
    }
    function generateRandomString($length = 10) {
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$charactersLength = strlen($characters);
    	$randomString = '';
    	for ($i = 0; $i < $length; $i++) {
    		$randomString .= $characters[rand(0, $charactersLength - 1)];
    	}
    	return $randomString;
    }
    function update($id) {
        $safe_sql = $this->wpdb->prepare("
				UPDATE $this->table_name SET
				first_name = %s,
				last_name = %s,
				email = %s,
				primary_doctor = %s,
				street = %s,
				province = %s,
				city = %s,
				postal_code = %s,
				address1 = %s,
				address2 = %s,
				contact_me = %d,
				phone=%s,
				cell = %s,
				work = %s,
				primary_phone = %s,
				update_at = %d WHERE id = %d", array(
            $_POST['cust_first_name'],
            $_POST['cust_last_name'],
            $_POST['cust_email'],
            $_POST['cust_primary_doctor'],
            $_POST['cust_street'],
            $_POST['cust_province'],
            $_POST['cust_city'],
            $_POST['cust_postal_code'],
            $_POST['cust_address1'],
            $_POST['cust_address2'],
            $_POST['cust_contact_me'],
            $_POST['cust_phone'],
            $_POST['cust_cell'],
            $_POST['cust_work'],
            $_POST['cust_primary_phone'],
            time(),
            $id
        ));                $is_udpated = $this->wpdb->query($safe_sql);		        if(trim($_POST['cust_password']) AND trim($_POST['cust_password']) == trim($_POST['cust_retype_password'])){        	$this->wpdb->update(
        			$this->table_name,
        			array('password' => trim($_POST['cust_password'])),
        			array( 'id' => $id )
        	);        }
        return $is_udpated;
    }
    function validate($update=false, $cust_id='') {
    	if($_POST['cust_password']!=$_POST['cust_retype_password']){
    		return 'password and retype password should be same';
    	}
    	$is_email_valid = $this->validate_email(trim($_POST['cust_email']), $update, $cust_id);
    	if ($is_email_valid) {
    		return true;
    	} else {
    		return 'email already taken or not in valid format';
    	}
    }        private function validate_email($email, $update = false, $cust_id = '') {
    	if (!is_email($_POST['cust_email'])) {
    		return false;
    	}
    
    	if ($cust_id) {
    		$cust_info = $this->single_info($cust_id);
    		if ($cust_info) {
    			if ($cust_info['email'] == $email)
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
    function activate($id) {
        $safe_sql = $this->wpdb->prepare("UPDATE " . $this->table_name . " SET
				status=%s WHERE id=%d", array(
            '1',
            $id
        ));
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }

    function deactivate($id) {
        $safe_sql = $this->wpdb->prepare("UPDATE " . $this->table_name . " SET
				status=%s WHERE id=%d", array(
            '0',
            $id
        ));
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }

    function single_info($id) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->table_name . "
				WHERE id=%d AND status !=%s", array(
            $id,
            '-1'
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    function practitioners($id) {
        
    }

    function rooms($id) {
        
    }

    function save_n_inserted_id($params) {
		
        $columns = array(
            'first_name' => $params['fname'],
            'last_name' => $params['lname'],
            'email' => $params['cus_email'],
            'password' => $params['upass'],
            'primary_doctor' => 0,
            'street' => '',
            'province' => '',
            'city' => '',
            'postal_code' => '',
            'address1' => '',
            'address2' => '',
            'phone' => $params['cus_phone'],
            'contact_me' => '1',
            'cell' => '',
            'work' => '',
            'primary_phone' => 'PHONE',
            'status' => '1',
            'blog_id' => get_current_blog_id(),
            'blog_user_id' => get_current_user_id(),
            'create_at' => time(),
            'update_at' => time()
        );

        if (isset($params['is_details'])) {
            $columns['phone'] = $params['phone'];
            $columns['cell'] = $params['phone_cell'];
            $columns['work'] = $params['phone_work'];
            $columns['contact_me'] = $params['contact_with'];
            $columns['primary_phone'] = $params['primary_phone'];
            $columns['primary_doctor'] = $params['primary_doctor'];
        }

        $formats = array(
            '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s',
            '%d', '%d', '%d', '%d'
        );

        $this->wpdb->insert($this->table_name, $columns, $formats);
        return $this->wpdb->insert_id;
    }

    public function fetch_user_for($firstname, $lastname) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->table_name . " 
				 WHERE first_name=%s AND last_name=%s", array(
            $firstname,
            $lastname
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    public function fetch_by_suggestion($firstname = '', $lastname = '') {
        $where_clause = '';
        if ($firstname)
            $where_clause.=" AND first_name LIKE '%$firstname%'";
        if ($lastname)
            $where_clause.=" AND last_name LIKE '%$lastname%'";
        $safe_sql = "SELECT * FROM {$this->table_name} WHERE 1 $where_clause";
        $results = $this->wpdb->get_results($safe_sql, ARRAY_A);
        return $results;
    }

    function get_existing_customer_list() {
        $customer_emails = array();
        $result = $this->wpdb->get_results("SELECT id,email FROM {$this->table_name} WHERE blog_id='" . get_current_blog_id() . "'", ARRAY_A);
        foreach ($result as $row) {
            $customer_emails[$row['id']] = $row['email'];
        }
        return $customer_emails;
    }

    function create_new_customer($data) {
        $columns = array(
            'first_name' => $data[0],
            'last_name' => $data[1],
            'email' => $data[2],
            'phone' => $data[3],
            'contact_me' => '1',
            'primary_phone' => 'PHONE',
            'blog_id' => get_current_blog_id(),
            'blog_user_id' => get_current_user_id(),
            'create_at' => time(),
            'update_at' => time()
        );
        $result = $this->wpdb->insert($this->table_name, $columns);
        $customer_id = $this->wpdb->insert_id;
        return $customer_id;
    }

    function save_any_customer($columns) {
        $validation_error = $this->basic_validation();
        if ($validation_error)
            return $validation_error;
        $this->wpdb->insert($this->table_name, $columns);
        $customer_id = $this->wpdb->insert_id;
        return $customer_id;
    }

    function basic_validation() {
        $data = array();
        if (!$this->is_unique_data('email', $_POST['email'])) {
            $data['status'] = "failed";
            $data['msg'] = 'email already exist';
        } elseif (trim($_POST['passwd']) != trim($_POST['rPasswd'])) {
            $data['status'] = "failed";
            $data['msg'] = "password doesn't match";
        }
        return $data;
    }
    
    function verify_customer($activation_key){
        $query = "SELECT * FROM {$this->table_name} WHERE activation_key='$activation_key'";
        $columns=array('activation_key'=>'', 'status'=>'1');
        $where_clause=array('activation_key'=>$activation_key, 'blog_id'=>get_current_blog_id());
        $is_updated = $this->wpdb->update($this->table_name, $columns, $where_clause);
        return $is_updated;
    }

    function is_unique_data($field, $value) {
        $result = $this->wpdb->get_results("SELECT id FROM {$this->table_name} WHERE $field='$value' AND blog_id='" . get_current_blog_id() . "'", ARRAY_A);
        if (count($result))
            return false;
        else
            return true;
    }

    function get_valid_user() {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->table_name . " 
				 WHERE email=%s AND password=%s AND blog_id=%d AND status=%s", array(
            $_POST['email'],
            $_POST['password'],
            get_current_blog_id(),
            '1'
        ));
        return $this->wpdb->get_row($safe_sql, ARRAY_A);
    }

    function get_customer_by_email($email) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->table_name . " 
				 WHERE email=%s  AND blog_id=%d", array(
            $email,
            get_current_blog_id(),
        ));
        return $this->wpdb->get_row($safe_sql, ARRAY_A);
    }

    function update_from_frontend($user_id) {
        $data = array();
        $contact = $_POST['editContact'];
        if ($contact) {
            $contact=$contact;
        } else {
            $data['status'] = "failed";
            $data['msg'] = "please select at least one way of contact.";
            return $data;
        }
        $phone = $_POST['editPhone'];
        $columns = array(
            'first_name' => $_POST['editFirstName'],
            'last_name' => $_POST['editLastName'],
            'address1' => $_POST['editAddress1'],
            'address2' => $_POST['editAddress2'],
            'contact_me' => $contact,
            'phone' => $_POST['editPhone'],
            'primary_doctor' => $_POST['editPrimary1'],
            'street' => $_POST['editStreet'],
            'province' => $_POST['editProvince'],
            'city' => $_POST['editCity'],
            'postal_code' => $_POST['editPostal'],
        );
        if (isset($_POST['new_password']) AND ! empty($_POST['new_password'])) {
            if (trim($_POST['new_password']) !== trim($_POST['re_password'])) {
                $data['status'] = "failed";
                $data['msg'] = "two password does not match, please type again.";
                return $data;
            }
            $columns['password'] = trim($_POST['new_password']);
        }
        $where = array('id' => $user_id, 'blog_id' => get_current_blog_id());
        $is_updated = $this->wpdb->update('glab_cas_users', $columns, $where);
        if ($is_updated) {
            $data['status'] = "success";
            $data['msg'] = "Your profile successfully updated.";
            return $data;
        } else {
            $data['status'] = "failed";
            $data['msg'] = "Failed to update, please check your data and try again.";
            return $data;
        }
    }

}
