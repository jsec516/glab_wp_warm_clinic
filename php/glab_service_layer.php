<?php

class glab_service_layer {

    private $wpdb;
    private $table_name;
    private $table_prac_service;
    private $table_room_service;
    private $table_practitioner;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'glab_cas_services';
        $this->table_prac_service = 'glab_cas_practitioner_services';
        $this->table_room_service = 'glab_cas_room_services';
        $this->table_room = 'glab_cas_rooms';
        $this->table_practitioner='glab_cas_practitioners';
    }

    function all() {
        $condition = "status IN ('0','1') AND blog_id='" . get_current_blog_id() . "'";
        if (isset($_POST['s']) && trim($_POST['s'])) {
            $term = trim($_POST['s']);
            $condition .= " AND name LIKE '%$term%'";
        }
        $query = "SELECT * FROM {$this->table_name} WHERE {$condition} ORDER BY update_at DESC";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            $row['description'] = substr($row['description'], 0, 100) . '...';
            $output[$row['id']] = $row;
        }
        return $output;
    }

    function get_active_services() {
        $query = "SELECT * FROM {$this->table_name} WHERE status='1' AND blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        return $results;
    }

    function delete($id) {
        $safe_sql = $this->wpdb->prepare("UPDATE {$this->table_name} SET
				status=%s WHERE id=%d and blog_id=%d", array(
            '-1',
            $id,
            get_current_blog_id()
        ));
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }
    
    function delete_room_services($room_id){
    	$safe_sql = $this->wpdb->prepare("DELETE * FROM {$this->table_room_service}  
    	 WHERE room_id=%d and blog_id=%d", array(
    			$room_id,
    			get_current_blog_id()
    	));
    	$is_deleted = $this->wpdb->query($safe_sql);
    	return $is_deleted;
    }

    function bulk_delete($id_array) {
        $ids = implode(",", $id_array);
        $safe_sql = $this->wpdb->prepare("UPDATE {$this->table_name} SET
				status=%s WHERE id IN ({$ids}) and blog_id=%d", array(
            '-1',
            get_current_blog_id()
        ));
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }

    function save() {
        $allow_multiple = (($_POST['service_multi_client'] == 'Y') && isset($_POST['service_interval']) && ($_POST['service_interval']>0)) ? '1' : '0';

        if ($allow_multiple)
            $betn_hours = $_POST['service_interval'];
        else
            $betn_hours = '0';

        $duration = glab_convert_helper::to_minute($_POST['service_hour'], $_POST['service_minute']);

        $safe_sql = $this->wpdb->prepare("
				INSERT INTO {$this->table_name}
				( name, description, flag, duration, allow_multiple, betn_minutes, color_value, status, blog_id, blog_user_id, create_at, update_at )
				VALUES ( %s, %s, %s, %d, %d, %d, %s, %s, %d, %d, %d, %d  )
				", array(
            $_POST['service_name'],
            $_POST['service_description'],
            $_POST['service_code'],
            $duration,
            $allow_multiple,
            $betn_hours,
            $_POST['service_color'],
            '1',
            get_current_blog_id(),
            get_current_user_id(),
            time(),
            time()
        ));
        $is_created = $this->wpdb->query($safe_sql);
        return $is_created;
    }

    function update($id) {
        $allow_multiple = (($_POST['service_multi_client'] == 'Y') && isset($_POST['service_interval']) && ($_POST['service_interval']>0)) ? '1' : '0';

        if ($allow_multiple)
            $betn_hours = $_POST['service_interval'];
        else
            $betn_hours = '0';

        $duration = glab_convert_helper::to_minute($_POST['service_hour'], $_POST['service_minute']);
        $safe_sql = $this->wpdb->prepare("
				UPDATE {$this->table_name} SET
				name = %s,
				description = %s,
				flag = %s,
				duration = %d,
				allow_multiple = %d,
				betn_minutes = %d,
				color_value = %s,
				blog_user_id = %d,
				update_at = %d WHERE id = %d", array(
            $_POST['service_name'],
            $_POST['service_description'],
            $_POST['service_code'],
            $duration,
            $allow_multiple,
            $betn_hours,
            $_POST['service_color'],
            get_current_user_id(),
            time(),
            $id
        ));
        $is_udpated = $this->wpdb->query($safe_sql);
        return $is_udpated;
    }

    function activate($id) {
        $safe_sql = $this->wpdb->prepare("UPDATE {$this->table_name} SET
				status=%s WHERE id=%d AND blog_id=%d", array(
            '1',
            $id,
            get_current_blog_id()
        ));
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }

    function deactivate($id) {
        $safe_sql = $this->wpdb->prepare("UPDATE {$this->table_name} SET
				status=%s WHERE id=%d AND blog_id=%d", array(
            '0',
            $id,
            get_current_blog_id()
        ));
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }

    function single_info($id) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name}
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

    function get_names($ids) {
        $sql = "SELECT GROUP_CONCAT(name) AS services_name FROM {$this->table_name} WHERE id IN ($ids)";
        $result = $this->wpdb->get_row($sql, ARRAY_A);
        return $result['services_name'];
    }

    function practitioners($id) {
        $prac_service_id_sql = "SELECT service_id FROM {$this->table_prac_service}
        WHERE practitioner_id='$id'";
        $query = "SELECT * FROM {$this->table_name}
        WHERE id IN ($prac_service_id_sql) AND status IN ('0','1')";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            $output[$row['id']] = $row;
        }
        return $output;
    }

    function service_based_practitioners($service_id){
        $query="SELECT p.* FROM {$this->table_prac_service} AS ps "
        . " LEFT JOIN {$this->table_practitioner} AS p ON p.id=ps.practitioner_id "
        . " WHERE ps.service_id='{$service_id}' AND p.status='1'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            $output[$row['id']] = $row;
            $output[$row['id']]['name']=$row['first_name'].' '.$row['last_name'];
        }
        return $output;
    }
    
    function rooms($id) {
        
    }

    function add_room_service($room_id, $service_id) {
        if($this->is_service_already_exist($room_id, $service_id))
                return true;
        $safe_sql = $this->wpdb->prepare("
    			INSERT INTO {$this->table_room_service}
    			( room_id, service_id, blog_id, blog_user_id, create_at, update_at )
    			VALUES ( %d, %d, %d, %d, %d, %d )
    			", array(
            $room_id,
            $service_id,
            get_current_blog_id(),
            get_current_user_id(),
            time(),
            time()
        ));
        $is_created = $this->wpdb->query($safe_sql);
        return $is_created;
    }
    
    function is_service_already_exist($room_id, $service_id){
        $query = "SELECT id FROM {$this->table_room_service} WHERE room_id='$room_id' AND service_id='$service_id'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        if(count($results)){
            return true;
        }else{
            return false;
        }
    }

    function get_room_services($room_id, $only_ids=false) {
        $query = "SELECT service.id,service.name FROM {$this->table_room_service}
    	LEFT JOIN {$this->table_name} as service on service.id={$this->table_room_service}.service_id
    	WHERE room_id='{$room_id}'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = $results;
        if($only_ids){
        	$output=array();
        	foreach($results as $row){
        		array_push($output, $row['id']);
        	}
        	return $output;
        }
        return $output;
    }

    function get_gaps_betn_appts($id) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name}
				WHERE id=%d", array(
            $id
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);
        if (!empty($result) && $result['allow_multiple']) {
            return $result['betn_minutes'];
        } else {
            return false;
        }
    }

    function get_treat_color($id) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name}
    	WHERE id=%d", array(
            $id
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);
        if (!empty($result) && $result['color_value']) {
            return $result['color_value'];
        } else {
            return '#000000';
        }
    }

    function get_service_duration($id) {

        $output = array();
        $safe_sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name}
                        WHERE id=%d", array(
            $id
        ));

        $row = $this->wpdb->get_row($safe_sql, ARRAY_A);
        $output['service_type'] = $row['allow_multiple'];

        if ($output['service_type'] == 1) {
            $output['service_hours'] = 0;
            $output['service_minutes'] = $row['betn_minutes'];
        } else {
            $output['service_hours'] = glab_convert_helper::only_hours($row['duration']);
            $output['service_minutes'] = glab_convert_helper::only_minutes($row['duration']);
        }
        $output['service_duration'] = $row['duration'];

        return $output;
    }

    public function get_service_specific_rooms($service_id) {
        $output = array();
        $safe_sql = $this->wpdb->prepare("SELECT r.id,r.name 
                        FROM {$this->table_room} as r
                        LEFT JOIN {$this->table_room_service} as rs ON rs.room_id=r.id 
                        WHERE rs.blog_id=%d 
                        AND rs.service_id=%d 
                        AND r.status='1' 
                        GROUP BY rs.room_id 
                        ", array(
            get_current_blog_id(),
            $service_id
        ));

        $results = $this->wpdb->get_results($safe_sql, ARRAY_A);
        foreach ($results as $row) {
            array_push($output, $row['id']);
        }

        return $output;
    }

    function get_existing_treatment_list() {
        $treatment_lists = array();
        $result = $this->wpdb->get_results("SELECT id,name FROM {$this->table_name} WHERE blog_id='" . get_current_blog_id() . "'", ARRAY_A);
        foreach ($result as $row) {
            $treatment_lists[$row['id']] = $row['name'];
        }
        return $treatment_lists;
    }

    function create_new_treatment($data) {
        $columns = array('name' => $data[5], 'flag'=>strtoupper(substr($data[5],0,3)), 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id(), 'create_at' => time(), 'update_at' => time());
        $result = $this->wpdb->insert($this->table_name, $columns);
        $treatment_id = $this->wpdb->insert_id;
        return $treatment_id;
    }

}
