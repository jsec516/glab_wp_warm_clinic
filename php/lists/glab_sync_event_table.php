<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class glab_sync_event_table extends WP_List_Table {

    var $data = array();

    function set_glab_data($input) {
        $this->data = $input;
    }

    function column_action($item) {
        $date = substr(trim($item['start_time']), 0, 10);
        $date_arr = explode('-', $date);
        return sprintf('<a href="#" data-day="%s" data-month="%s" data-year="%s" data-id="%d" class="set-pattern">Set Pattern</a>', $date_arr[2], $date_arr[1], $date_arr[0], $item['id']);
    }

    function get_columns() {
        $columns = array(
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'event_content' => 'Content',
            'action' => 'Action',
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
                //'name'   => array('name', false),
                //'status' => array('status', false)
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b) {
        // If no sort, default to title
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'name';
        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'start_time':
            case 'end_time':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_event_content($item) {
        $format_array = array($item['event_title'], $item['event_content'], $item['event_where']);
        $filtered_event = array_filter($format_array, 'strlen'); //remove all empty element
        $event_string = implode(' - ', $filtered_event);
        return $event_string;
    }

    function get_bulk_actions() {
        $actions = array(
            '0' => 'Unsolved',
            '1' => 'Solved'
        );
        return $actions;
    }

    function no_items() {
        _e('No Calendar Event found.');
    }

}
