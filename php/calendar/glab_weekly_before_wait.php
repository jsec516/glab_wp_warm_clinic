<?php

class glab_weekly {

    private $clinic_layer;
    private $app_layer;

    function __construct() {
        $this->clinic_layer = new glab_clinic_layer();
        $this->app_layer = new glab_appointment_layer();
    }

    function regular_view($args) {
        require_once 'glab_weekly_html.php';
        $data = $this->prepare_calendar_attributes($args);
        $data = array_merge($args, $data);
        return glab_weekly_html::generate_calendar($data);
    }

    private function prepare_calendar_attributes($data) {
        

        return $data;
    }

}
