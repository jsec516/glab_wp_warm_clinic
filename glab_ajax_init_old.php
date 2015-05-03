<?php
$plugin_path = dirname(dirname(__FILE__));
$wp_content_path = dirname($plugin_path);
$root_path = dirname($wp_content_path);
require_once( $root_path . "/wp-load.php" );

// service allocation to room request handler
if (isset($_POST['service_alloc'])) {
    $clinic_layer_obj = new glab_clinic_layer();
    $clinic_layer_obj->add_room_service(trim($_POST['room_id']), trim($_POST['service_id']));
}