<?php

class glab_init {

    private $glab_db_version = '1.0';

    public function add_admin_menus() {
        $calendar_obj = new glab_calendar();
        $sync_obj = new glab_sync();
        add_menu_page("Calendar", "Calendar", "manage_options", "glab_calendar", array($calendar_obj, "load"), plugins_url('glab_clinic/assets/images/calendar_hover.png'), 81);
        add_submenu_page("glab_calendar", "Global Calendar", "Global Calendar", "manage_options", "glab_calendar", array($calendar_obj, 'load'));
        add_submenu_page("glab_calendar", "Sync Calendar", "Sync Calendar", "manage_options", "glab_sync_calendar", array($sync_obj, 'load'));
        add_submenu_page("glab_calendar", "Synced Events", "Synced Events", "manage_options", "glab_sync_events", array($sync_obj, 'events'));

        $appt_obj = new glab_appointment();
        $waiting_obj = new glab_waiting();
        add_menu_page("Appointment", "Appointment", "manage_options", "glab_appointment", array($appt_obj, "load"), plugins_url('glab_clinic/assets/images/user_hover.png'), 82);
        add_submenu_page("glab_appointment", "Appointment List", "Appointment List", "manage_options", "glab_appointment", array($appt_obj, 'load'));
        add_submenu_page("glab_appointment", "Waiting List", "Waiting List", "manage_options", "glab_waiting", array($waiting_obj, 'load'));
        add_submenu_page("glab_appointment", "Add Waiting", "Add Waiting", "manage_options", "glab_add_waiting", array($waiting_obj, 'add'));

        $customer_obj = new glab_customer();
        add_menu_page("Customer", "Customer", "manage_options", "glab_customer", array($customer_obj, "load"), plugins_url('glab_clinic/assets/images/customer_hover.png'), 83);
        add_submenu_page("glab_customer", "Customer List", "Customer List", "manage_options", "glab_customer", array($customer_obj, 'load'));
        add_submenu_page("glab_customer", "Add New", "Add New", "manage_options", "glab_add_customer", array($customer_obj, 'add'));

        $reminder_obj = new glab_reminder();
        add_menu_page("Reminders", "Reminder", "manage_options", "glab_reminder", array($reminder_obj, "load"), plugins_url('glab_clinic/assets/images/reminders_hover.png'), 84);
        add_submenu_page("glab_reminder", "Reminder List", "Reminder List", "manage_options", "glab_reminder", array($reminder_obj, 'load'));
        add_submenu_page("glab_reminder", "Add Email Reminder", "Add Email Reminder", "manage_options", "glab_eradd_reminder", array($reminder_obj, 'er_add'));
        add_submenu_page("glab_reminder", "Add Call Reminder", "Add Call Reminder", "manage_options", "glab_cradd_reminder", array($reminder_obj, 'cr_add'));

        $clinic_obj = new glab_clinic();
        add_menu_page("Clinic", "Clinic", "manage_options", "glab_clinic", array($clinic_obj, "load"), plugins_url('glab_clinic/assets/images/clinic_hover.png'), 85);
        add_submenu_page("glab_clinic", "Clinic Settings", "Clinic Settings", "manage_options", "glab_clinic", array($clinic_obj, 'load'));
        add_submenu_page("glab_clinic", "Import Data", "Import Data", "manage_options", "glab_import_data", array($clinic_obj, 'import'));
        add_submenu_page("glab_clinic", "Frontend Settings", "Frontend Settings", "manage_options", "glab_frontend_settings", array($clinic_obj, 'frontend_settings'));
        add_submenu_page("glab_clinic", "Set Schedule", "Set Schedule", "manage_options", "glab_clinic_schedule", array($clinic_obj, 'set_schedule'));
        add_submenu_page("glab_clinic", "Cancellation Poll", "Cancellation Poll", "manage_options", "glab_cancel_poll", array($clinic_obj, 'cancellation_poll'));
        add_submenu_page("glab_clinic", "Confirmation Mail", "Confirmation Mail", "manage_options", "glab_user_confirmation", array($clinic_obj, 'user_confirmation'));

        $service_obj = new glab_service();
        add_menu_page("Service", "Service", "manage_options", "glab_service", array($service_obj, "load"), plugins_url('glab_clinic/assets/images/services_hover.png'), 86);
        add_submenu_page("glab_service", "Service List", "Service List", "manage_options", "glab_service", array($service_obj, 'load'));
        add_submenu_page("glab_service", "Add New", "Add New", "manage_options", "glab_add_service", array($service_obj, 'add'));

        $prac_obj = new glab_practitioner();
        add_menu_page("Practitioner", "Practitioner", "manage_options", "glab_practitioner", array($prac_obj, "load"), plugins_url('glab_clinic/assets/images/practitioners_hover.png'), 87);
        add_submenu_page("glab_practitioner", "Practitioner List", "Practitioner List", "manage_options", "glab_practitioner", array($prac_obj, 'load'));
        add_submenu_page("glab_practitioner", "Add New", "Add New", "manage_options", "glab_add_practitioner", array($prac_obj, 'add'));
        /* add_submenu_page("glab_practitioner", "Set Schedule", "Set Schedule", "manage_options", "glab_add_practitioner_schedule", array($prac_obj, 'set_schedule')); */
    }
    
    function remove_toolbar_menu(){
        global $wp_admin_bar;
        $sites = wp_get_sites();
        foreach($sites as $site){
        $blog_comment_id='blog-'.$site['blog_id'].'-c';
        $blog_dashboard_id='blog-'.$site['blog_id'].'-n';
        $wp_admin_bar->remove_node( $blog_comment_id );
        $wp_admin_bar->remove_node( $blog_dashboard_id );
        }
    }

    function remove_menus() {
        remove_menu_page('edit.php');                   //Posts
        remove_menu_page('upload.php');                 //Media
        remove_menu_page('edit.php?post_type=page');    //Pages
        remove_menu_page('edit-comments.php');          //Comments
        remove_menu_page('themes.php');                 //Appearance
        remove_menu_page('plugins.php');                //Plugins
        remove_menu_page('users.php');                  //Users
        remove_menu_page('tools.php');                  //Tools
        remove_menu_page('options-general.php');        //Settings
    }

    public function add_tables() {
        $sql = '';
        $tables = new glab_tables();
        $sql.=$tables->appointment();
        $sql.=$tables->config_clinic();
        $sql.=$tables->practitoner();
        $sql.=$tables->reminder();
        $sql.=$tables->room();
        $sql.=$tables->service();
        $sql.=$tables->user();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option("glab_db_version", $this->glab_db_version);
    }

}
