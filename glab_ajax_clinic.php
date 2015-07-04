<?php

$plugin_path = dirname(dirname(__FILE__));
$wp_content_path = dirname($plugin_path);
$root_path = dirname($wp_content_path);
require_once( $root_path . "/wp-load.php" );
require_once 'php/glab_ajax_loader.php';

/*
 *  service allocation to room request handler
 *  old: devlounge-plugin-series\php\ajax.php
 */
if (isset($_POST['service_alloc'])) {
    $service_layer_obj = new glab_service_layer();
    $services = $service_layer_obj->add_room_service(trim($_POST['room_id']), trim($_POST['service_id']));
}

/*
 * existing service room html for update
 */

if(isset($_GET['get_room_service_html'])){
	require_once 'php/glab_clinic.php';
	$clinic_obj=new glab_clinic();
	$clinic_obj->get_room_service_html();
	exit;
}

/*
 *  duplicate room generator
 *  old: devlounge-plugin-series\php\ajax.php
 */
if (isset($_POST['CREATE_ROOM'])) {
    require_once 'php/glab_clinic.php';
    $clinic_obj = new glab_clinic();
    $saved = $clinic_obj->generate_duplicate_room(trim($_POST['new_name']), trim($_POST['duplicate_id']));
    $data = array();
    if ($saved) {
        $data['status'] = 'success';
        $data['msg'] = 'done';
    } else {
        $data['status'] = 'failed';
        $data['msg'] = 'failed to create duplicate room.';
    }
    echo json_encode($data);
    exit;
}

/*
 *  deactivate room
 *  old: devlounge-plugin-series\php\ajax.php
 */
if (isset($_POST['DEACTIVATE_ROOM'])) {
    $clinic_layer_obj = new glab_clinic_layer();
    $saved = $clinic_layer_obj->deactivate(trim($_POST['room_id']));
    $data = array();
    $data['status'] = 'success';
    $data['msg'] = 'done';
    echo json_encode($data);
    exit;
}

/*
 *  delete room service
 *  old: devlounge-plugin-series\php\ajax.php
 */
if (isset($_POST['DELETE_ROOM_SERVICE'])) {
    $clinic_layer_obj = new glab_clinic_layer();
    $saved = $clinic_layer_obj->delete_room_service(trim($_POST['room_id']),trim($_POST['service_id']));
    $data = array();
    $data['status'] = 'success';
    $data['msg'] = 'done';
    echo json_encode($data);
    exit;
}

/*
 *  room service retrieve to resort the room
 *  old: devlounge-plugin-series\php\ajax.php
 */
if (isset($_POST['room_resort'])) {
    $service_layer_obj = new glab_service_layer();
    $services = $service_layer_obj->get_room_services(trim($_POST['room_id']));
    $response = "<ul class='glab-room-services'>";
    foreach ($services as $row) {
        $response.="<li>{$row['name']} <a href='' data-service_id='{$row['id']}'    data-room_id='{$_POST['room_id']}' class='glab-remove-service'>X</a></li>";
    }
    $response.="</ul>";
    $status = "ok";

    $response_data = array('status' => $status, 'data' => $response);
    echo json_encode($response_data);
    exit;
}

/*
 *  weekly view of calendar
 *  old: week_ajax.php, month_ajax.php(refresh slider)
 */
if (isset($_POST['change_calendar_view'])) {
    $calendar_obj = new glab_calendar();
    if ($_POST['view_type'] != 'monthly')
        call_user_func(array($calendar_obj, $_POST['view_type']), $_POST);
    else
        call_user_func(array($calendar_obj, 'load'), $_POST);
}

/*
 *  add new appointment form loader
 *  old: daily_slider.php
 */
if (isset($_POST['add_new_appointment'])) {
    $appt_obj = new glab_appointment();
    call_user_func(array($appt_obj, 'add'), $_POST);
}

/* update clinic available hours based on date
 * it happens when view type changed, specially for view type 3
 * old: clinic_time.php
 */
if (isset($_POST['update_clinic_hour'])) {
    $appt_obj = new glab_appointment();
    call_user_func(array($appt_obj, 'date_available_slots'), $_POST);
}

/* update available slot based on practitioner id or practioner id with date
 * it happens when practitioner changed on add appointment form
 * old: docters.php, doctors_time.php
 */
if (isset($_POST['new_app_prac_filter'])) {
    if ($_POST['filter_type'] == 'only_services') {
        $prac_obj = new glab_practitioner();
        call_user_func(array($prac_obj, 'get_services'), $_REQUEST['id']);
    } else {
        $appt_obj = new glab_appointment();
        call_user_func(array($appt_obj, 'get_date_based_slot'), $_REQUEST);
    }
}

/* update available slot based on practitioner id, service, date
 * it happens when service changed on add appointment form
 * old: availableTimeSlot_slider_check.php
 */
if (isset($_POST['load_available_slot'])) {
    $appt_obj = new glab_appointment();
    call_user_func(array($appt_obj, 'get_service_based_slot'));
}

/* fetch user based on first name, or last name or email
 * 
 * old: suggestion.php
 */
if (isset($_POST['query_based_customer'])) {
    $customer_obj = new glab_customer();
    call_user_func(array($customer_obj, 'filter_based_on'));
}

/* practitioner break type appointment handler
 * 
 * old: break_practitioner.php
 */

if (isset($_POST['break_practitioner'])) {
    $appt_obj = new glab_appointment();
    call_user_func(array($appt_obj, 'break_practitioner'));
}

/* block entire clinic appointment handler
 * 
 * old: block_clinic.php
 */

if (isset($_POST['block_clinic'])) {
    $appt_obj = new glab_appointment();
    call_user_func(array($appt_obj, 'block_clinic'));
}

/* block entire clinic appointment handler
 * 
 * old: add_appointment_day.php, edit_appointment_day.php
 */

if (isset($_POST['submit_regular_app'])) {
    $appt_obj = new glab_appointment();
    call_user_func(array($appt_obj, 'submit_regular_app'));
}


/*
 * block entire clinic appointment handler
 */

if (isset($_POST['load_waiting_schedule'])) {
    $wait_obj = new glab_waiting();
    call_user_func(array($wait_obj, 'load_waiting_schedule'), $_POST['prac_id']);
}

/*
 * load services
 */

if (isset($_POST['load_prac_services'])) {
    $prac_obj = new glab_practitioner();
    call_user_func(array($prac_obj, 'get_services'), $_POST['prac_id']);
}

/*
 * load reminder practitioner services
*/

if (isset($_POST['load_prac_reminder_services'])) {
	$reminder_obj = new glab_reminder();
	call_user_func(array($reminder_obj, 'get_prac_services'), $_POST);
}

/*
 * purpose: load add appointment form to set pattern of synced events
 * old : pattern_daily_slider.php
 */

if (isset($_POST['load_pattern_form'])) {
    $appt_obj = new glab_appointment();
    call_user_func(array($appt_obj, 'add'), $_POST);
}

/*
 * purpose: import csv file
 * old : process_csv.php
 */

if (isset($_POST['process_csv'])) {
    require_once 'php/glab_import.php';
    $import_obj = new glab_import();
    call_user_func(array($import_obj, 'process'));
}

/*
 * load waiting apps for confirmation
 * old: showAppConfirmation.php
 */
if(isset($_POST['showAppConfirmation'])){
    $wait_obj = new glab_waiting();
    call_user_func(array($wait_obj, 'load_app_confirmation'));
}

/*
 * load particular waiting info
 * old: waitingInfo.php
 */
if(isset($_POST['waitingInfo'])){
    $wait_obj = new glab_waiting();
    $info = call_user_func(array($wait_obj, 'load_waiting_info'));
    echo json_encode($info);exit;
}

/*
 * load waiting apps time
 * old: waittime.php
 */
if(isset($_POST['waitTime'])){
    $wait_obj = new glab_waiting();
    call_user_func(array($wait_obj, 'load_wait_time'));
}

/*
 * finalize waiting appointment
 * old : finalizeApp.php
 */

if(isset($_POST['finalizeWaitingApp'])){
    $wait_obj = new glab_waiting();
    $req_response = call_user_func(array($wait_obj, 'finalize_waiting'));
    if($req_response=='finalized'){
        echo "Appointment Finalized";
    }else{
        echo "Failed to finalized :".$req_response;
    }
    exit;
}

/*
 * get appointment information on hover
 * old : tooltipInfo.php
 */

if(isset($_GET['tooltipInfo'])){
    $appt_obj=new glab_appointment();
    $appt_obj->view_appt_info();
    exit;
}

/*
 * show appointment details with edit button
 * old : showAppDetails.php
 */

if(isset($_POST['showAppDetails'])){
    $appt_obj=new glab_appointment();
    $appt_obj->show_app_details();
    exit;
}

/*
 * delete appointment
 * old : deleteApp.php
 */

if(isset($_POST['deleteApp'])){
    $appt_obj=new glab_appointment();
    $appt_obj->delete(trim($_POST['id']));
    echo "appointment cancelled";
    exit;
}