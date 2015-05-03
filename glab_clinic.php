<?php

/**
 * Plugin Name: clinic management plugins
 * Plugin URI: http://wp-clinic.glitzlab.com/
 * Description: manage clinic appointment system
 * Version: 1.0
 * Author: glitzlab
 * Author URI: http://glitzlab.com
 * License: GPL2
 */
require_once 'php/glab_loader.php';


//load admin menus
$glab_init = new glab_init();
add_action('admin_menu', array($glab_init, 'add_admin_menus'));
// remove toolbar additional menu node
add_action( 'admin_bar_menu', array($glab_init, 'remove_toolbar_menu'), 999 );
//uncomment line below in production
add_action( 'admin_menu', array($glab_init, 'remove_menus') );

/* It will remove the tabs, not hide them with CSS */

add_filter( 'contextual_help', 'mytheme_remove_help_tabs', 999, 3 );
function mytheme_remove_help_tabs($old_help, $screen_id, $screen){
    $screen->remove_help_tabs();
    return $old_help;
}

add_filter('screen_options_show_screen', '__return_false');

// create tables when plugins installed
register_activation_hook(__FILE__, array($glab_init, 'add_tables'));

// create dashboard widget
$dasboard_widget = new glab_dashboard();

// waiting alert html
$waiting_obj = new glab_waiting();
$waitingList=$waiting_obj->getWaitingListMenu();
/*$waitingList=$waiting_obj->getWaitingList();*/
function glab_wait_div_script() {
    wp_enqueue_script('wait_alert_script', plugins_url('glab_clinic/assets/js/wait_alert.js'), array('jquery'));
    echo "<style>#wpfooter{display:none;}</style><input type='hidden' name='glab_asset' value='".plugins_url('glab_clinic/assets/')."' />";
}
add_action('in_admin_footer', 'glab_wait_div_script');
// end of waiting alert html

// add links/menus to the admin bar
function mytheme_admin_bar_render() {
    global $wp_admin_bar, $waitingList;
    
    $calendar = new glab_calendar();
    $menu_id = 'new-content'; // the menu id which you want to remove
    $wp_admin_bar->remove_menu($menu_id);
    $menu_id = 'comments'; // the menu id which you want to remove
    $wp_admin_bar->remove_menu($menu_id);
    $menu_id = 'updates'; // the menu id which you want to remove
    $wp_admin_bar->remove_menu($menu_id);
    $menu_id = 'wp-logo'; // the menu id which you want to remove
    $wp_admin_bar->remove_menu($menu_id);
    $menu_id = 'view-site';
    $wp_admin_bar->remove_menu($menu_id);


    $wp_admin_bar->add_menu(array(
        'parent' => false, // use 'false' for a root menu, or pass the ID of the parent menu
        'id' => 'wait-indicator', // link ID, defaults to a sanitized title value
        'title' => __('Waiting List Alert '.$waitingList['total_slot'].' New Slot'), // link title
        'href' => admin_url(array($calendar, 'load')), // name of file
        'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
    ));
    $sub_wait=0;
    foreach($waitingList['lists'] as $submenu){
        $wp_admin_bar->add_menu( array(
            'id'    => 'wait-avail-'.$sub_wait++,
            'parent' => 'wait-indicator',
            'title' => $submenu['content'],
            'href'  => $submenu['href'],
            'meta'  => array(
                'class' => 'my_menu_item_class'
            ),
        ));
    }
}

add_filter('admin_bar_menu', 'howdy_to_greetings', 25);

function howdy_to_greetings($wp_admin_bar) {
    $my_account = $wp_admin_bar->get_node('my-account');
    $newtitle = str_replace('Howdy,', 'Logged in as:', $my_account->title);
    $wp_admin_bar->add_node(array(
        'id' => 'my-account',
        'title' => $newtitle,
    ));
}

add_action('admin_head', 'admin_css_changes');

function admin_css_changes() {
    $css_url = plugins_url('glab_clinic/assets/css/admin_changes.css');
    echo '<link rel="stylesheet" href="' . $css_url . '" type="text/css" media="all" />';
}

function remove_footer_admin() {
    echo 'Developed by <a href="http://www.glitzlab.com" target="_blank">Glitzlab</a></p>';
}

add_filter('admin_footer_text', 'remove_footer_admin');

add_action('wp_before_admin_bar_render', 'mytheme_admin_bar_render');

/* remove all update notice */
add_action('after_setup_theme','remove_core_updates');
function remove_core_updates()
{
 if(! current_user_can('update_core')){return;}
 add_action('init', create_function('$a',"remove_action( 'init', 'wp_version_check' );"),2);
 add_filter('pre_option_update_core','__return_null');
 add_filter('pre_site_transient_update_core','__return_null');
}

