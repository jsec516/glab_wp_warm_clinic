<?php
require_once plugin_dir_path(__FILE__) . '../helper/glab_html_helper.php';

class glab_reminder_form {

    static function add($data) {
        ?>
        <form method="post" id="reminderTypeForm" enctype= "multipart/form-data" action="">
            <?php
            if ($data['load_type'] == 'email') {
                self::add_email_reminder_form($data);
            } else {
                self::add_call_reminder_form($data);
            }
            ?>
        </form>
        <?php
    }

    static function edit($data) {
        ?>
        <form method="post" id="reminderTypeForm" enctype= "multipart/form-data" action="">
            <?php
            if ($data['load_type'] == 'email') {
                self::edit_email_reminder_form($data);
            } else {
                self::edit_call_reminder_form($data);
            }
            ?>
        </form>
        <?php
    }

    static function add_email_reminder_form($args) {
        extract($args);
        $settings = array(
            'media_buttons' => false,
            'textarea_name' => 'email[email_content]',
            'textarea_rows' => 10,
        );
        ?>
        <table id="tbl-email-reminder" class="form-table th-medium reminder-table">
            <tr valign="top">
                <th scope="row">Select Practitioner <span class="required">*</span></th>
                <td>
                    <select id="practitioner" name="email[practitioner_id]" class="glab_wp_select large">
                        <?php echo glab_html_helper::prac_options($practitioners); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Select Services <span class="required">*</span></th>
                <td>
                    <select id="service" name="service_ids[]" multiple="multiple" class="glab_wp_select large">
                        <?php echo glab_html_helper::prac_service_option($services); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Subject <span class="required">*</span></th>
                <td>
                    <input type="text" name="email[subject]" class="glab_wp_text large" placeholder="subject of reminder" required="required" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Body Of Email <span class="required">*</span></th>
                <td>
                    <?php wp_editor('', 'email_content', $settings); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" style="padding-top:10px;">Send Reminder <span class="required">*</span></th>
                <td style="padding-top:10px;">
                    <select class="glab_wp_text large" name="email[reminder_day]"><?php echo glab_html_helper::reminder_day_option(); ?></select> Day Before The Appointment
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Attach File</th>
                <td>
                    <input type="file" name="attach_url" id="att_file" /> <a href="#" id="cancel_upload" >Cancel</a>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                <input type="hidden" name="reminder_type" value="1" />
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Submit"></th>
                <td>&nbsp;</td>
            </tr>
        </table>
        <?php
        wp_nonce_field('er_add', 'er_add_nonce');
    }

    static function edit_email_reminder_form($args) {
        extract($args);
        $settings = array(
            'media_buttons' => false,
            'textarea_name' => 'email[email_content]',
            'textarea_rows' => 10,
        );
        ?>
        <table id="tbl-email-reminder" class="form-table th-medium reminder-table">
            <tr valign="top">
                <th scope="row">Select Practitioner</th>
                <td>
                    <select id="practitioner" name="email[practitioner_id]" class="glab_wp_select large">
                        <?php echo glab_html_helper::prac_options($practitioners, $info['practitioner_id']); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Select Services</th>
                <td>
                    <select id="service" name="service_ids[]" multiple="multiple" class="glab_wp_select large">
                        <?php
                        $service_ids_array = explode(',', $info['service_ids']);
                        $selected_services = array();
                        foreach ($service_ids_array as $value) {
                            $tmp_array = array('service_id' => $value);
                            array_push($selected_services, $tmp_array);
                        }
                        echo glab_html_helper::prac_service_option($services, $selected_services);
                        ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Subject</th>
                <td>
                    <input type="text" name="email[subject]" class="glab_wp_text large" placeholder="subject of reminder" value="<?php echo $info['subject']; ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Body Of Email</th>
                <td>
                    <?php wp_editor($info['email_content'], 'email_content', $settings); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" style="padding-top:10px;">Send Reminder</th>
                <td style="padding-top:10px;">
                    <select class="glab_wp_text large" name="email[reminder_day]"><?php echo glab_html_helper::reminder_day_option($info['reminder_day']); ?></select> Day Before The Appointment
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Attach File</th>
                <td>                	<?php $file_exist = false;					if($info['attach_file'] && glab_html_helper::url_exists($info['attach_file'])) {						$file_exist = true; 					?>					<a  href="<?php echo $info['attach_file'];?>"><?php echo glab_html_helper::get_base_file_name($info['attach_file']);?></a>&nbsp;|&nbsp; <a class="delete_file" data-url="<?php echo $info['attach_file']; ?>" href="#">Remove</a>					<?php } ?>
                    <div style="<?=($file_exist)?'display:none;':''; ?>" >                    	<input type="file" name="attach_url" /> <a href="#">Cancel</a>					</div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <input type="hidden" name="reminder_type" value="1" />
                    <input type="hidden" name="er_id" value="<?php echo $info['id']; ?>" />
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Submit"></th>
                <td>&nbsp;</td>
            </tr>
        </table>
        <?php
        wp_nonce_field('er_edit', 'er_edit_nonce');
    }

    static function add_call_reminder_form($args) {
        extract($args);
        ?>
        <div style="display:none;" id="file_names">
            <input type="hidden" id="lineNumber" name="lineNumber" value="0" />
            <input type="hidden" id="fullnameNumber" name="fullnameNumber" value="0"/>
            <input type="hidden" id="dateNumber" name="dateNumber" value="0"/>
            <input type="hidden" id="fileNumber" name="fileNumber" value="0"/>
            <input type="hidden" id="pauseNumber" name="pauseNumber" value="0"/>
            <input type="hidden" id="5pauseNumber" name="5pauseNumber" value="0"/>
            <input type="hidden"  id="saying_text_msg" name="saying_text_msg" value="0"/>
            <input type="hidden" id="formatOrder" name="formatOrder" value=""/>
        </div>
        <table id="tbl-call-reminder" class="form-table th-medium reminder-table" >
            <tr valign="top">
                <th scope="row">Select Practitioner <span class="required">*</span></th>
                <td>
                    <select name="call[practitioner]" id="prac_option" class="glab_wp_select large">
                        <?php echo glab_html_helper::prac_options($practitioners); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Select Services <span class="required">*</span></th>
                <td>
                    <select name="services[]" id="treat_ids" multiple="multiple" class="glab_wp_select large">
                        <?php echo glab_html_helper::prac_service_option($services); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">                <th scope="row">Reminder Option <span class="required">*</span></th>                <td>                    <div class="reminder-options-container">                    	<div class="attach_nd_container">                        	<div><button data-event_type="fullname" id="attach_name" name="attach_name" class="button-secondary elem-event" title="insert full name of the patient">full name</button></div>                                <div><button data-event_type="date" id="attach_time" name="attach_time" class="button-secondary elem-event" title="insert time and date of the appointment">time and date</button></div>                        </div>                        <div class="attach_lf_container">                            <div><input type="button"  value="      attach file to play     "  id="att_file_play" name="att_file_play" class="button-secondary " /></div>                            <div><input type="button" value="    attach a line of text   " data-event_type="line" id="attach_line" name="attach_line" class="button-secondary elem-event" /></div>                        </div>                        <div class="attach_pause_container">                            <div><button data-event_type="1_sec_pause" id="pause_name" name="pause_name" class="button-secondary elem-event" title="insert 1 sec pause" >1 sec pause</button></div>                            <div><button data-event_type="half_sec_pause" id="5_pause_name" name="5_pause_name" class="button-secondary elem-event" title="insert 0.5 sec pause"> 0.5 sec pause</button></div>                        </div>                    </div>                </td>            </tr>            <tr valign="top">            	<th scope="row">Voice</th>            	<td><select id="voice_type" name="call[voice_type]"><option value="man">male</option><option value="woman">female</option></select></td>            </tr>            <tr valign="top" class="call-content-container">                <th scope="row">Call Content</th>                <td>                    <div  class="call-content">                        <table id="call_content">                        </table>                    </div>                </td>            </tr>
           
            <tr>            	<th scope="row">Test</th>            	<td>            		<div><input type="text" placeholder="enter number" id="number_text" name="number_text" value="" class="call_box"></div>            		<div><input type="button" value="Make A Test Call" id="make_call" name="make_call" class="button-secondary"></div>            	</td>            </tr>	 		<tr valign="top">
	            <th scope="row">Send Reminder <span class="required">*</span></th>	            <td>	                <select class="glab_wp_text large" name="call[day]"><?php echo glab_html_helper::reminder_day_option(); ?></select> Day Before The Appointment	            </td>	        </tr>

        <tr valign="top">
            <th scope="row">
                <input type="hidden" name="reminder_type" value="2" />
                <input type="submit" name="submit" id="submit" class="button button-primary call-submit-btn" value="Submit"></th>
            <td>&nbsp;</td>
        </tr>
        </table>
        <?php
        wp_nonce_field('cr_add', 'cr_add_nonce');
    }

    static function edit_call_reminder_form($args) {
        extract($args);
        $order_array = explode(',', $info['call_element_format']);
        $format_array = explode('*-', $info['call_format']);
        $elem_infos = self::get_elem_infos($order_array, $format_array);
        ?>
        <div style="display:none;" id="file_names">
            <?php echo $elem_infos['hidden_file_content']; ?>
            <input type="hidden" id="lineNumber" name="lineNumber" value="<?= $elem_infos['lineNumber']; ?>" />
            <input type="hidden" id="fullnameNumber" name="fullnameNumber" value="<?= $elem_infos['nameNumber']; ?>"/>
            <input type="hidden" id="dateNumber" name="dateNumber" value="<?= $elem_infos['dateNumber']; ?>"/>
            <input type="hidden" id="fileNumber" name="fileNumber" value="<?= $elem_infos['fileNumber']; ?>"/>
            <input type="hidden" id="pauseNumber" name="pauseNumber" value="<?= $elem_infos['pauseNumber']; ?>"/>
            <input type="hidden" id="5pauseNumber" name="5pauseNumber" value="<?= $elem_infos['pause5Number']; ?>"/>
            <input type="hidden"  id="saying_text_msg" name="saying_text_msg" value=""/>
            <input type="hidden" id="formatOrder" name="formatOrder" value="<?= $info['call_element_format']; ?>"/>
        </div>
        <table id="tbl-call-reminder" class="form-table th-medium reminder-table" >
            <tr valign="top">
                <th scope="row">Select Practitioner</th>
                <td>
                    <select name="call[practitioner]" id="prac_option" class="glab_wp_select large">
                        <?php echo glab_html_helper::prac_options($practitioners, $info['practitioner_id']); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Select Services</th>
                <td>
                    <select name="services[]" id="treat_ids" multiple="multiple" class="glab_wp_select large">
                        <?php 
                        $service_ids_array = explode(',', $info['service_ids']);
                        $selected_services = array();
                        foreach ($service_ids_array as $value) {
                            $tmp_array = array('service_id' => $value);
                            array_push($selected_services, $tmp_array);
                        }
                        echo glab_html_helper::prac_service_option($services, $selected_services); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Reminder Option</th>
                <td>
                    <div class="reminder-options-container">
                        <div class="attach_file_container">
                            <span style="display:block;"><input type="button"  value="      attach file to play     "  id="att_file_play" name="att_file_play" class="button-secondary " /></span><br/>
                            <span style="display:block;text-align:right;overflow:hidden;"><input type="file" value="choose file" id="att_file" name="att_file" /></span><br/>
                            <input type="hidden" name="upload_call_attachment" value="true"/>
                            <span style="display:block;"><input type="button" value="cancel upload" data-event_type="cancel_upload" id="cancel_upload" name="cancel_upload" class="button-secondary elem-event" /></span><br/>
                        </div>
                        <div class="attach_line_container">
                            <span style="display:block;"><input type="button" value="    attach a line of text   " data-event_type="line" id="attach_line" name="attach_line" class="button-secondary elem-event" /></span><br/>
                            <span style="display:block;text-align:right;height:28px;">&nbsp;</span><br/>
                            <span style="display:block;">choose voice : <select id="voice_type" name="call[voice_type]"><option value="man">male</option><option value="woman">female</option></select></span><br/>
                        </div>
                        <div class="attach_pause1_container">
                            <span style="display:block;"><button data-event_type="1_sec_pause" id="pause_name" name="pause_name" class="button-secondary elem-event">insert 1 sec pause</button></span><br/>
                            <span style="display:block;text-align:right;">&nbsp;</span><br/>
                            <span style="display:block;"><button data-event_type="fullname" id="attach_name" name="attach_name" class="button-secondary elem-event">insert full name<br> of the patient</button></span><br/>
                        </div>
                        <div class="attach_pause2_container">
                            <span style="display:block;"><button data-event_type="half_sec_pause" id="5_pause_name" name="5_pause_name" class="button-secondary elem-event">insert 0.5 sec pause</button></span><br/>
                            <span style="display:block;text-align:right;">&nbsp;</span><br/>
                            <span style="display:block;"><button data-event_type="date" id="attach_time" name="attach_time" class="button-secondary elem-event">insert time and date<br> of the appointment</button></span><br/>
                        </div>
                        <span>&nbsp;</span>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Call Content</th>
                <td>
                    <div  class="call-content">
                        <table id="call_content">
                            <?php echo $elem_infos['format_content'];?>    
                        </table>
                    </div>
                </td>
            </tr>
            <tr valign="top">
            <tr><td style="padding: 0 10px 0 20px;text-align: right;width: 145px;"><input type="button" value="Make A Test Call" id="make_call" name="make_call" class="button-secondary"></td><td><input type="text" id="number_text" name="number_text" value="" class="call_box"></td></tr>
            <th scope="row" style="padding-top:10px;">Send Reminder</th>
            <td style="padding-top:10px;">
                <select class="glab_wp_text large" name="call[day]"><?php echo glab_html_helper::reminder_day_option($info['reminder_day']); ?></select> Day Before The Appointment
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <input type="hidden" name="reminder_type" value="2" />
                <input type="hidden" name="cr_id" value="<?php echo $info['id']; ?>" />
                <input type="submit" name="submit" id="submit" class="button button-primary call-submit-btn" value="Submit"></th>
            <td>&nbsp;</td>
        </tr>
        </table>
        <?php
        wp_nonce_field('cr_edit', 'cr_edit_nonce');
    }

    static function get_elem_infos($order_array, $format_array) {
        $elem_infos = array();
        $base_url = plugins_url('glab_clinic/assets');
        $lineNumber = 0;
        $fileNumber = 0;
        $nameNumber = 0;
        $dateNumber = 0;
        $pauseNumber = 0;
        $pause5Number = 0;
        $elem_infos = array();
        $format_content = '';
        $hidden_file_content = '';
        $inc = 0;
        foreach ($order_array as $element) {
            if (substr_count($element, 'line_') > 0) {
                $lineNumber++;
                $lineValue = ($format_array[$inc]) ? trim($format_array[$inc]) : 'this message is for';
                $format_content.='<tr id="line_' . $lineNumber . '">'
                        . '<td><div style="position:relative;">'
                        . '<input type="text" style="padding-right:30px;" id="msg_for_text_' . $lineNumber . '" name="format_text[]" value="' . $lineValue . '" class="call_box" onfocus="if (this.value == \'this message is for\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'this message is for\';}">'
                        . '<div class="remove-elem" data-target_type="regular" data-target_id="line_' . $lineNumber . '" id="close_for_msg" style="margin-left:-25px;width:20px;display:inline;cursor:pointer;">'
                        . '<img style="margin-bottom:-3px;" alt="x" src="' . $base_url . '/images/cancel.png"></div></div></td></tr>';
            } else if (substr_count($element, 'fullname_') > 0) {
                $nameNumber++;
                $nameValue = ($format_array[$inc]) ? trim($format_array[$inc]) : '&lt;full name&gt;';
                $format_content.='<tr id="fullname_' . $nameNumber . '"><td><div style="position:relative;">'
                        . '<input type="text" style="padding-right:30px;" id="name_text_' . $nameNumber . '" name="format_text[]" value="' . $nameValue . '" class="call_box" onfocus="if (this.value == \'&lt;full name&gt;\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'&lt;full name&gt;\';}">'
                        . '<div class="remove-elem" data-target_type="regular" data-target_id="fullname_' . $nameNumber . '" id="close_name_text" style="margin-left:-25px;width:20px;display:inline;cursor:pointer;"><img style="margin-bottom:-3px;" alt="x" src="' . $base_url . '/images/cancel.png"></div></div></td></tr>';
            } else if (substr_count($element, 'date_') > 0) {
                $dateNumber++;
                $dateValue = ($format_array[$inc]) ? trim($format_array[$inc]) : '&lt;appointment date&gt;';
                $format_content.='<tr id="date_' . $dateNumber . '"><td><div style="position:relative;">'
                        . '<input type="text" style="padding-right:30px;" id="app_date_text_' . $dateNumber . '" name="format_text[]" value="' . $dateValue . '" class="call_box" onfocus="if (this.value == \'&lt;appointment date&gt;\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'&lt;appointment date&gt;\';}">'
                        . '<div class="remove-elem" data-target_type="regular" data-target_id="date_' . $dateNumber . '" id="close_app_date_text" style="margin-left:-25px;width:20px;display:inline;cursor:pointer;"><img style="margin-bottom:-3px;" alt="x" src="' . $base_url . '/images/cancel.png"></div></div></td></tr>';
            } else if (substr_count($element, 'file_') > 0) {
                $fileNumber++;
                $keywords = array('928afile_', '_endafile');
                $format_array[$inc] = str_replace($keywords, "", $format_array[$inc]);
                $fileValue = (trim($format_array[$inc])) ? trim($format_array[$inc]) : '&lt;file:acupuncture_appt_reminder.mp3&gt;';
                $format_content.='<tr id="file_' . $fileNumber . '"><td><div style="position:relative;"><input type="text" style="padding-right:30px;" id="file_text_' . $fileNumber . '" name="format_text[]" value="' . $fileValue . '" onfocus="if (this.value == \'&lt;file:acupuncture_appt_reminder.mp3&gt;\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'&lt;file:acupuncture_appt_reminder.mp3&gt;\';}" class="call_box">'
                        . '<div class="remove-elem" data-target_type="file" data-target_id="' . $fileNumber . '" id="close_file_text" style="margin-left:-25px;width:20px;display:inline;cursor:pointer;"><img style="margin-bottom:-3px;" alt="x" src="' . $base_url . '/images/cancel.png"></div></div></td></tr>';
                $hiddenFileValue = (trim($format_array[$inc])) ? trim($format_array[$inc]) : '';
                $hidden_file_content.='<input type="hidden" name="attached_files_name[]" value="' . $hiddenFileValue . '" id="attached_' . $fileNumber . '">';
            } else if (substr_count($element, 'pause_') > 0) {
                $pauseNumber++;
                $format_content.='<tr id="pause_' . $pauseNumber . '"><td><div style="position:relative;"><input type="text" class="call_box" onblur="if (this.value == \'\') {this.value = \'&lt;1 second pause&gt;\';}" onfocus="if (this.value == \'&lt;1 second pause&gt;\') {this.value = \'\';}" value="&lt;1 second pause&gt;" name="format_text[]" id="pause_text_' . $pauseNumber . '" style="padding-right:30px;">'
                        . '<div style="margin-left:-25px;width:20px;display:inline;cursor:pointer;" id="close_file_text" class="remove-elem" data-target_type="regular" data-target_id="pause_' . $pauseNumber . '" ><img src="' . $base_url . '/images/cancel.png" alt="x" style="margin-bottom:-3px;"></div></div></td></tr>';
            } else if (substr_count($element, 'pause5_') > 0) {
                $pause5Number++;
                $format_content.='<tr id="pause5_' . $pause5Number . '"><td><div style="position:relative;"><input type="text" style="padding-right:30px;" id="pause5_text_' . $pauseNumber . '" name="format_text[]" value="&lt;0.5 second pause&gt;" onfocus="if (this.value == \'&lt;0.5 second pause&gt;\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'&lt;0.5 second pause&gt;\';}" class="call_box"><div class="remove-elem" data-target_type="regular" data-target_id="pause5_' . $pause5Number . '" id="close_file_text" style="margin-left:-25px;width:20px;display:inline;cursor:pointer;"><img style="margin-bottom:-3px;" alt="x" src="' . $base_url . '/images/cancel.png"></div></div></td></tr>';
            }
            $inc++;
        }

        $elem_infos['format_content'] = $format_content;
        $elem_infos['hidden_file_content'] = $hidden_file_content;
        $elem_infos['lineNumber']=$lineNumber;
        $elem_infos['fileNumber']=$fileNumber;
        $elem_infos['nameNumber']=$nameNumber;
        $elem_infos['dateNumber']=$dateNumber;
        $elem_infos['pauseNumber']=$pauseNumber;
        $elem_infos['pause5Number']=$pause5Number;
        
        return $elem_infos;
    }

}
