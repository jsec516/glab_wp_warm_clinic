<?php
class glab_daily {
	private $clinic_layer;
	function __construct() {
		$this->clinic_layer = new glab_clinic_layer();
	}

	function regular_view($args) {
		require_once 'glab_daily_html.php';
		$data = $args;
		return glab_daily_html::generate_calendar($data);
	}
}