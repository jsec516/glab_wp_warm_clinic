<?php

class glab_sync_layer {

    private $wpdb;
    private $table_name;
    private $sync_cal_table_name;
    private $sync_event_table_name;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'glab_cas_synced_accounts';
        $this->sync_cal_table_name = 'glab_cas_synced_calendars';
        $this->sync_event_table_name = 'glab_cas_synced_appointments';
    }

    function get_sync_account() {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM {$this->table_name}
				WHERE blog_id=%d AND status =%s", array(
            get_current_blog_id(),
            '1'
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    function save_account() {
        if ($_POST['is_added']) {
            $columns = array('g_account' => $_POST['gc_account'], 'g_password' => $_POST['gc_password'], 'update_at' => time());
            $where = array('blog_id' => get_current_blog_id());
            $this->wpdb->update($this->table_name, $columns, $where);
        } else {
            $columns = array('g_account' => $_POST['gc_account'], 'g_password' => $_POST['gc_password'], 'update_at' => time(), 'create_at' => time(), 'blog_id' => get_current_blog_id());
            $this->wpdb->insert($this->table_name, $columns);
        }
    }

    function remove_old_calendar($account_id) {
        $where_clause = array('acount_id' => $account_id);
        $this->wpdb->delete($this->sync_cal_table_name, $where_clause);
    }

    function update_calendar_list($list_feed, $account) {
        $this->remove_old_calendar($account['id']);
        $avail_calendar_ids = $this->get_avail_calendar_ids($account['id']);
        foreach ($list_feed as $calendar) {
            if (in_array($calendar->id, $avail_calendar_ids)) {
                $columns = array('calendar_name' => $calendar->title, 'update_at' => time());
                $where = array('blog_id' => get_current_blog_id(), 'account_id' => $account['id']);
                $this->wpdb->update($this->sync_cal_table_name, $columns, $where);
            } else {
                $columns = array('calendar_id' => $calendar->id, 'calendar_name' => $calendar->title, 'email' => $account['g_account'], 'account_id' => $account['id'], 'blog_id' => get_current_blog_id(), 'blog_user_id' => get_current_user_id(), 'create_at' => time(), 'update_at' => time());
                $this->wpdb->insert($this->sync_cal_table_name, $columns);
            }
        }
    }

    function all_calendar() {
        $safe_sql = $this->wpdb->prepare("SELECT cal.*,ac.g_account as account 
                FROM {$this->sync_cal_table_name} as cal 
            LEFT JOIN {$this->table_name} AS ac ON ac.id=cal.account_id 
				WHERE blog_id=%d", array(
            get_current_blog_id()
        ));
        $result = $this->wpdb->get_results($safe_sql, ARRAY_A);
        return $result;
    }

    function get_sync_events() {
        $safe_sql = $this->wpdb->prepare("SELECT *  
                FROM {$this->sync_cal_table_name} 
		WHERE blog_id=%d AND status=%s", array(
            get_current_blog_id(),
            '0'
        ));
        $result = $this->wpdb->get_results($safe_sql, ARRAY_A);
        return $result;
    }

    function all_events($status = '0') {
        $query = "SELECT * FROM {$this->sync_event_table_name} WHERE status='{$status}' AND blog_id='" . get_current_blog_id() . "'";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        return $results;
    }

    function get_calendar_info($id) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->sync_cal_table_name . "
				WHERE id=%d AND blog_id=%d ", array(
            $id,
            get_current_blog_id()
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }

        return $result;
    }
    
    function get_pattern_info($id){
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->sync_event_table_name . "
				WHERE id=%d AND blog_id=%d ", array(
            $id,
            get_current_blog_id()
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    function update_event_list($eventFeed, $calendar_id) {
        foreach ($eventFeed as $event) {
            $id_array = explode('/', $event->id);
            $event_id = $id_array[count($id_array) - 1];
            if (!$this->is_event_exist($calendar_id, $event_id)) {
                $columns = array(
                    'calendar_id' => $calendar_id,
                    'event_id' => $event_id,
                    'event_title' => $event->title,
                    'start_time' => $event->when[0]->startTime,
                    'end_time' => $event->when[0]->endTime,
                    'event_where' => $event->where[0],
                    'event_content' => $event->content,
                    'event_author_name' => $event->author->name,
                    'event_author_email' => $event->author->email,
                    'blog_id' => get_current_blog_id(),
                    'blog_user_id' => get_current_user_id(),
                    'create_at' => time(),
                    'update_at' => time()
                );
                $this->wpdb->insert($this->sync_event_table_name, $columns);
            }
        }
    }

    function is_event_exist($calendar_id, $event_id) {
        $safe_sql = $this->wpdb->prepare("SELECT * FROM " . $this->sync_event_table_name . "
				WHERE calendar_id=%d AND event_id=%s blog_id=%d ", array(
            $calendar_id,
            $event_id,
            get_current_blog_id()
        ));
        $result = $this->wpdb->get_row($safe_sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }else{
            return true;
        }
    }

}
