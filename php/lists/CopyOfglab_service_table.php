<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class glab_service_table extends WP_List_Table {

	var $data = array();

	function set_glab_data($input) {
		$this->data = $input;
	}

	function column_cb($item) {
		return sprintf('<input type="checkbox" name="service[]" value="%s" />', $item['id']);
	}

	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => 'Title',
			'description' => 'Description',
			'color_value' => 'Service Color',
			'allow_multiple' => 'Allow Multiple',
			'status'      => 'Status'
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
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		));
		$this->items = $this->found_data;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name'   => array('name', false),
			'status' => array('status', false)
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
			case 'name':
			case 'description':
			case 'color_value':
			case 'allow_multiple':
			case 'status':
				return $item[$column_name];
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_name($item) {

		$actions = array(
			'edit'   => sprintf('<a href="?page=%s&action=%s&service=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
			'delete' => sprintf('<a href="?page=%s&action=%s&service=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),

		);

		if ($item['status'])
			$actions['deactivate'] = sprintf('<a href="?page=%s&action=%s&service=%s">Deactivate</a>', $_REQUEST['page'], 'deactivate', $item['id']);
		else
			$actions['activate'] = sprintf('<a href="?page=%s&action=%s&service=%s">Activate</a>', $_REQUEST['page'], 'activate', $item['id']);

		return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions));
	}

	function column_status($item) {
		$status_label = 'Inactive';
		if ($item['status'])
			$status_label = 'Active';
		return sprintf('%1$s', $status_label);
	}
	
	function column_allow_multiple($item){
		$mulitple_label = 'No';
		if ($item['allow_multiple'])
			$mulitple_label = 'Yes';
		return sprintf('%1$s', $mulitple_label);
	}
	
	function column_color_value($item){
		$color_html=sprintf('<div style="background:%s;width:20px;height:20px;"></div>',$item['color_value']);
		return sprintf('%1$s', $color_html);
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);
		return $actions;
	}
}
