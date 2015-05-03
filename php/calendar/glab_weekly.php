<?php

class glab_weekly {



    private $clinic_layer;

    private $app_layer;



    function __construct() {

        $this->clinic_layer = new glab_clinic_layer();

        $this->app_layer = new glab_appointment_layer();

    }



    function regular_view($args) {

        $data = $this->prepare_calendar_attributes($args);

        $data = array_merge($args, $data);

        if (isset($_GET['wait_request_day'])) {

            require_once 'glab_weekly_regular_html.php';

            $php_folder=dirname(dirname(__FILE__));

            require_once $php_folder.'/glab_waiting_layer.php';

            $waiting_layer=new glab_waiting_layer();

            $data['waiting_appts']=$waiting_layer->get_specific_day_waiting(substr($args['wait_request_day'], 0, 3));

            return glab_weekly_regular_html::generate_calendar($data);

        } else {

            require_once 'glab_weekly_html.php';

            return glab_weekly_html::generate_calendar($data);

        }

    }



    private function prepare_calendar_attributes($data) {

        

        

        return $data;

    }



}

