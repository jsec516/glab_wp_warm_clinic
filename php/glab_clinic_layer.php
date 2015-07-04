<?php

class glab_clinic_layer {

    private $wpdb;
    private $table_name;
    private $room_table_name;
    private $room_service_table_name;
    private $appointment_table_name;
    private $hour_table_name;
    private $cpoll_table_name;
    private $cfm_table_name;
    private $frontend_table_name;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'glab_cas_clinic_hours';
        $this->room_service_table_name = 'glab_cas_room_services';
        $this->room_table_name = 'glab_cas_rooms';
        $this->appointment_table_name = 'glab_cas_appointments';
        $this->hour_table_name = 'glab_cas_hours';
        $this->cpoll_table_name = 'glab_cas_poll_options';
        $this->cfm_table_name = 'glab_cas_mail_contents';
        $this->frontend_table_name = 'glab_cas_frontend_settings';
    }

    function save_room() {
        $safe_sql = $this->wpdb->prepare("
				INSERT INTO {$this->room_table_name}
				( name, status, blog_id, blog_user_id, create_at, update_at )
				VALUES ( %s, %s, %d, %d, %d, %d  )
				", array(
            $_POST['glab_room_name'],
            '1',
            get_current_blog_id(),
            get_current_user_id(),
            time(),
            time()
        ));
        $this->wpdb->query($safe_sql);
        return $this->wpdb->insert_id;
    }

    function deactivate($room_id) {
        $where_clause = array('id' => $room_id, 'blog_id' => get_current_blog_id());
        $columns = array('status' => '0');
        $this->wpdb->update($this->room_table_name, $columns, $where_clause);
    }

    function get_rooms() {
        $query = "SELECT {$this->room_table_name}.id as id,name as title,GROUP_CONCAT(service_id) as services
				FROM {$this->room_table_name} 
				LEFT JOIN {$this->room_service_table_name} on {$this->room_table_name}.id={$this->room_service_table_name}.room_id
						WHERE {$this->room_table_name}.status='1' AND {$this->room_table_name}.blog_id='" . get_current_blog_id() . "'  GROUP BY {$this->room_table_name}.id";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            $output[$row['id']]['id'] = $row['id'];
            $output[$row['id']]['title'] = $row['title'];
            if ($row['services']) {
                $output[$row['id']]['services'] = explode(',', $row['services']);
            } else {
                $output[$row['id']]['services'] = array();
            }

            $output[$row['id']]['num_of_appts'] = 0;
        }
        return $output;
    }

    function delete_room_service($room_id, $service_id) {
        $where_clause = array('room_id' => $room_id, 'service_id' => $service_id, 'blog_id' => get_current_blog_id());
        return $this->wpdb->delete($this->room_service_table_name, $where_clause);
    }

    function get_schedule() {
        $query = "SELECT * FROM {$this->table_name} WHERE {$this->table_name}.blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_row($query, ARRAY_A);
        $output = array(
            'mon' => array('value' => null, 'is_off_day' => true),
            'tue' => array('value' => null, 'is_off_day' => true),
            'wed' => array('value' => null, 'is_off_day' => true),
            'thu' => array('value' => null, 'is_off_day' => true),
            'fri' => array('value' => null, 'is_off_day' => true),
            'sat' => array('value' => null, 'is_off_day' => true),
            'sun' => array('value' => null, 'is_off_day' => true)
        );
        $black_list = array("id", "blog_id", "blog_user_id", "create_at", "update_at");
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

    function update_schedule() {
        $days = array('mon' => '', 'tue' => '', 'wed' => '', 'thu' => '', 'fri' => '', 'sat' => '', 'sun' => '');
        $post = array();

        foreach ($days as $key => $value) {
            if (!isset($_POST[$key . '_d_off']))
                $_POST[$key . '_d_off'] = '';
            $post[$key] = sprintf('%02d', $_POST[$key . '_s_h']) . ':' . sprintf('%02d', $_POST[$key . '_s_m']) . ':00:-' . sprintf('%02d', $_POST[$key . '_d_h']) . ':' . sprintf('%02d', $_POST[$key . '_d_m']) . ':00:' . $_POST[$key . '_d_off'];
        }
        $query = "SELECT * FROM {$this->table_name} WHERE blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_row($query, ARRAY_A);
        $update = false;
        if (!empty($results)) {
            $update = true;
        }
        $safe_sql = $this->prepare_sql_for_schedule($update, $post);
        $is_updated = $this->wpdb->query($safe_sql);
        return $is_updated;
    }

    private function prepare_sql_for_schedule($will_update, $post) {
        $safe_sql = '';
        if ($will_update) {
            $safe_sql = $this->wpdb->prepare("
					UPDATE {$this->table_name} SET
					mon=%s,
					tue=%s,
					wed=%s,
					thu=%s,
					fri=%s,
					sat=%s,
					sun=%s,
					update_at=%d
					WHERE blog_id=%d", array(
                $post['mon'],
                $post['tue'],
                $post['wed'],
                $post['thu'],
                $post['fri'],
                $post['sat'],
                $post['sun'],
                time(),
                get_current_blog_id()
            ));
        } else {
            $safe_sql = $this->wpdb->prepare("
					INSERT INTO {$this->table_name}
					( mon, tue, wed, thu, fri, sat, sun, blog_id, blog_user_id, create_at, update_at )
					VALUES ( %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d  )
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
                time(),
                time()
            ));
        }
        return $safe_sql;
    }

    function get_monthly_off_days($month, $year) {
        $app_start_date = date('Y-m-d',mktime(0, 0, 0, $month, 1, $year));
        $app_end_date = date('Y-m-t',mktime(0, 0, 0, $month, 1, $year));
        /*$last_day_of_month = date('j', $app_start_date);
        $app_end_date = mktime(0, 0, 0, $month, $last_day_of_month, $year);*/
        $query = "SELECT * FROM {$this->appointment_table_name} WHERE app_type='2' AND blog_id='" . get_current_blog_id() . "' AND (app_date between '$app_start_date' AND '$app_end_date')";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            array_push($output, $row['app_date']);
        }
        return $output;
    }

    function regular_off_days() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_row($query, ARRAY_A);
        $output = array();
        if (!empty($results)) {
            foreach ($results as $key => $value) {
                if (glab_convert_helper::is_off_day($value)) {
                    array_push($output, $key);
                }
            }
        }

        return $output;
    }

    function get_monthly_appointments($month, $year) {
        $app_start_date = mktime(0, 0, 0, $month, 1, $year);
        $app_db_start_date = date('Y-m-d', $app_start_date);
        $app_db_end_date = date('Y-m-t', $app_start_date);
         $filterDocClause = (isset($_POST['selected_prac']) and $_POST['selected_prac'])?" AND practitioner_id='".$_POST['selected_prac']."' ":'';
         //var_dump($_COOKIE['filterDocId']);exit;
        $query = "SELECT * FROM {$this->appointment_table_name} WHERE app_type='0' AND blog_id='" . get_current_blog_id() . "' AND status = '1' AND (app_date between '$app_db_start_date' AND '$app_db_end_date') $filterDocClause";
        
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $output = array();
        foreach ($results as $row) {
            $output[$row['app_date']][] = $row;
        }
        return $output;
    }

    function get_hours() {
        $query = "SELECT * FROM {$this->hour_table_name}";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        return $results;
    }

    function get_specific_day_slot($day) {
        $query = "SELECT {$day} FROM {$this->table_name} where blog_id='".get_current_blog_id()."'";
        $results = $this->wpdb->get_row($query, ARRAY_A);
        return $results[$day];
    }

    function get_clinic_interval($exp_day) {
        $clinic_time = $this->get_specific_day_slot($exp_day);
        $clinic_time_array=explode(":-",$clinic_time);
        $interval = array();
        $interval['start_time'] = $this->get_clinic_whole_day_of('start', $clinic_time_array);
        $interval['end_time'] = $this->get_clinic_whole_day_of('end', $clinic_time_array);

        return $interval;
    }

    function get_clinic_whole_day_of($point, $time_array) {
        $expected_time = '';

        if ($point == "start") {
            $expected_time = substr($time_array[0], 0, 5);
        } else {
            if (strpos($time_array[1], 'OFF') === FALSE) {
                $expected_time = substr($time_array[1], 0, 5);
            } else {
                $expected_time = "20:00";
            }
        }

        return ($expected_time != false) ? $expected_time : false;
    }

    function get_poll_option_old() {
        $url = plugins_url('glab_clinic/assets');
        $query = "SELECT * FROM {$this->cpoll_table_name} WHERE blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_row($query, ARRAY_A);
        $elem_array = explode(',', $results['element_format']);
        $text_array = explode('*-', $results['poll_format']);
        $inc = 0;
        $element_sequence = '';
        $tmp_content = '';
        $content_array = array();
        foreach ($elem_array as $element) {
            $tag = explode('_', $element);
            if ($tag[0] == 'option') {
                $tmp_content.='<tr id="option_' . $tag[1] . '"><td>&nbsp;</td><td style="padding:5px 0 0 200px;"><div style="position:relative;"><input placeholder="comment Field" type="text" class="field_box" value="' . $text_array[$inc] . '" name="format_text[]" id="msg_for_text_' . $tag[1] . '" style="padding-right:30px;"><div style="margin-left:-25px;width:20px;display:inline;cursor:pointer;" id="close_for_msg" onclick="return removeElementTag(\'option_' . $tag[1] . '\');"><img src="' . $url . '/images/cancel.png" alt="x" style="margin-bottom:-3px;"></div></div></td></tr>';
            } elseif ($tag[0] == 'poll') {
                $tmp_content.='<tr id="poll_' . $tag[1] . '"><td>&nbsp;</td><td style="padding:5px 0 0 200px;"><div style="position:relative;"><input placeholder="poll Field" type="text" class="field_box" value="' . $text_array[$inc] . '" name="format_text[]" id="poll_for_text_' . $tag[1] . '" style="padding-right:30px;"><div style="margin-left:-25px;width:20px;display:inline;cursor:pointer;" id="close_for_msg" onclick="return removeElementTag(\'poll_' . $tag[1] . '\');"><img src="' . $url . '/images/cancel.png" alt="x" style="margin-bottom:-3px;"></div></div></td></tr>';
            }
            if (!$element_sequence)
                $element_sequence.=$element;
            else
                $element_sequence.=',' . $element;
            $inc++;
        }
        $content_array['content'] = $tmp_content;
        $content_array['sequence'] = $element_sequence;
        return $content_array;
    }

    function get_poll_option() {
        $query = "SELECT * FROM {$this->cpoll_table_name} WHERE blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        $options = '';
        foreach ($results as $row) {
           $recent_value=stripslashes($row['poll_format']);
           $options.="<li><input type='text' name='options[]' class='glab_wp_text large' placeholder='type option' value=\"{$recent_value}\" /><a href='#' style='padding-left:10px;' class='remove-elem'>Remove</a></li>";
        }
        return $options;
    }
    
    function get_polls(){
        $query = "SELECT * FROM {$this->cpoll_table_name} WHERE blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        return $results;
    }

    function update_cancel_poll_old() {
        $poll_field = implode('*-', $_REQUEST['format_text']);
        $element_tag = $_REQUEST['sequence'];
        if ($_POST['is_update']) {
            $columns = array('poll_format' => $poll_field, 'element_format' => $element_tag, 'update_at' => time());
            $where = array('blog_id' => get_current_blog_id());
            return $this->wpdb->update($this->cpoll_table_name, $columns, $where);
        } else {
            $columns = array('poll_format' => $poll_field, 'element_format' => $element_tag, 'create_at' => time(), 'update_at' => time(), 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id());
            return $this->wpdb->insert($this->cpoll_table_name, $columns);
        }
    }

    function update_cancel_poll() {
        $where_clause = array('blog_id' => get_current_blog_id());
        $this->wpdb->delete($this->cpoll_table_name, $where_clause);
        foreach ($_POST['options'] as $option) {
            if ($option) {
                $columns = array('poll_format' => $option, 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id(), 'create_at' => time(), 'update_at' => time());
                $this->wpdb->insert($this->cpoll_table_name, $columns);
            }
        }
        return true;
    }

    function get_cfm_mail_format() {
        $query = "SELECT * FROM {$this->cfm_table_name} WHERE mail_for='cfm' AND blog_id='" . get_current_blog_id() . "'";
        $result = $this->wpdb->get_row($query, ARRAY_A);
        $data = array('mail_subject' => '', 'mail_content' => '', 'is_already_added' => 0);
        if ($result) {
            $data['mail_subject'] = $result['mail_subject'];
            $data['mail_content'] = $result['mail_content'];
            $data['is_already_added'] = 1;
        }
        return $data;
    }

    function update_confirmation_mail() {
        $is_updated = null;
        if ($_POST['is_added']) {
            $data = array('mail_subject' => $_POST['mail_subject'], 'mail_content' => $_POST['mail_content'], 'update_at' => time());
            $where = array('blog_id' => get_current_blog_id());
            $is_updated = $this->wpdb->update($this->cfm_table_name, $data, $where);
        } else {
            $data = array('mail_for' => 'cfm', 'mail_subject' => $_POST['mail_subject'], 'mail_content' => $_POST['mail_content'], 'update_at' => time(), 'create_at' => time(), 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id());
            $is_updated = $this->wpdb->insert($this->cfm_table_name, $data);
        }
        return $is_updated;
    }

    function update_frontend_settings() {
        $frame_color = trim($_POST['frame_color']);
        $frame_width = trim($_POST['frame_width']);
        $frame_height = trim($_POST['frame_height']);
        if (is_numeric($frame_width) && is_numeric($frame_height)) {
            if ($frame_width >= 430) {
                $where_clause = array('blog_id' => get_current_blog_id());
                $this->wpdb->delete($this->frontend_table_name, $where_clause);
                $columns = array('frame_color' => $frame_color, 'frame_width' => $frame_width, 'frame_height' => $frame_height, 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id(), 'create_at' => time(), 'update_at' => time());
                $this->wpdb->insert($this->frontend_table_name, $columns);
                $msg = "fronted settings updated successfully";
            } else {
                $msg = "frame width should be at least 430";
            }
        } else {
            $msg = "frame height and width should be numeric";
        }
        return $msg;
    }

    function get_hours_in_range($start, $end) {
        $query = "SELECT * FROM {$this->hour_table_name} WHERE num_hour between " . $start . ' and ' . $end;
        $rows = $this->wpdb->get_results($query, ARRAY_A);
        return $rows;
    }

    function get_frontend_settings() {
        $frontSQL = "SELECT * FROM {$this->frontend_table_name} WHERE blog_id='" . get_current_blog_id() . "'";
        $row = $this->wpdb->get_row($frontSQL, ARRAY_A);
        return $row;
    }

    function get_existing_room_list() {
        $room_lists = array();
        $result = $this->wpdb->get_results("SELECT id,name FROM {$this->room_table_name} WHERE blog_id='" . get_current_blog_id() . "'", ARRAY_A);
        foreach ($result as $row) {
            $room_lists[$row['id']] = $row['name'];
        }
        return $room_lists;
    }

    function create_new_room($data) {
        $columns = array('name' => $data[10], 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id(), 'create_at' => time(), 'update_at' => time());
        $result = $this->wpdb->insert($this->room_table_name, $columns);
        $room_id = $this->wpdb->insert_id;
        return $room_id;
    }

    /* function get_room_with_services(){
      $query="SELECT {$this->room_table_name}.id,{$this->room_table_name}.name,GROUP_CONCAT(DISTINCT {$this->room_service_table_name}.service_id separator ',')) AS services
      FROM {$this->room_table_name}
      LEFT JOIN {$this->room_service_table_name} on {$this->room_service_table_name}.room_id = {$this->room_table_name}.id
      WHERE {$this->room_table_name}.status='1' AND
      {$this->room_table_name}.blog_id='".get_current_blog_id()."'
      ";
      $results = $this->wpdb->get_results($query, ARRAY_A);
      $output=array();
      foreach($results as $row){
      $row['services']=explode(',',$row['services']);
      $output[$row['id']]=$row;
      }
      return $output;
      } */
}