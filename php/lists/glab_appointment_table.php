<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class glab_appointment_table extends WP_List_Table {

    var $data = array();

    function set_glab_data($input) {
        $this->data = $input;
    }

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'cus_name' => 'Patient',
            'service' => 'Service',
            'prac_name' => 'Practioner',
            'app_time' => 'Time',
            'reminder_type' => 'Reminder',
            'reminder_sent' => 'Reminder Sent',
            'status' => 'Status'
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = count($this->data);
        $this->found_data = array_slice($this->data, (($current_page - 1) * $per_page), $per_page);
        usort($this->data, array(&$this, 'usort_reorder'));
        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ));
        $this->items = $this->found_data;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'cus_name' => array('cus_name', false),
            'status' => array('status', false)
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b) {
        // If no sort, default to title
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'cus_name';
        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'service':
            case 'prac_name':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="appointments[]" value="%s" />', $item['id']);
    }

    function column_cus_name($item) {

        $actions = array(
            //'edit' => sprintf('<a href="?page=%s&action=%s&app=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
            'delete' => sprintf('<a href="?page=%s&action=%s&app=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
        );

        return sprintf('%1$s %2$s', $item['cus_name'], $this->row_actions($actions));
    }

    function column_status($item) {
        $status_label = 'Inactive';
        if ($item['status'])
            $status_label = 'Active';
        return sprintf('%1$s', $status_label);
    }

    function column_app_time($item) {
        return sprintf('%1$s', $item['app_time']);
    }

    function column_reminder_type($item) {
        $reminder_type_label = 'Email';
        if ($item['reminder_type']=='1')
            $reminder_type_label = 'Call';
        return sprintf('%1$s', $reminder_type_label);
    }

    function column_reminder_sent($item) {
        $reminder_sent_label = 'No';
        if ($item['reminder_sent'])
            $reminder_sent_label = 'Yes';
        return sprintf('%1$s', $reminder_sent_label);
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function no_items() {
        _e('No Appointment found.');
    }

}
