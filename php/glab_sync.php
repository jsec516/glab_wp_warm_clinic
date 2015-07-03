<?php

class glab_sync {

    private $sync_layer;

    function __construct() {
        require_once 'lists/glab_sync_table.php';
        require_once 'lists/glab_sync_event_table.php';
        require_once 'glab_sync_layer.php';
        require_once 'forms/glab_sync_form.php';
        $this->sync_layer = new glab_sync_layer();
    }

    public function load() {
        $saved = null;
        if (isset($_POST['sync_update_nonce']) && wp_verify_nonce($_POST['sync_update_nonce'], 'sync_update')) {
            $saved = $this->sync_layer->save_account();
        }
        if (isset($_POST['update_clist_nonce']) && wp_verify_nonce($_POST['update_clist_nonce'], 'update_clist')) {
            $saved = $this->sync_calendar_list();
        }
        $data['account'] = $this->sync_layer->get_sync_account();
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Google Calendar Settings</h3></div>';
        if ($saved) {
            echo '<div class="updated">';
            echo '<p>' . __('Saved!', 'my-text-domain') . '</p>
                <p><strong>If the account is new,an email has been sent to your account and please try again to sync calendar.</strong></p>
			    </div>';
        }
        wp_enqueue_script('valdation_script', plugins_url('glab_clinic/assets/js/jquery.validate.min.js'));

        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('valdation_script'), false, true);
        wp_enqueue_style('wp-clinic-common', plugins_url('glab_clinic/assets/css/glab_common.css'));
        echo '<div class="form-wrapper">';
        glab_sync_form::generate_account_form($data);
        $data['calendars'] = $this->sync_layer->all_calendar();
        $list_table = new glab_sync_table();
        $list_table->set_glab_data($data['calendars']);
        $list_table->prepare_items();
        $list_table->display();
        echo '</div>';
        echo '</div>';
    }

    /*
      @name:get_calendar_list
      @purpose:get synced calendar list
     */

    function sync_calendar_list() {
        $path = dirname(__FILE__) . '/ZendGdata/library';
        $oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_Calendar');
        // get account info
        $account = $this->sync_layer->get_sync_account();
        if ($account):
            // User whose calendars you want to access
            $user = $account['g_account'];
            $pass = $account['g_password'];
            $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
            $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
            $service = new Zend_Gdata_Calendar($client);

            // Get the calendar list feed
            $listFeed = $service->getCalendarListFeed();
            $this->sync_layer->update_calendar_list($listFeed, $account);
        endif;
    }

    public function events() {
        $data = $this->sync_layer->all_events();
        $this->load_resource();
        echo '<div class="wrap glab_cas_wrap"><h2></h2>';
        echo '<div class="header"><h3>Synced Event List</h3></div>';
        echo '<div class="form-wrapper">';
        $list_table = new glab_sync_event_table();
        $list_table->set_glab_data($data);
        $list_table->prepare_items();
        $list_table->display();
        echo '</div>';
        echo '<div style="display: none;" id="patternFrm"></div>';
        echo '</div>';
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
        wp_enqueue_script('my-script-handle', plugins_url('glab_clinic/assets/js/main.js'), array('user-script', 'appt-script'), null, true);
        //echo '<script type="text/javascript" src=' . $base_url . 'calender-files/js/calendar_sync.js></script>';
        wp_enqueue_style('calendar-style', plugins_url('glab_clinic/assets/css/calender_css.css'));
        wp_enqueue_style('next-calendar-style', plugins_url('glab_clinic/assets/css/calender.css'));
        wp_enqueue_style('barchart-style', plugins_url('glab_clinic/assets/css/barChart.css'));
    }

    //sync calendar's event
    function get_events_synced($calendar_id) {
        $calendar_info = $this->sync_layer->get_calendar_info($calendar_id);
        $path = dirname(__FILE__) . '/ZendGdata/library';
        $oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_Calendar');
        // User whose calendars you want to access
        $accountArr = $this->sync_layer->get_sync_account();
        // User whose calendars you want to access
        $user = $accountArr['g_account'];
        $pass = $accountArr['g_password'];
        $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
        $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
        $service = new Zend_Gdata_Calendar($client);
        $idSPlit = explode('/', $calendarInfo['calendar_id']);
        $calendar_id = $idSPlit[count($idSPlit) - 1];
        $query = $service->newEventQuery();
        // Set different query parameters
        $query->setUser($calendar_id);
        $query->setVisibility('private');
        $query->setProjection('full');
        $query->setOrderby('starttime');
        try {
            $eventFeed = $service->getCalendarEventFeed($query);
        } catch (Zend_Gdata_App_Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        $this->sync_layer->update_event_list($eventFeed, $calendar_id);
        echo "success";
        exit;
    }

}
