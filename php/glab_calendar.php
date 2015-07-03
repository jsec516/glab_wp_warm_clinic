<?php

class glab_calendar {

    private $clinic_layer;
    private $prac_layer;

    function __construct() {
        $this->clinic_layer = new glab_clinic_layer();
        $this->prac_layer = new glab_practitioner_layer();
    }

    public function load() {
        $this->load_resource();
        $data = $this->prepare_common_attributes();
        call_user_func(array($this, $data['view_type']), $data);
    }

    private function prepare_common_attributes() {
        $data['view_type'] = $this->set_current_view_type();
        $data['month'] = (!isset($_REQUEST['month']) || $_REQUEST['month'] == '' || !is_numeric(trim($_REQUEST['month']))) ? date('n') : trim($_REQUEST['month']);
        $data['year'] = (!isset($_REQUEST['year']) || $_REQUEST['year'] == '' || !is_numeric(trim($_REQUEST['year']))) ? date('Y') : trim($_REQUEST['year']);
        $data['day'] = (!isset($_REQUEST['num_day']) || $_REQUEST['num_day'] == '' || !is_numeric(trim($_REQUEST['num_day']))) ? date('d') : trim($_REQUEST['num_day']);
        $data['timestamp'] = mktime(1, 1, 1, $data['month'], $data['day'], $data['year']);
        $data['previous_month'] = explode('-', date('n-Y', strtotime('-1 month', $data['timestamp'])));
        $data['next_month'] = explode('-', date('n-Y', strtotime('+1 month', $data['timestamp'])));
        $data['recent_month'] = explode('-', date('n-Y', strtotime('+0 month', $data['timestamp'])));
        $data['recent_month_in_text'] = explode('-', date('F-Y', strtotime('+0 month', $data['timestamp'])));
        $data['recent_week_number'] = date('W', strtotime('+0 month', $data['timestamp']));

        $data['prev_view_type']=  $this->prev_view_type($data['view_type']);
        $data['next_view_type'] = $this->next_view_type($data['view_type']);
        $data['regular_off_days'] = $this->clinic_layer->regular_off_days();
        $data['rooms'] = $this->clinic_layer->get_rooms();
        $data['practitioners'] = $this->prac_layer->all(false);
        $data['clinic_schedule'] = $this->clinic_layer->get_schedule();
        return $data;
    }

    private function set_current_view_type() {
        $view_type = 'monthly';

        if(isset($_REQUEST['view_type'])){
            switch ($_REQUEST['view_type']) {
                case 'weekly':
                    $view_type='weekly';
                    break;
                case 'daily':
                    $view_type='daily';
                    break;
                default:
                    $view_type='monthly';
                    break;
            }
        }
        return $view_type;
    }
    
    private function prev_view_type($current_view_type){
        $prev_view_type = 'daily';
            switch ($current_view_type) {
                case 'weekly':
                    $prev_view_type='monthly';
                    break;
                case 'daily':
                    $prev_view_type='weekly';
                    break;
                default:
                    $prev_view_type='daily';
                    break;
            }
        return $prev_view_type;
    }
    
    private function next_view_type($current_view_type){
        $next_view_type = 'weekly';
            switch ($current_view_type) {
                case 'weekly':
                    $next_view_type='daily';
                    break;
                case 'daily':
                    $next_view_type='monthly';
                    break;
                default:
                    $next_view_type='weekly';
                    break;
            }
        return $next_view_type;
    }

    public function weekly($common_data = array()) {
        require_once 'calendar/glab_weekly.php';
        $calendar = new glab_weekly();
        $specified_data = $this->prepare_weekly_common_attributes();
        $data=array_merge($common_data, $specified_data);
        $week_view_html = $calendar->regular_view($data);
        echo '<script type="text/javascript">jQuery(document).ready(function($){$("a.black_tips").cluetip();});</script>';
        if(!isset($_GET['wait_request_day'])){
        echo '<div id="week_calendar_view">';
        echo $week_view_html;
        echo '</div>';
        exit;
        }
    }

    private function prepare_weekly_common_attributes() {
        $data = array();
        $data['cal_month'] = ($_REQUEST['month'] == '' or ! is_numeric(trim($_REQUEST['month']))) ? date('n') : trim($_REQUEST['month']);
        $data['cal_year'] = ($_REQUEST['year'] == '' or ! is_numeric(trim($_REQUEST['year']))) ? date('Y') : trim($_REQUEST['year']);
        $data['cal_day'] = ($_REQUEST['day'] == '' or ! is_numeric(trim($_REQUEST['day']))) ? date('d') : trim($_REQUEST['day']);
        $data['cal_timeStamp'] = mktime(1, 1, 1, $data['cal_month'], 1, $data['cal_year']);
        $data['cal_requested_timeStamp'] = mktime(1, 1, 1, $data['cal_month'], $data['cal_day'], $data['cal_year']);
        $data['previous_month'] = explode('-', date('n-Y', strtotime('-1 month', $data['cal_timeStamp'])));
        $data['next_month'] = explode('-', date('n-Y', strtotime('+1 month', $data['cal_timeStamp'])));
        $data['recent_month_char'] = explode('-', date('F-Y', strtotime('+0 month', $data['cal_timeStamp'])));
        $data['recent_month_number'] = explode('-', date('n-Y', strtotime('+0 month', $data['cal_timeStamp'])));
        $data['recent_month'] = explode('-', date('n-Y', strtotime('+0 month', $data['cal_timeStamp'])));
        $data['recent_week_number']=date('W', strtotime('+0 month', $data['cal_requested_timeStamp']));
        $data['next_view_type'] = $this->next_view_type('weekly');
        $data['prev_view_type']=$this->prev_view_type('weekly');
        $data['view_type'] = $this->set_current_view_type();

        // referece for old code compatibility

        $data['month'] = $data['cal_month'];
        $data['year'] = $data['cal_year'];
        $data['current_day'] = $data['cal_day'];
        if(isset($_GET['wait_request_day'])){
            $data['wait_request_day']=strtolower(trim($_REQUEST['wait_request_day']));
            $data['load_wait_apps']=true;
        }
        return $data;
    }

    public function monthly($data) {
        require_once 'calendar/glab_monthly.php';
        $calendar = new glab_monthly();
        $calendar->regular_view($data);
    }

    public function daily($data = array()) {
        $this->load_resource();
        $data = $this->prepare_daily_attributes();
        if ($data['regular']) {
            require_once 'calendar/glab_regular_daily.php';
            $calendar = new glab_regular_daily();
            return $calendar->regular_view($data);
        } else {
            require_once 'calendar/glab_daily.php';
            $calendar = new glab_daily();
            echo $calendar->regular_view($data);
            exit;
        }

        // pause at line 278 a.php
    }

    private function load_resource() {
        $base_url = plugins_url('glab_clinic/glab_ajax_clinic.php');
        $asset_url = plugins_url('glab_clinic/assets');
        echo "<script type='text/javascript'>var glab_ajax_url='{$base_url}';
                                            var  glab_asset_url='{$asset_url}';
               </script>";
        wp_enqueue_script('calendar-slider', plugins_url('glab_clinic/assets/js/jquery.easing.1.3.js'), array('jquery'), null, true);
        wp_enqueue_script('hover-intent-js', plugins_url('glab_clinic/assets/tool/clue_tip/jquery.hoverIntent.js'), array('jquery'), null, true);
        wp_enqueue_script('clue-tip-js', plugins_url('glab_clinic/assets/tool/clue_tip/jquery.cluetip.js'), array('jquery', 'hover-intent-js'), null, true);
        wp_enqueue_style('clue-tip-style', plugins_url('glab_clinic/assets/tool/clue_tip/jquery.cluetip.css'));
        wp_enqueue_script('cookie-js', plugins_url('glab_clinic/assets/js/jquery.cookie.js'), array('jquery'), null, true);
        wp_enqueue_script('ajax-script', plugins_url('glab_clinic/assets/js/jquery.glab_calendar.js'), array('jquery', 'calendar-slider', 'jquery-ui-datepicker', 'cookie-js'), null, true);
        wp_enqueue_script('user-script', plugins_url('glab_clinic/assets/js/jquery.glab_user.js'), array('ajax-script'), null, true);
        wp_enqueue_script('appt-script', plugins_url('glab_clinic/assets/js/jquery.glab_appointment.js'), array('ajax-script'), null, true);
        wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
        wp_enqueue_style('jquery-ui');
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('user-script','appt-script'), null, true);
        wp_enqueue_style('calendar-style', plugins_url('glab_clinic/assets/css/calender_css.css'));
        wp_enqueue_style('next-calendar-style', plugins_url('glab_clinic/assets/css/calender.css'));
        wp_enqueue_style('barchart-style', plugins_url('glab_clinic/assets/css/barChart.css'));
    }

    private function prepare_daily_attributes() {

        $data['room_data'] = $this->clinic_layer->get_rooms();
        $data['hours_data'] = $this->clinic_layer->get_hours();
        // if request comes from calendar menu (ajax request dailyViewSlider)
        if (isset($_POST['from_calendar_ajax'])) {
            $data['regular'] = false;
            $data['selected_day'] = $_POST["selected_day"];
            $data['selected_month'] = $_POST["selected_month"];
            $data['selected_year'] = $_POST["selected_year"];
            return $data;
        }
        $data['regular'] = (isset($_REQUEST['selected_day'])) ? false : true;
        $data['selected_day'] = (isset($_REQUEST['selected_day'])) ? $_REQUEST['selected_day'] : date("d");
        $data['selected_month'] = (isset($_REQUEST['selected_month'])) ? $_REQUEST['selected_month'] : date("m");
        $data['selected_year'] = (isset($_REQUEST['selected_year'])) ? $_REQUEST['selected_year'] : date("Y");
        $data['selected_date'] = mktime(0, 0, 0, $data['selected_month'], $data['selected_day'], $data['selected_year']);
        $data['selected_week'] = (int) date('W', $data['selected_date']);

        return $data;
    }

}
