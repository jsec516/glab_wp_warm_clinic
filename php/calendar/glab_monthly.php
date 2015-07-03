<?php
class glab_monthly {
	private $clinic_layer;
	function __construct() {
		$this->clinic_layer = new glab_clinic_layer();
	}

	function regular_view($args) {
		require_once 'glab_monthly_html.php';		$data = $this->prepare_calendar_attributes($args);		$data = array_merge($args, $data);		glab_monthly_html::generate_calendar($data);
	}

	private function prepare_calendar_attributes($data) {
		$data['monthly_off_days'] = $this->clinic_layer->get_monthly_off_days($data['month'], $data['year']);		$data['appointments'] = $this->clinic_layer->get_monthly_appointments($data['month'], $data['year']);		$clickArr = array();
		if (strtolower($data['view_type']) == 'monthly') {
			$clickArr[] = array('left' => 'daily_view_slider()', 'right' => 'week_view_slider()');
		} elseif (strtolower($data['view_type']) == 'weekly') {
			$clickArr[] = array('left' => 'month_view_slider()', 'right' => 'daily_view_slider()');
		} else {
			$clickArr[] = array('left' => 'week_view_slider()', 'right' => 'month_view_slider()');
		}
		$data['click_array'] = $clickArr;

		return $data;
	}

}
