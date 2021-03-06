<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class glab_reminder_table extends WP_List_Table {

    var $data = array();

    function set_glab_data($input) {
        $this->data = $input;
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="reminders[]" value="%s" />', $item['id']);
    }

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'practitioner_id' => 'Practioner',
            'services_name' => 'Services',
            'reminder_type' => 'Type',
            'content' => 'Content'
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $per_page = 5;
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
            'reminder_type' => array('reminder_type', false)
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b) {
        
        // If no sort, default to title
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'reminder_type';
        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'services_name':
            case 'reminder_type':
            case 'content':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_practitioner_id($item) {
        $actions = array(
            'edit' => sprintf('<a href="?page=%s&action=%s&reminder=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
            'delete' => sprintf('<a href="?page=%s&action=%s&reminder=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
        );

        return sprintf('%1$s %2$s', $item['prac_name'], $this->row_actions($actions));
    }

    function column_reminder_type($item) {
        $reminder_label = 'Email';
        if ($item['reminder_type']=='2')
            $reminder_label = 'Call';
        return sprintf('%1$s', $reminder_label);
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

}
