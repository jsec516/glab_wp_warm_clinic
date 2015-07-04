<?php
require_once plugin_dir_path(__FILE__) . '../helper/glab_html_helper.php';

class glab_waiting_form {

    static function add($data) {
        extract($data);
        $settings = array(
            'media_buttons' => false,
            'textarea_name' => 'customer_description',
            'textarea_rows' => 10,
        );
        ?>
        <form method="post" id="frm_waiting" action="">
            <?php self::generate_customer_related_field($data); ?>
            <div id="scheduleForm" class="load-wait-schedule ">
                <p>Slot will be loaded based on practitioner selection</p>
                <div class="init-slot">
                    <?php self::generate_slot_fields($data); ?>
                </div>
            </div>

            <?php wp_nonce_field('wait_add', 'wait_add_nonce'); ?>
        </form>
        <?php
    }
    
    static function edit($data) {
    	extract($data);
    	$settings = array(
    			'media_buttons' => false,
    			'textarea_name' => 'customer_description',
    			'textarea_rows' => 10,
    	);
    	?>
            <form method="post" id="frm_waiting" action="">
                <?php self::generate_customer_related_field($data, true); ?>
                <div id="scheduleForm" class="load-wait-schedule " style="display: block;">
                        <?php self::generate_slot_fields($data, true, $current_slots); ?>
                    
                </div>
    
                <?php wp_nonce_field('wait_edit', 'wait_edit_nonce'); ?>
            </form>
            <?php
        }
        
        static function get_selected_hours($current_schedule, $position){
			if ($position == 'start')
				$selected = substr($current_schedule, 0, 2);
			else
				$selected = substr($current_schedule, 6, 2);
			return $selected;
		}
		
		static function get_selected_minutes($current_schedule, $position){
			if ($position == 'start')
				$selected = substr($current_schedule, 3, 2);
			else
				$selected = substr($current_schedule, 9, 2);
			return $selected;
		}

    static function generate_customer_related_field($data, $is_update = false) {
        extract($data);
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">User Type</th>
                <td>
                    <select id="user_type" name="cust_type" class="glab_wp_select large user-type">
                        <option value="1">New User</option>
                        <option value="2" <?php echo ($is_update)?"selected":'';?> >Registered User</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">First Name</th>
                <td><input id="first_name" type="text" autocomplete="off" name="first_name" placeholder="first name" required class="glab_wp_text large firstName" value="<?php echo ($is_update)?$info['first_name']:""; ?>" />
                    <div id="fA" class="ac_results firstAutoComplete" style="left: 113px !important;position: absolute;top: 194px  !important; width: 296px;visibility:hidden;"><ul style="max-height: 180px; overflow: auto;"></ul></div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Last Name</th>
                <td><input type="text" id="last_name" name="last_name" placeholder="last name" required class="glab_wp_text large" value="<?php echo ($is_update)?$info['last_name']:""; ?>" /></td>
            </tr>
            <tr valign="top" class="only-new-cus" style="<?php echo ($is_update)?'display:none;':''; ?>" >
                <th scope="row">Password</th>
                <td><input type="password" name="new_password" placeholder="password"  class="glab_wp_text large" /></td>
            </tr>
            <tr valign="top" class="only-new-cus" style="<?php echo ($is_update)?'display:none;':''; ?>">
                <th scope="row">Retype Password</th>
                <td><input type="password" name="retype_pass" placeholder="retype password"  class="glab_wp_text large" /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Email</th>
                <td>
                    <input type="text" id="email" name="email" placeholder="example@email.com" required class="glab_wp_text large" value="<?php echo ($is_update)?$info['email']:""; ?>" />
                    <input type="hidden" name="selected_user" value="<?php echo ($is_update)?$info['user_id']:''; ?>" id="selected_user" />
                </td>
            </tr>
            <tr valign="top" class="only-new-cus" style="<?php echo ($is_update)?'display:none;':''; ?>" >
                <th scope="row">Phone</th>
                <td><input type="text" id="phone" name="phone" placeholder=""  class="glab_wp_text large" value="<?php echo ($is_update)?$info['phone']:""; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Phone(Cell)</th>
                <td><input type="text" id="cell" name="phone_cell" placeholder=""  class="glab_wp_text large" value="<?php echo ($is_update)?$info['cell']:""; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Phone(Work)</th>
                <td><input type="text" id="work" name="phone_work" placeholder=""  class="glab_wp_text large" value="<?php echo ($is_update)?$info['work']:""; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Contact With</th>
                <td>
                    <select name="contact" class="glab_wp_select large">
                        <option value="1" <?php echo ($is_update && $info['contact_me']=='1')?'selected="selected"':"";?> >Email</option>
                        <option value="2" <?php echo ($is_update && $info['contact_me']=='2')?'selected="selected"':"";?> >Phone</option>
                        <option value="3" <?php echo ($is_update && $info['contact_me']=='3')?'selected="selected"':"";?> >Both</option>
                    </select>
                </td>
            </tr>
            <tr valign="top" class="only-new-cus" style="<?php echo ($is_update)?'display:none;':''; ?>" >
                <th scope="row">Primary Contact</th>
                <td>
                    <select name="primary_phone" class="glab_wp_select large">
                        <option value="PHONE" <?php echo ($is_update && $info['primary_phone']=='PHONE')?'selected="selected"':"";?> >Phone</option>
                        <option value="CELL" <?php echo ($is_update && $info['contact_me']=='CELL')?'selected="selected"':"";?> >Cell</option>
                        <option value="WORK" <?php echo ($is_update && $info['contact_me']=='WORK')?'selected="selected"':"";?> >Work</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Practitioner</th>
                <td>
                    <select name="wait_prac" class="glab_wp_select large wait-prac">
                        <option value="">Select</option>
                        
                        <?php echo ($is_update)?glab_html_helper::prac_options($practitioners, $info['practitioner_id']):glab_html_helper::prac_options($practitioners); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Service</th>
                <td>
                    <select name="service" class="glab_wp_select large services">
                        <option value="">Select</option>
                        <?php if($is_update){?>
                        <?php foreach ($services as $service){?>
                        	<option value="<?php echo $service['service_id']; ?>" <?php echo ($service['service_id']==$info['service_id'])?'selected="selected"':''; ?> ><?php echo $service['service_name']; ?></option>
                        <?php }?>
                        <?php }?>
                    </select>
                </td>
            </tr>



        </table>
        <?php
    }

    static function generate_slot_fields($data, $is_update = false, $current_slots='') {
        extract($data);
        echo '<link type="text/css" rel="stylesheet" href="' . plugins_url('glab_clinic/assets/css/jquery.schedule.css') . '" />';
        ?>

        <table id="table_set_schedule">
            <tr>
                <th>From</th>
                <th>To</th>
                <th style="padding-left:20px;">Practitioner Schedule</th>
            </tr>
            <?php foreach ($schedule as $key => $value): ?>
                <?php if (isset($schedule[$key]['is_off_day']) && !$schedule[$key]['is_off_day']) { ?>
                    <tr>
                        <td>
                            <div class="day-start <?php echo $key ?>-start">
                                <input type="checkbox" name="days[]" value="<?php echo strtolower($key); ?>" <?php echo ($is_update && $info[$key])?'checked="checked"':''; ?> />
                                <label class="week-day"><?php echo ucfirst($key) . '. '; ?></label>
                                <select data-day="<?php echo $key ?>" data-matter="hour" data-target="<?php echo $key ?>_d_h" class="start" name="<?php echo $key ?>_s_h">
                                	<?php
                                	if($is_update) 
                                		echo glab_html_helper::get_sliced_schedule($schedule[$key]['value'], 'start', self::get_selected_hours($info[strtolower($key)], 'start'));
                                	else 
                                		echo glab_html_helper::get_sliced_schedule($schedule[$key]['value'], 'start');
                                	?>
                                		
                                </select>
                                <select data-day="<?php echo $key ?>" data-matter="minute" class="" name="<?php echo $key ?>_s_m">
                                	<?php
                                	if($is_update) 
                                		echo glab_html_helper::get_schedule_minutes($schedule[$key]['value'], 'start', self::get_selected_minutes($schedule[$key]['value'], $info[strtolower($key)], 'start'));
                                	else 
                                		echo glab_html_helper::get_schedule_minutes($schedule[$key]['value'], 'start');
                                	?>
                                </select>
                                <label id="<?php echo $key ?>_s_meridian" class="meridian">
                                    <?php
                                    if($is_update)
                                    	echo self::get_meridian($schedule[$key]['value'], $info[strtolower($key)], 'start');
                                    else
                                    	echo isset($schedule[$key]['value']) ? glab_html_helper::get_meridian($schedule[$key]['value'], 'start') : 'AM'; ?>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="<?php echo $key ?>-end">
                                <select data-day="<?php echo $key ?>" data-matter="hour" class="end" id="<?php echo $key ?>_d_h" name="<?php echo $key ?>_d_h">
                                    <?php //echo glab_html_helper::get_sliced_schedule($schedule[$key]['value'], 'end', self::get_selected_hours($info[strtolower($key)], 'end')); ?>
                                    <?php
                                	if($is_update) 
                                		echo glab_html_helper::get_sliced_schedule($schedule[$key]['value'], 'end', self::get_selected_hours($info[strtolower($key)], 'end'));
                                	else 
                                		echo glab_html_helper::get_sliced_schedule($schedule[$key]['value'], 'end');
                                	?>
                                </select>
                                <select data-day="<?php echo $key ?>" data-matter="minute" class="" id="<?php echo $key ?>_d_m" name="<?php echo $key ?>_d_m">
                                    <?php //echo glab_html_helper::get_schedule_minutes($schedule[$key]['value'], 'end'); ?>
                                    <?php
                                	if($is_update) 
                                		echo glab_html_helper::get_schedule_minutes($schedule[$key]['value'], 'end', self::get_selected_minutes($info[strtolower($key)], 'end'));
                                	else 
                                		echo glab_html_helper::get_schedule_minutes($schedule[$key]['value'], 'end');
                                	?>
                                </select>
                                <label id="<?php echo $key ?>_d_meridian" class="meridian">
                                    <?php
                                    if($is_update)
                                    	echo self::get_meridian($schedule[$key]['value'], $info[strtolower($key)], 'end');
                                    else
                                    	echo isset($schedule[$key]['value']) ? glab_html_helper::get_meridian($schedule[$key]['value'], 'end') : 'AM'; ?>
                                </label>

                            </div>
                        </td>
                        <td style="padding-left:20px;">
                            <?php echo glab_convert_helper::user_readable_prac_schedule($schedule[$key]['value']); ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php endforeach; ?>
            <tr valign="top">
                <th scope="row">
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Submit">

            </p>
        </th>
        <td>&nbsp;</td>
        </tr>
        </table>


        <?php
    }
    
    static function get_meridian($default_schedule, $current_schedule, $position){
		
		$meridian = '';
		$schedule='';
		$end_offset='';
		if(strlen($current_schedule)==11){
			$schedule = $current_schedule;
			$end_offset = 6;
		}else{
			$schedule = $default_schedule;
			$end_offset = 10;
		}
		if ($position == 'start')
			$selected = substr($schedule, 0, 2);
		else
			$selected = substr($schedule, $end_offset, 2);
		
		if ($selected >= 12)
			$meridian = "PM";
		else
			$meridian = "AM";
		
		return $meridian;
	}

    static function load_schedule($args) {
        self::generate_slot_fields($args);
    }

    

    static function confirmation_form($args) {
        extract($args);
        ?>
        <form method="post" name="frm1" id="frm1" action="">

            <style>
                #add_app_table td{
                    padding-left:94px;
                }
            </style>
            <div id="app_table_form" style="margin-left: 13%; width:687px; margin-top: 50px;  border:1px solid #AAAAAA; padding:10px 86px 24px 0px;">
                <div style="width:750px;">
                    <div style="float:left; margin-left:20px;">
                        <h1 class="appointment_title">Appointment</h1>
                    </div>
                    <div style="float:right; margin-left:20px;">	
                        <p class="submit" style="padding:0;">
                            <input type="button" name="Add_appointment_sub" class="button-primary" value="Finalize" onclick="finalize_appointment();">
                            <input type="button" name="Add_appointment_sub" class="button-primary" value="Book Appointment" onclick="Appointment_view_slider12('<?= $_REQUEST['selected_day_bunch']; ?>', '<?= $_REQUEST['selected_month']; ?>', '<?= $_REQUEST['selected_year']; ?>', '<?= $_REQUEST['time']; ?>');">
                            <input type="reset" onclick="javascript:closeDailyAppointment();
                                            return false;" name="cus_cancel_submit" class="button-primary" value="Cancel">
                        </p>
                    </div>
                </div>
                <div id="eA" style=" left: 531px !important;  position: absolute !important;  top: 780px !important; width: 245px !important; visibility:hidden;" class="ac_results emailAutoComplete1"><ul style="max-height: 180px; overflow: auto;"></ul></div><table id="add_app_table" style="margin-left:40px;">
                    <tbody>
                        <tr>
                            <th style="padding-bottom:20px;">Select Patient</th>
                            <td style="padding-bottom:20px;">
                                <select style="width:250px;" class="app_practitioners1" onchange="grabPatientInfo(this.value)" name=""><?= self::getPatientList($validAppArr); ?></select>
                                <input type="hidden" value="" id="w_id"/><input type="hidden" value="" id="w_selected_patient"/><input type="hidden" value="" id="w_selected_doctor"/><input type="hidden" value="" id="w_selected_treatment"/><input type="hidden" value="" id="w_app_reminder"/><input type="hidden" value="" id="w_selected_firstname"/><input type="hidden" value="" id="w_selected_lastname"/>
                                <input type="hidden" value="<?= $_POST["selected_day_bunch"]; ?>" id="w_day"/><input type="hidden" value="<?= $_POST["selected_month"]; ?>" id="w_month"/><input type="hidden" value="<?= $_POST["selected_year"]; ?>" id="w_year"/><input type="hidden" value="<?= $_POST["time"]; ?>" id="w_time"/>
                            </td></tr>

                        <tr>
                            <th style="padding-bottom:20px;">Practitioner</th>
                            <td style="padding-bottom:20px;" id="w_paractitioner">

                            </td>
                        </tr>

                        <tr>
                            <th style="padding-bottom:20px;">Treatments</th>
                            <td style="padding-bottom:20px;" id="w_treatments">

                            </td>
                        </tr>
                        <tr>
                            <th style="padding-bottom:20px;">Contact with</th>
                            <td style="padding-bottom:20px;" id="w_contact_with">

                            </td>
                        </tr>
                        <tr>
                            <th style="padding-bottom:20px;">Available Appointment Time</th>
                            <td style="padding-bottom:20px;" >
                                <input type="hidden" id="expected_week_day" value="<?php echo $expected_week_day; ?>" />
                                <select name="appointment_time" id="appointment_time" ><option>--select--</option></select>

                            </td>
                        </tr>	
                        <tr>
                            <td style="padding-bottom:20px;">&nbsp;</td>
                            <td style="padding-bottom:20px;">
                        </tr>

                    </tbody>
                </table>
            </div>
        </form>
        <?php
    }

    static function getPatientList($appt_array) {
        $options = '<option value="">Select Patient</option>';
        foreach ($appt_array as $row) {
            $options.='<option value="' . $row['id'] . '">' . $row['patient_name'] . '</option>';
        }
        return $options;
    }

}
