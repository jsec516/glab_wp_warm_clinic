<?php

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

class glab_waiting_table extends WP_List_Table {

	var $data = array();

	function set_glab_data($input) {
		$this->data = $input;
	}

	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'cus_name'  => 'Patient',
			'prac_name' => 'Practioner',
			'service'   => 'Service',
			'mon'       => 'Mon',
			'tue'       => 'Tue',
			'wed'       => 'Wed',
			'thu'       => 'Thu',
			'fri'       => 'Fri',
			'sat'       => 'Sat',
			'sun'       => 'Sun',
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
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		));
		$this->items = $this->found_data;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'cus_name' => array('cus_name', false),
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
			case 'mon':
			case 'tue':
			case 'wed':
			case 'thu':
			case 'fri':
			case 'sat':
			case 'sun':
				return $item[$column_name];
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	function column_cb($item) {
		return sprintf('<input type="checkbox" name="wait_appt[]" value="%s" />', $item['id']);
	}

	function column_cus_name($item) {
		$actions = array(
				'edit'   => sprintf('<a href="?page=%s&action=%s&waiting=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
				'delete' => sprintf('<a href="?page=%s&action=%s&waiting=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
		);
		
		return sprintf('%1$s %2$s', $item['cus_name'], $this->row_actions($actions));
	}		function column_mon($item){				return $this->humanize_slot($item['mon']);	}	function column_tue($item){
		return $this->humanize_slot($item['tue']);
	} 		function column_wed($item){
		return $this->humanize_slot($item['wed']);
	}	function column_thu($item){
		return $this->humanize_slot($item['thu']);
	}		function column_fri($item){
		return $this->humanize_slot($item['fri']);
	}		function column_sat($item){		return $this->humanize_slot($item['sat']);
	}
	
	function column_sun($item){
		return $this->humanize_slot($item['sun']);
	}		private function humanize_slot($slot){				if(!$slot)			return '-';				$parts = explode('-',$slot);		$start=explode(':',$parts[0]);		$start_hour=$start[0];		$start_meridian='am';		if($start_hour>12){			$start_hour=$start_hour-12;			$start_meridian='pm';		}				$end=explode(':',$parts[1]);		$end_hour=$end[0];		$end_meridian='am';		if($end_hour>12){
			$end_hour=$end_hour-12;			$end_meridian='pm';
		}		return str_pad($start_hour,2,'0',STR_PAD_LEFT).':'.str_pad($start[1],2,'0',STR_PAD_LEFT).' '.$start_meridian.' - '.str_pad($end_hour,2,'0',STR_PAD_LEFT).':'.str_pad($end[1],2,'0',STR_PAD_LEFT).' '.$end_meridian;	}

	function column_status($item) {
		$status_label = 'Inactive';
		if ($item['status'])
			$status_label = 'Active';
		return sprintf('%1$s', $status_label);
	}

	function column_expected_day($item) {
		$mulitple_label = 'No';
		if ($item['allow_multiple'])
			$mulitple_label = 'Yes';
		return sprintf('%1$s', $mulitple_label);
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);
		return $actions;
	}

	function no_items() {
		_e('No Waiting Appointment found.');
	}

}
