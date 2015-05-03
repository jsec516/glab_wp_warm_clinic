<?php
class glab_regular_daily {
	private $clinic_layer;
	function __construct() {
		$this->clinic_layer = new glab_clinic_layer();
	}

	function regular_view($args) {
		require_once 'glab_regular_daily_html.php';
		$data = $args;
		return glab_regular_daily_html::generate_calendar($data);
	}
}
