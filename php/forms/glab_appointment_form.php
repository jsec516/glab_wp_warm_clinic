<?php
require_once plugin_dir_path(__FILE__) . '../helper/glab_html_helper.php';

class glab_appointment_form {

    static function add($args) {
        extract($args);
        $target_pattern = (isset($_POST['targetPattern'])) ? '1' : '0';
        $msg = '';
        if ($target_pattern == '1') {
            $msg .= '<table id="patternInfo">
                    <tr>
			<td>Title: </td><td>' . $patternInfo['event_title'] . '</td>
                    </tr>
                    <tr>
			<td>Start Time: </td><td>' . $patternInfo['start_time'] . '</td>
                    </tr>
                    <tr>
			<td>End Time: </td><td>' . $patternInfo['end_time'] . '</td>
                    </tr>
                    <tr>
			<td>Where: </td><td>' . $patternInfo['event_where'] . '</td>
                    </tr>
                    <tr>
			<td>Content: </td><td>' . $patternInfo['event_content'] . '</td>
                    </tr>
		</table>';
        }
        $msg .= '<form method="post"  name="frm1" class="add_appt" data-target-pattern="' . $target_pattern . '"  id="frm1" action="">
                    <style>
                    #app_table_form{
                        position:relative;
                    }
                    #add_app_table td{
                    padding-left:94px;
                    }
                    #add_app_table select{
                            width:250px;		
                    }
                    </style>
                   
		<div id="app_table_form"  style="margin-left: 13%; width:687px; margin-top: 50px;  border:10px solid #AAAAAA; padding:10px 86px 24px 0px;">
		<div style="width:750px;">
		<div style="float:left; margin-left:20px;">
		<h1 class="appointment_title">Appointment</h1>
		</div>
		<div style="float:right; margin-left:20px;">';
        $closeFunc = (isset($_REQUEST['targetPattern'])) ? 'closeAppFrm();' : 'closeDailyAppointment();';
        $saveFunc = (isset($_REQUEST['targetPattern'])) ? 'savePattern();' : 'appointmentSubmit1();';
        $hiddenHtml = (isset($_REQUEST['targetPattern'])) ? "<input type='hidden' id='patternId' name='patternId' value='" . $_REQUEST['targetPattern'] . "' />" : '';
        $msg.='<p class="submit" style="padding:0;">
		<input type="submit" name="Add_appointment_sub" id="sub_btn"  class="button-primary" value="Save Changes" />
		<input type="button" id="cl_btn" onClick="javascript:' . $closeFunc . 'return false;" name="cus_cancel_submit" class="button-primary" value="Cancel" />
		' . $hiddenHtml . '
		</p>
		</div>
		</div>
                
		<table id="add_app_table" style="margin-left:40px;" >
                    <tbody>
                        <tr>
                            <th style="padding-bottom:20px;">Appointment Type:</th>
                            <td style="padding-bottom:20px;">
				<select name="typeOfAppt" id="typeOfAppt" >
                                    <option value="1">Regular</option>
                                    <option value="2">Break of Practitioner</option>
                                    <option value="3">Block Time</option>
				</select>
                            </td>
                        </tr>
			<tr class="type1 type2">
                            <th style="padding-bottom:20px;">Practitioner:</th>
                            <td style="padding-bottom:20px;">
				<select style="width:250px;" class="app_practitioners1"  name="doc_name" id="doc_name">
				<option selected="selected" value="">Select Practitioner</option>';

        $prac_layer = new glab_practitioner_layer();
        $practitioners = $prac_layer->all(false);
        foreach ($practitioners as $row) {
            if (!empty($app_id) && $row['id'] == $selected_app_info['practitioner_id']) {
                $msg .='<option selected="selected" value="' . $row['id'] . '">' . $row['name'] . '</option>';
            } else {
                $msg .='<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
            }
        }

        $msg .='</select></td></tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">Treatment:</th>
		<td style="padding-bottom:20px;">
                    <select class="app_treatments1" style="width:250px;" id="statediv2"  name="statediv2" >
			<option value="">-- Select --</option>
                    </select>
		</td>
            </tr>
            <tr class="type1 type2 type3">
		<th style="padding-bottom:20px;">Date:</th>
                <td style="padding-bottom:20px;vertical-align:top;">
		<input type="text" id="datepick19" style="width:250px;"  class="samplePicker1" value="' . $date . '" name="datepick19" />
                </td>
            </tr>
            <tr id="shwtime" class="type1">
		<th style="padding-bottom:20px;">Time:</th>
		<td style="padding-bottom:20px;">
                    <select name="app_hours1" id="a_hour_chk" class="app_hour1" name="a_hour_chk"  >
			<option value="">-----Select Time-----</option>
                    </select>
		</td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">Reminder Type:</th>
		<td style="padding-bottom:20px;">
                    <select name="app_reminder1" id="app_reminder1">
			<option value="1">Call</option>
			<option value="2">Email</option>
                    </select>
		</td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">User Type:</th>
                <td style="padding-bottom:20px;">
                    <select name="usrval" id="usrval" >
                        <option value="2">Registered User</option>
			<option value="1">New User</option>
                    </select>
                </td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">First Name:</th>
		<td style="padding-bottom:20px;">
                    <input type="hidden" id="user_type" name="user_type" value="2" />
                    <input type="hidden" class="userId" id="user_id" name="user_id" value="" />
                    <input type="text"  id="app_first_name1" name="app_first_name" class="onkeyFname firstName"  style="width:250px;" autocomplete="off" /><div id="fA" class="ac_results firstAutoComplete" style="left: 242px !important;position: absolute;top: 455px  !important; width: 245px;visibility:hidden;"><ul style="max-height: 180px; overflow: auto;"></ul></div>
		</td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">Last Name:</th>
		<td style="padding-bottom:20px;"><input type="text" value="" autocomplete="off" id="app_last_name2"  name="app_last_name" class="lastName"  style="width:243px;" ><div id="lA" style=" left: 242px !important; position: absolute; top: 509px !important; width: 245px;visibility:hidden;"  class="ac_results lastAutoComplete"  ><ul style="max-height: 180px; overflow: auto;"></ul></div>
		</td>
            </tr>
            <tr id="uname1" style="display:none;" class="type1 user-elem1">
		<th style="padding-bottom:20px;">User Name:</th>
		<td style="padding-bottom:20px;"> <input type="text" id="cus_username1" name="cus_username" class="" style="width:250px;" autocomplete="off" /></td>
		</tr>
            <tr id="pwd12" style="display:none;" class="type1 user-elem1">
		<th style="padding-bottom:20px;">Password:</th>
		<td style="padding-bottom:20px;"> <input type="password" id="cus_password12" name="cus_password" class="" style="width:240px;" autocomplete="off" /></td>
            </tr>
            <tr id="pwd11" style="display:none;" class="type1 user-elem1">
                <th style="padding-bottom:20px;">Confirm Password:</th>
		<td style="padding-bottom:20px;"> <input type="password" id="cus_password11" name="cus_password1" class="" style="width:240px;" autocomplete="off" />
                </td>
            </tr>
            <tr id="email1" style="display:none;" class="type1 user-elem1">
		<th style="padding-bottom:20px;">E-mail:</th>
		<td style="padding-bottom:20px;"> <input type="text" id="cus_email1" name="cus_email" class="email" style="width:250px;" autocomplete="off" /></td>
                    <div id="eA" style=" left: 531px !important;  position: absolute !important;  top: 780px !important; width: 245px !important; visibility:hidden;" class="ac_results emailAutoComplete1"><ul style="max-height: 180px; overflow: auto;"></ul></div>
		</tr>
                <tr id="phone1" style="display:none;" class="type1 user-elem1">
                    <th style="padding-bottom:20px;">Phone:</th>
                    <td style="padding-bottom:20px;"> <input type="text" id="cus_phone1" name="cus_phone" class="" style="width:250px;" autocomplete="off" /></td>
		</tr>
		<tr id="break_from"  style="display:none;" class="type2 type3">
                    <th style="padding-bottom:20px;">Break From:</th>
                    <td style="padding-bottom:20px;">
			<select id="break_st_box" name="break_st_box">
                            <option value="">--select time--</option>' .
                self::getClinicHours($month, $day, $year)
                . '</select>
                    </td>
		</tr>
                <tr id="break_to" style="display:none;" class="type2 type3">
                    <th style="padding-bottom:20px;">Break To:</th>
                    <td style="padding-bottom:20px;">
			<select id="break_to_box" name="break_to_box">
                            <option value="">-- select time --</option>
			</select>
                    </td>
		</tr>';
        if (self::is_empty_day($year . '-' . $month . '-' . $day)) {
            $msg.='<tr id="mark_day_off" class="type2 type3" style="display:none;">
                        <th>&nbsp;</th>
			<td style="padding-bottom:20px;">
                            <input type="checkbox" value="off" name="mark_as_off" id="mark_as_off" /> Mark the day as off
                            <input type="hidden" value="" id="off_flag" name="off_flag" /> 
			</td>
                    </tr>';
        }
        $msg.='</tbody>
	</table>';
        $msg.='</div>
	</form>';

        echo $msg;
    }

    static function getClinicHours($month, $day, $year) {

        require_once dirname(dirname(__FILE__)) . '/helper/glab_slot_helper.php';
        $db_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        $day = strtolower(date('D', mktime(0, 0, 0, $month, $day, $year)));

        $clinic_layer = new glab_clinic_layer();
        $exp_day_slot = $clinic_layer->get_specific_day_slot($day);
        ;
        $exp_day_arr = explode('-', $exp_day_slot);
        $option = "";
        if (!glab_slot_helper::is_weekly_off_day($exp_day_arr[1])) {
            $start_arr = explode(':', $exp_day_arr[0]);
            $start_hour = $start_arr[0];
            $end_arr = explode(':', $exp_day_arr[1]);
            $end_hour = $end_arr[0];
            $tmp_hour = $start_hour;
            while ($tmp_hour < $end_hour) {
                $rep_string = $tmp_hour . ':00 AM';
                if ($tmp_hour > 12) {
                    if ($tmp_hour - 12 <= 9)
                        $rep_string = '0' . ($tmp_hour - 12) . ':00 PM';
                    else
                        $rep_string = ($tmp_hour - 12) . ':00 PM';
                }elseif ($tmp_hour == 12) {
                    $rep_string = $tmp_hour . ':00 PM';
                } elseif ($tmp_hour <= 9 && $tmp_hour > 6) {
                    $rep_string = '0' . $tmp_hour . ':00 AM';
                }
                $option.="<option value='{$tmp_hour}:00'>{$rep_string}</option>";
                $tmp_hour++;
            }
        }
        return $option;
    }

    static function is_empty_day($date) {
        $appt_layer = new glab_appointment_layer();
        $appointments = $appt_layer->get_specific_day_appt($date);
        $numRowsHours = count($appointments);
        if ($numRowsHours)
            return false;
        else
            return true;
    }

    static function edit($args) {
        extract($args);
        $msg='';
        $msg .= '<form method="post"  name="frm1" class="add_appt" data-target-pattern=""  id="frm1" action="">
                    <style>
                    #app_table_form{
                        position:relative;
                    }
                    #add_app_table td{
                    padding-left:94px;
                    }
                    #add_app_table select{
                            width:250px;		
                    }
                    </style>
                   
		<div id="app_table_form"  style="margin-left: 13%; width:687px; margin-top: 50px;  border:10px solid #AAAAAA; padding:10px 86px 24px 0px;">
		<div style="width:750px;">
		<div style="float:left; margin-left:20px;">
		<h1 class="appointment_title">Edit Appointment</h1>
		</div>
		<div style="float:right; margin-left:20px;">';
        $msg.='<p class="submit" style="padding:0;">
		<input type="submit" name="Add_appointment_sub" id="sub_btn"  class="button-primary" value="Save Changes" />
		<input type="button" id="cl_btn" onClick="javascript:closeDailyAppointment(); return false;" name="cus_cancel_submit" class="button-primary" value="Cancel" />
		</p>
		</div>
		</div>
                
		<table id="add_app_table" style="margin-left:40px;" >
                    <tbody>
                        <tr>
                            <th style="padding-bottom:20px;">Appointment Type:</th>
                            <td style="padding-bottom:20px;">
				<select name="typeOfAppt" id="typeOfAppt" >
                                    <option value="1">Regular</option>
                                    <option value="2">Break of Practitioner</option>
                                    <option value="3">Block Time</option>
				</select>
                            </td>
                        </tr>
			<tr class="type1 type2">
                            <th style="padding-bottom:20px;">Practitioner:</th>
                            <td style="padding-bottom:20px;">
				<select style="width:250px;" class="app_practitioners1"  name="doc_name" id="doc_name">
				<option selected="selected" value="">Select Practitioner</option>';

        $prac_layer = new glab_practitioner_layer();
        $practitioners = $prac_layer->all();
        foreach ($practitioners as $row) {
            if (!empty($app_id) && $row['id'] == $selected_app_info['practitioner_id']) {
                $msg .='<option selected="selected" value="' . $row['id'] . '">' . $row['name'] . '</option>';
            } else {
                $msg .='<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
            }
        }

        $msg .='</select></td></tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">Treatment:</th>
		<td style="padding-bottom:20px;">
                    <select class="app_treatments1" style="width:250px;" id="statediv2"  name="statediv2" >
			' . glab_html_helper::get_services_option($selected_app_services, $selected_app_info['service_id']) . '
                    </select>
		</td>
            </tr>
            <tr class="type1 type2 type3">
		<th style="padding-bottom:20px;">Date:</th>
                <td style="padding-bottom:20px;vertical-align:top;">
		<input type="text" id="datepick19" style="width:250px;"  class="samplePicker1" value="' . $date . '" name="datepick19" />
                </td>
            </tr>
            <tr id="shwtime" class="type1">
		<th style="padding-bottom:20px;">Time:</th>
		<td style="padding-bottom:20px;">
                    <select name="app_hours1" id="a_hour_chk" class="app_hour1" name="a_hour_chk"  >
			' . $free_slots . '
                            <option value="' . date('g:ia', strtotime($selected_app_info['app_time'])) . '" selected >' . date('h:i A', strtotime($selected_app_info['app_time'])) . '</option>
                    </select>
		</td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">Reminder Type:</th>
		<td style="padding-bottom:20px;">
                    <select name="app_reminder1" id="app_reminder1">
			<option value="1" ' . (($selected_app_info['reminder_type'] == '2') ? 'checked' : '') . '>Call</option>
										<option value="2" ' . (($selected_app_info['reminder_type'] == '1') ? 'checked' : '') . '>Email</option>
                    </select>
		</td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">User Type:</th>
                <td style="padding-bottom:20px;">
                    <select name="usrval" id="usrval" >
                        <option value="2">Registered User</option>
			<option value="1">New User</option>
                    </select>
                </td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">First Name:</th>
		<td style="padding-bottom:20px;">
                    <input type="hidden" id="user_type" name="user_type" value="2" />
                    <input type="hidden" class="userId" id="user_id" name="user_id" value="' . $selected_app_info['user_id'] . '" />
                        <input type="hidden" id="app_id" name="app_id" value="' . $selected_app_info['id'] . '" />
                    <input type="text"  id="app_first_name1" name="app_first_name" class="onkeyFname firstName"  style="width:250px;" autocomplete="off" value="' . $selected_app_info['first_name'] . '"/><div id="fA" class="ac_results firstAutoComplete" style="left: 242px !important;position: absolute;top: 455px  !important; width: 245px;visibility:hidden;"><ul style="max-height: 180px; overflow: auto;"></ul></div>
		</td>
            </tr>
            <tr class="type1">
		<th style="padding-bottom:20px;">Last Name:</th>
		<td style="padding-bottom:20px;"><input  autocomplete="off" id="app_last_name2"  name="app_last_name" class="lastName"  value="' . $selected_app_info['last_name'] . '" style="width:243px;" ><div id="lA" style=" left: 242px !important; position: absolute; top: 509px !important; width: 245px;visibility:hidden;"  class="ac_results lastAutoComplete"  ><ul style="max-height: 180px; overflow: auto;"></ul></div>
		</td>
            </tr>
            <tr id="uname1" style="display:none;" class="type1 user-elem1">
		<th style="padding-bottom:20px;">User Name:</th>
		<td style="padding-bottom:20px;"> <input type="text" id="cus_username1" name="cus_username" class="" style="width:250px;" autocomplete="off" /></td>
		</tr>
            <tr id="pwd12" style="display:none;" class="type1 user-elem1">
		<th style="padding-bottom:20px;">Password:</th>
		<td style="padding-bottom:20px;"> <input type="password" id="cus_password12" name="cus_password" class="" style="width:240px;" autocomplete="off" /></td>
            </tr>
            <tr id="pwd11" style="display:none;" class="type1 user-elem1">
                <th style="padding-bottom:20px;">Confirm Password:</th>
		<td style="padding-bottom:20px;"> <input type="password" id="cus_password11" name="cus_password1" class="" style="width:240px;" autocomplete="off" />
                </td>
            </tr>
            <tr id="email1" style="display:none;" class="type1 user-elem1">
		<th style="padding-bottom:20px;">E-mail:</th>
		<td style="padding-bottom:20px;"> <input type="text" id="cus_email1" name="cus_email" class="email" style="width:250px;" autocomplete="off" /></td>
                    <div id="eA" style=" left: 531px !important;  position: absolute !important;  top: 780px !important; width: 245px !important; visibility:hidden;" class="ac_results emailAutoComplete1"><ul style="max-height: 180px; overflow: auto;"></ul></div>
		</tr>
                <tr id="phone1" style="display:none;" class="type1 user-elem1">
                    <th style="padding-bottom:20px;">Phone:</th>
                    <td style="padding-bottom:20px;"> <input type="text" id="cus_phone1" name="cus_phone" class="" style="width:250px;" autocomplete="off" /></td>
		</tr>
		<tr id="break_from"  style="display:none;" class="type2 type3">
                    <th style="padding-bottom:20px;">Break From:</th>
                    <td style="padding-bottom:20px;">
			<select id="break_st_box" name="break_st_box">
                            <option value="">--select time--</option>' .
                self::getClinicHours($month, $day, $year)
                . '</select>
                    </td>
		</tr>
                <tr id="break_to" style="display:none;" class="type2 type3">
                    <th style="padding-bottom:20px;">Break To:</th>
                    <td style="padding-bottom:20px;">
			<select id="break_to_box" name="break_to_box">
                            <option value="">-- select time --</option>
			</select>
                    </td>
		</tr>';
        if (self::is_empty_day($year . '-' . $month . '-' . $day)) {
            $msg.='<tr id="mark_day_off" class="type2 type3" style="display:none;">
                        <th>&nbsp;</th>
			<td style="padding-bottom:20px;">
                            <input type="checkbox" value="off" name="mark_as_off" id="mark_as_off" /> Mark the day as off
                            <input type="hidden" value="" id="off_flag" name="off_flag" /> 
			</td>
                    </tr>';
        }
        $msg.='</tbody>
	</table>';
        $msg.='</div>
	</form>';

        echo $msg;
    }

    static function toolTipInfo($args) {
        extract($args);
        $contact_with = ($row['reminder_type'] == '1') ? "Email" : "Phone";
        ?><table style="text-align:left; ">
            <tr><th>Patient</th></tr>
            <tr><td><?= $row['patient_name']; ?></td></tr>
            <tr><th>Practitioner</th></tr>
            <tr><td><?= $row['practitioner_name']; ?></td></tr>
            <tr><th>Treatments</th></tr>
            <tr><td><?= $row['treat_name']; ?></td></tr>
            <tr><th>Appointment Date</th></tr>
            <tr><td><?= $row['app_date']; ?></td></tr>
            <tr><th>Appointment Time</th></tr>
            <tr><td><?= $row['app_time']; ?></td></tr>
            <tr><th>Contact With</th></tr>
            <tr><td><?= $contact_with; ?></td></tr>
            <tr><th>Patient Comment</th></tr>
            <tr><td><?= $row['pt_comments']; ?></td></tr>
            <tr><th>Practitioner's Note</th></tr>
            <tr><td><?= $row['dr_comments']; ?></td></tr>

        </table>
        <?php
    }

    static function showAppDetails($args) {
        extract($args);
        ?>
        <style type="text/css">
            .tblDetails td{
                padding-top:10px;
            }
        </style>


        <div style="margin-top:70px; text-align:center; width:510px; height:501px; margin-left:235px; border:1px solid #aaaaaa;">
            <?php
            $app_date_parts = explode("-", $row['app_date']);

            echo $content = '<div><span  class="add_an_appointment" style="display:inline-block;padding:10px;background:#999999; -moz-border-radius: 12px 12px 12px 12px;-moz-border-radius: 12px;-khtml-border-radius:12px;-webkit-border-radius: 12px;border-radius: 12px; margin-left:-89px; margin-top:30px;"><a href="#" onclick="javascript:Appointment_view_slider12(\'' . $app_date_parts[2] . '\',\'' . $app_date_parts[1] . '\',\'' . $app_date_parts[0] . '\',\'' . $row['app_time'] . '\',' . $row['id'] . '); return false;" style="float:left; text-decoration:none;color:#ffffff;">Edit Appointment</a></span><span style="display:inline-block;padding:10px;background:#999999; -moz-border-radius: 12px 12px 12px 12px;-moz-border-radius: 12px;-khtml-border-radius:12px;-webkit-border-radius: 12px;border-radius: 12px; margin-left:10px; margin-top:30px;"><a href="#" onclick="javascript:delete_appointment(' . $row['id'] . '); return false;" style="float:left; text-decoration:none;color:#ffffff;">Cancel Appointment</a></span><div style="float:right;"><span class="close_tb" style="display:inline-block;padding:10px;background:#F80000; -moz-border-radius: 12px 12px 12px 12px;-moz-border-radius: 12px;-khtml-border-radius:12px;-webkit-border-radius: 12px;border-radius: 12px; margin-top:30px; margin-right:20px;"><a href="#" onclick="javascript:close_appDetails(); return false;" style="text-decoration:none;color:#ffffff;">Close</a></span></div></div>';
            ?>
            <table  style="width:100%;text-align:center; margin-top:20px; margin-bottom:26px;" class="tblDetails">
                <tr><td style="text-align:right;width:250px; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Patient</span></td><td style="text-align:left;"><?= $row['patient_name']; ?>&nbsp;</td></tr>
                <tr><td style="text-align:right;width:250px; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Practitioner</span></td><td style="text-align:left;"><?= $row['practitioner_name']; ?>&nbsp;</td></tr>
                <tr><td style="text-align:right;width:250px; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Treatments</span></td><td style="text-align:left;"><?= $row['treat_name']; ?>&nbsp;</td></tr>
                <tr><td style="text-align:right;width:250px; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Appointment Date</span></td><td style="text-align:left;"><?= $row['app_date']; ?>&nbsp;</td></tr>
                <tr><td style="text-align:right;width:250px; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Appointment Time</span></td><td style="text-align:left;"><?= $row['app_time']; ?>&nbsp;</td></tr>
                <tr><td style="text-align:right;width:250px; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Contact with</span></td><td style="text-align:left;"><?= ($row['reminder_type'] == '1') ? "Email" : "Phone"; ?>&nbsp;</td></tr>
                <tr><td style="text-align:right;width:250px; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Patient's Comment</span></td><td style="text-align:left;"><?= (trim($row['pt_comments']) != '') ? $row['pt_comments'] : 'N/A'; ?>&nbsp;</td></tr>
                <tr><td style="text-align:right;width:250px;vertical-align:top; padding:10px;"><span style="padding-right:50px; font-weight:bold;">Practitioner's Note</span></td><td style="text-align:left;"><?= (trim($row['dr_comments']) != '') ? $row['dr_comments'] : 'N/A'; ?></td></tr>

                <table>
                    </div>
                    <?php
                }

            }
            