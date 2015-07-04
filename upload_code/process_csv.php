<?php

if (is_uploaded_file($_FILES['csv_file']['tmp_name']) && is_csv_file($_FILES['csv_file']['name'])) {
    //Import uploaded file to Database
    $current_practitioner_list = get_existing_practitioner_list();
    $current_customer_list = get_existing_customer_list();
    $current_treatment_list = get_existing_treatment_list();
    $current_room_list = get_existing_room_list();
    $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
    $firstRow = true;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($firstRow) {
            if (!check_column_title_order($data)) {
                die("invalid order");
                exit;
            }
            $firstRow = false;
        } else {
            if (in_array($data[2], $current_customer_list)) {
                $ids = array_keys($current_customer_list, $data[2]);
                $user_id = $ids[0];
            } else {
                $user_id = create_new_customer($data);
            }

            if (in_array($data[4], $current_practitioner_list)) {
                $ids = array_keys($current_practitioner_list, $data[4]);
                $prac_id = $ids[0];
            } else {
                $prac_id = create_new_practitioner($data);
            }

            if (in_array($data[5], $current_treatment_list)) {
                $ids = array_keys($current_treatment_list, $data[5]);
                $treatment_id = $ids[0];
            } else {
                $treatment_id = create_new_treatment($data);
            }

            if (in_array($data[10], $current_room_list)) {
                $ids = array_keys($current_room_list, $data[10]);
                $room_id = $ids[0];
            } else {
                $room_id = create_new_room($data);
            }

            $duration = get_duration($data[6], $data[7]);
            $app_sql = "INSERT into glab_cas_appointments SET 
					first_name='{$data[0]}',
					last_name='{$data[1]}',
					email='{$data[2]}',
					user_id='{$user_id}',
					doctors='{$prac_id}',
					treatments='{$treatment_id}',
					app_date='{$data[8]}',
					app_time='{$data[6]}',
					app_end_time='{$data[7]}',
					app_reminder='{$data[9]}',
					reminder_status='N',
					app_status='1',
					room_id='{$room_id}',
					app_type='0',
					created_at='" . time() . "',
					updated_at='" . time() . "',
					treatment_duration='$duration'
			";
            mysql_query($app_sql) or die(mysql_error());
        }
    }

    fclose($handle);

    print "Import done";
    exit;
} else {
    print "invalid file";
    exit;
}

function get_duration($app_start_time, $app_end_time) {
    $duration = 0;
    // app_start_time=11:00AM
    // app_end_time=12:30PM
    $start_time_parts = explode(':', trim($app_start_time));
    $end_time_parts = explode(':', trim($app_end_time));
    if (substr($start_time_parts[1], -2) == 'PM' && $start_time_parts[0] != '12') {
        $start_time_parts[0]+=12;
    }

    if (substr($end_time_parts[1], -2) == 'PM' && $end_time_parts[0] != '12') {
        $end_time_parts[0]+=12;
    }
    $start_in_minute = ($start_time_parts[0] * 60) + intval($start_time_parts[1]);
    $end_in_minute = ($end_time_parts[0] * 60) + intval($end_time_parts[1]);

    $duration = $end_in_minute - $start_in_minute;
    return $duration;
}

function get_username($email) {
    $email_part = explode('@', $email);
    return $email_part[0];
}

function create_new_customer($data) {
    global $current_customer_list;
    $username = get_username($data[2]);
    $sql = "INSERT INTO users SET username='$username',
			firstname='{$data[0]}',
			lastname='{$data[1]}',
			email='{$data[2]}',
			phone='{$data[3]}',
			contactme='1',
			primary_phone='PHONE'";
    $result = mysql_query($sql);
    $customer_id = mysql_insert_id();
    // var_dump($customer_id);
    $current_customer_list = get_existing_customer_list();
    return $customer_id;
}

function create_new_practitioner($data) {
    $sql = "INSERT INTO doctors SET 
	cod_email='{$data[4]}'";
    $result = mysql_query($sql);
    $practitioner_id = mysql_insert_id();
    $current_practitioner_list = get_existing_practitioner_list();
    return $practitioner_id;
}

function create_new_treatment($data) {
    $sql = "INSERT INTO treatments SET
	flag_name='{$data[5]}'";
    $result = mysql_query($sql);
    $treatment_id = mysql_insert_id();
    $current_treatment_list = get_existing_treatment_list();
    return $treatment_id;
}

function create_new_room($data) {
    $sql = "INSERT INTO room SET
	room_name='{$data[10]}'";
    $result = mysql_query($sql);
    $room_id = mysql_insert_id();
    $current_room_list = get_existing_room_list();
    return $room_id;
}

function get_existing_practitioner_list() {
    $practitioner_emails = array();
    $result = mysql_query("SELECT codDr,cod_email FROM doctors");
    while ($row = mysql_fetch_array($result)) {
        $practitioner_emails[$row['codDr']] = $row['cod_email'];
    }
    return $practitioner_emails;
}

function get_existing_customer_list() {
    $customer_emails = array();
    $result = mysql_query("SELECT cod_user,email FROM users");
    while ($row = mysql_fetch_array($result)) {
        $customer_emails[$row['cod_user']] = $row['email'];
    }
    return $customer_emails;
}

function get_existing_treatment_list() {
    $treatment_lists = array();
    $result = mysql_query("SELECT cod_treatment,flag_name FROM treatments");
    while ($row = mysql_fetch_array($result)) {
        $treatment_lists[$row['cod_treatment']] = $row['flag_name'];
    }
    return $treatment_lists;
}

function get_existing_room_list() {
    $room_lists = array();
    $result = mysql_query("SELECT id,room_name FROM room");
    while ($row = mysql_fetch_array($result)) {
        $room_lists[$row['id']] = $row['room_name'];
    }
    return $room_lists;
}

function check_column_title_order($user_input_columns) {
    $column_array = array('customer_firstname', 'customer_lastname', 'customer_email',
        'customer_phone', 'doctors_email', 'service_name', 'app_start_time',
        'app_end_time', 'app_date', 'app_reminder', 'room_name');
    $is_valid = true;
    for ($i = 0; $i < count($column_array); $i++) {
        $defined = strtolower(trim($column_array[$i]));
        $user_request = strtolower(trim($user_input_columns[$i]));
        $is_equal = strcmp($defined, $user_request);
        if ($is_equal) {
            //echo $defined.' and '.$user_request;
            $is_valid = false;
        }
    }
    return $is_valid;
}

function is_csv_file($name) {
    $ext = end((explode(".", $name))); # extra () to prevent notice
    if (strtolower($ext) == 'csv')
        return true;
    else
        return false;
}

//view upload form
