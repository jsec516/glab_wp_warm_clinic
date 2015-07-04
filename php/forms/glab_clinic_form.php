<?php
require_once plugin_dir_path(__FILE__) . '../helper/glab_html_helper.php';

class glab_clinic_form {

    static function set_schedule($args) {
        extract($args);
        echo '<link type="text/css" rel="stylesheet" href="' . plugins_url('glab_clinic/assets/css/jquery.schedule.css') . '" />';
        ?>
        <form id="scheduleForm" method="post" action="">
            <table id="table_set_schedule">
                <tr>
                    <th>From</th>
                    <th>To</th>
                </tr>
                <?php foreach ($schedule as $key => $value): ?>
                    <tr>
                        <td>
                            <div class="day-start <?php echo $key ?>-start">
                                <label class="week-day"><?php echo ucfirst($key) . '. '; ?></label>
                                <select data-day="<?php echo $key ?>" data-matter="hour" data-target="<?php echo $key ?>_d_h" class="start" name="<?php echo $key ?>_s_h">
                                    <?php echo glab_html_helper::get_schedule_hours($schedule[$key]['value'], 'start'); ?>
                                </select>
                                <select data-day="<?php echo $key ?>" data-matter="minute" class="" name="<?php echo $key ?>_s_m">
                                    <?php echo glab_html_helper::get_schedule_minutes($schedule[$key]['value'], 'start'); ?>
                                </select>
                                <label id="<?php echo $key ?>_s_meridian" class="meridian">
                                    <?php echo isset($schedule[$key]['value']) ? glab_html_helper::get_meridian($schedule[$key]['value'], 'start') : 'AM'; ?>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="<?php echo $key ?>-end">
                                <select data-day="<?php echo $key ?>" data-matter="hour" class="end" id="<?php echo $key ?>_d_h" name="<?php echo $key ?>_d_h">
                                    <?php echo glab_html_helper::get_schedule_hours($schedule[$key]['value'], 'end'); ?>
                                </select>
                                <select data-day="<?php echo $key ?>" data-matter="minute" class="" id="<?php echo $key ?>_d_m" name="<?php echo $key ?>_d_m">
                                    <?php echo glab_html_helper::get_schedule_minutes($schedule[$key]['value'], 'end'); ?>
                                </select>
                                <label id="<?php echo $key ?>_d_meridian" class="meridian">
                                    <?php echo isset($schedule[$key]['value']) ? glab_html_helper::get_meridian($schedule[$key]['value'], 'end') : 'AM'; ?>
                                </label>
                                <label class="closed">
                                    <?php
                                    $checked = '';
                                    if (isset($schedule[$key]['is_off_day']) && $schedule[$key]['is_off_day'])
                                        $checked = "checked";
                                    ?>

                                    <input type="checkbox" value="OFF" name="<?php echo $key ?>_d_off"  <?php echo $checked; ?>/>CLOSED
                                </label>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr valign="top">
                    <th scope="row">
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Submit">
                    <input type="button" name="close"  id="closeBtn" class="button button-primary schedule-close" value="Close">
                </p>
                </th>
                <td>&nbsp;</td>
                </tr>
            </table>
            <?php wp_nonce_field('clinic_schedule', 'clinic_schedule_nonce'); ?>
        </form>
        <?php
    }

    static function display_schedule($args) {
        extract($args);
        ?>
        <table id="table_display_schedule">
            <tr>
                <th>Day</th>
                <th class="schedule-label">Schedule <a href="#" class="change-schedule">Change Schedule</a></th>
            </tr>
            <?php foreach ($schedule as $key => $value): ?>
                <tr valign="top">
                    <th class="day-label" scope="row"><?php echo $key; ?></th>
                    <td class="day-schedule"><?php echo isset($value['value']) ? glab_convert_helper::show_schedule_data($value['value']) : 'NOT SET'; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    static function display_rooms($rooms, $services, $active_services_id) {
        ?>
        <div id="daily_view_dashboard_widget" class="postbox ">
            <div class="handlediv" title="Click to toggle"><br/></div>
            <h3 class="hndle hndle-header" style="padding-left:10px;padding-bottom:10px;"><span>Room/Service Configuration</span></h3>
            <div class="inside">
                <?php
                $inc=1;
                foreach ($rooms as $room) {
					if($inc==4){
                    self::create_room_html($room, $services, $active_services_id, true);
                    $inc=1;
                    }else{
					self::create_room_html($room, $services, $active_services_id);
					}
					$inc++;
                }
                ?>
                <span style="clear:both;display:block;"></span>
            </div>
        </div>
        <?php
    }

    static function create_room_html($room_info, $services, $active_services_id, $new_row=false) {
        ?>
        <div id='glab_room_<?php echo $room_info['id']; ?>' data-id="<?php echo $room_info['id']; ?>" class='postbox glab_available_room' style='<?php echo $new_row?'clear:both;':'';?>'>
            <div class='handlediv' title=''>
                <a class="glab-option-menu" href="#">&nbsp;&nbsp;</a>
                <ul class="glab-toggle-menu">
                    <li><a href="" data-room_id="<?php echo $room_info['id']; ?>" data-target="duplicate">Duplicate</a></li>
                    <li><a href="" data-room_id="<?php echo $room_info['id']; ?>" data-target="deactivate">Deactivate</a></li>
					<li><a class="service_change" href="<?php echo plugins_url('glab_clinic/glab_ajax_clinic.php?action=room_service_alloc&room_id='.$room_info['id']); ?>" data-room_id="<?php echo $room_info['id']; ?>" data-target="change_schedule">Change Service</a></li>
                </ul>
            </div>
            <h3 class='hndle room-title' style='padding-left:10px;padding-bottom:10px;'><span><?php echo $room_info['title']; ?></span></h3>
            <div class='inside'>
                <ul class="glab-room-services">
                    <?php
					$active_services = glab_html_helper::only_active_room_services($room_info['services'], $active_services_id);
                    if (count($active_services)){
                        foreach ($services as $service) {
                            if(in_array($service['id'], $room_info['services']))
                                echo "<li>{$service['name']} <a href='' data-service_id='{$service['id']}'    data-room_id='{$room_info['id']}' class='glab-remove-service'>X</a></li>";
                        }
                    }else{
						echo '<div class="empty-room"><a data-room="'.$room_info['id'].'" href="#service_list_container" class="room_services">Add Room Services</a></div>';
					}
                    ?>
                </ul>
            </div>
        </div>
        <?php
    }

    static function generate_make_room_html() {
        ?>
        <div id="" class="postbox " style="width:450px;display:inline-block;">
            <div class="handlediv" title="Click to toggle"><br></div>
            <h3 class="hndle hndle-header" style="padding-left:10px;padding-bottom:10px;"><span>Make New Room</span></h3>
            <div class="glab-create-room inside">
                <form method="post" class="regular-form-wrapper" action="">
                    <label for="glab_room_name">Name :</label><br/>
                    <input type="text" style="margin-top:10px;" class="widefat glab_wp_text large" name="glab_room_name" id="glab_room_name" />
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Submit"></p>
                </form>
            </div>
        </div>
        <?php
    }

    static function generate_available_service_html($services) {
        ?>
        <div id="" class="postbox " style="width:580px;display:inline-block;vertical-align: top;min-height: 220px;float:right;">
            <div class="handlediv" title="Click to toggle"><br></div>
            <h3 class="hndle hndle-header" style="padding-left:10px;padding-bottom:10px;"><span>Available Services</span></h3>
            <div class="inside">
                <ul class="glab-clinic-services">
                    <?php foreach ($services as $service) { ?>
                        <li class="service_item" data-id="<?php echo $service['id']; ?>" ><span><?php echo $service['name']; ?></span></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
		<div id="service_list_container" style="width:300px;display:none;" class="service-list-container">
		<h1>List Of Services</h1>
		<form action="" method="post" id="service_lists_frm">
		<?php /*if(count($services)>10){ 
			self::display_single_column_table($services);
		}else{ 
			self::display_multi_column_table($services);
		} */
		self::display_multi_column_table($services);
		?>
		<input type="hidden" name="room_id" id="room_id" />
		</form>
		</div>
		<div id="change-service" style="width:300px;display:none;" class="change-service">
			<h2>awesome</h2>
		</div>
        <?php
		
    }
	
	static function display_multi_column_table($services, $selected=array()){

		?>
		<table class="service-table">
			<tr>
				<?php 
				$inc = 0;
				foreach ($services as $service) { 
				if($inc==2){
					echo "</tr><tr>";
					$inc = 0;
				}
				?>
				
					<td><input type="checkbox" name="services[]" value="<?php echo $service['id']; ?>" <?php echo (in_array($service['id'], $selected))?'checked="checked"':'';?> /> <?php echo $service['name']; ?></td>
				<?php 
				$inc++;
					} ?>
			</tr>
			<tr>
				<td class="button-td" colspan="2"><input type="submit" name="save_services" id="submit" class="button-primary" value="Save" /></td>
			</tr>
		</table>
		<?php
	}
	
	static function change_room_services($active_services, $room_services){
		?>
		<div id="service_list_container" style="width:300px;" class="service-list-container">
		<h1>List Of Services</h1>
		<form action="" method="post" id="service_lists_frm">
		<?php self::display_multi_column_table($active_services, $room_services); ?>
		<input type="hidden" name="room_id" id="existing_room_id" value="<?php echo $_GET['room_id']; ?>" />
		<input type="hidden" name="update_room_services"  value="yes" />
		</form>
		</div>
	<?php 
	}
	static function display_single_column_table($services){
	?>
		<table>
		<?php foreach ($services as $service) { ?>
		<tr>
			<td><input type="checkbox" name="services[]" value="<?php echo $service['id']; ?>" /> <?php echo $service['name']; ?></td>
		</tr>
		<?php } ?>
		<tr>
				<td><input type="submit" name="save_services" value="save services" /></td>
			</tr>
		</table>
	<?php
	}
	
	

    static function get_room_treatments($room_id = 0) {
        return '';
        $room_services = clinic_sql_retrieve('SELECT room_services FROM room WHERE status="1" and id="' . $room_id . '" limit 1');
        $room_service = $room_services[0]->room_services;
        $sql = 'SELECT cod_treatment,flag_name from treatments  WHERE cod_treatment IN (' . $room_service . ')';
        $result = clinic_sql_retrieve($sql);
        $treatments = '';
        foreach ($result as $row) {
            $treatments.='<div onmouseover="javascript:showCloseOpt(' . $room_id . ',' . $row->cod_treatment . ');" onmouseout="javascript:hideCloseOpt(' . $room_id . ',' . $row->cod_treatment . ');" id= "' . $room_id . '_' . $row->cod_treatment . '"class="c_room_treatments t_' . $row->cod_treatment . '">' . $row->flag_name . '<span style="position:absolute;right:5px;visibility:hidden;margin-top:-2px;" id="close_' . $room_id . '_' . $row->cod_treatment . '" class="treat_close"><a href="javascript:deleteRoomTreat(' . $room_id . ',' . $row->cod_treatment . ');" style="text-decoration:none;color:#6D6D6D;padding-left:3px;padding-right:3px;padding-bottom:3px;font-weight:normal;-moz-border-radius: 100px;-khtml-border-radius:100px;-webkit-border-radius: 100px;border-radius: 100px;">x</a><span></div>';
        }
        return $treatments;
    }

    static function cancellation_poll_old($args) {
        extract($args);
        ?>
        <form action="" method="post">
            <table>
                <tr>
                    <td><button  id="attach_poll" name="attach_poll" class="button-secondary add-elem" data-elem_type="poll">attach a <font style="font-weight:bold;">poll selection</font></button></td>
                    <td style="padding:0 0 0 40px;"><button  id="attach_option" name="attach_option" class="button-secondary add-elem" data-elem_type="comment">attach a <font style="font-weight:bold;">comment field</font></button></td>
                </tr>
            </table>
            <input type="hidden" id="optionNumber" name="optionNumber" value="1"/>
            <input type="hidden" id="pollNumber" name="pollNumber" value="0"/>
            <input type="hidden" id="sequence" name="sequence" value="<?= ($info['sequence']) ? $info['sequence'] : 'option_1'; ?>"/>

            <table id="content_table">
                <?php
                if ($info['content']) {
                    echo $info['content'];
                } else {
                    ?>
                    <tr id="option_1">
                        <td>&nbsp;</td>
                        <td style="padding:20px 0 0 200px;"><div style="position:relative;"><input type="text" style="padding-right:30px;" id="msg_for_text_1" name="format_text[]" placeholder="comment field" class="field_box" ><div data-target_id="option_1" id="close_for_msg" class="remove-elem" style="margin-left:-25px;width:20px;display:inline;cursor:pointer;"><img style="margin-bottom:-3px;" alt="x" src="<?= $base_url; ?>/images/cancel.png"></div></div></td>
                    </tr>
                <?php } ?>

            </table>
            <table>
                <tr>
                    <td>
                        <p class="submit">
                            <input type="hidden" name="is_update" value="<?php echo ($info['content']) ? '1' : '0'; ?>" />
                            <?php wp_nonce_field('cpoll_add', 'cpoll_add_nonce'); ?>
                            <input type="submit" value="Update" class="button-primary" name="update_poll">
                        </p>
                    </td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </form>
        <?php
    }
    
    static function cancellation_poll($args) {
        extract($args);
        ?>
        <form action="" method="post">
            <table>
                <tr>
                    <td><button  id="attach_poll" name="attach_poll" class="button-secondary add-elem" data-elem_type="poll">attach a <font style="font-weight:bold;">poll option</font></button></td>
                </tr>
            </table>

            <ul id="content_table" class="glab-poll-container" >
                <?php
                if ($info) {
                    echo $info;
                } else {
                    ?>
                <?php } ?>
            </ul>
            <table>
                <tr>
                    <td>
                        <p class="submit">
                            <?php wp_nonce_field('cpoll_add', 'cpoll_add_nonce'); ?>
                            <input type="submit" id="submit" value="Update" class="button-primary" name="update_poll">
                        </p>
                    </td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </form>
        <?php
    }

    static function confirmation_email($args) {
        extract($args);
        $settings = array(
            'media_buttons' => false,
            'textarea_name' => 'mail_content',
            'textarea_rows' => 10,
        );
        ?>
        <form method="post" action="" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <td colspan="2"><input type="text" class="glab_wp_text large"  name="mail_subject" style="" value="<?= stripslashes($info['mail_subject']); ?>"/></td>
                </tr>
                <tr>
                    <td style="padding-top:15px;" colspan="2">
                        <?php wp_editor($info['mail_content'], 'mail_content', $settings); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="submit">
                            <?php wp_nonce_field('cmail_add', 'cmail_add_nonce'); ?>
                            <input type="hidden" name="is_added" value="<?php echo $info['is_already_added']; ?>" />
                            <input type="submit" name="save_format" id="submit" class="button-primary" value="Save" />
                        </p>
                    </td>
                </tr>
            </table>
        </form>
        <?php
    }

    static function import() {
        ?>
        <div class="updated below-h2 import-status"><p><strong>successful</strong></p></div>
        <div>
            <h3>Important instruction about CSV file structure</h3>
            <ol style="list-style-type: decimal;margin-left: 30px;">
                <li>red colored are required field.</li>
                <li>optional fields will help for further analysis.</li>
                <li>room, services,practitioner should already been created</li>
                <li>only valid record will be inserted</li>
                <li>column name should be in similar order as mentioned below</li>
                <li>sample csv can be found  <a href="<?php echo plugins_url('glab_clinic/assets/data/sample.csv'); ?>">here</a></li>
            </ol>
            <h3>column names & required fields for CSV import</h3>
            <p style="color:red;">customer_firstname, customer_lastname, customer_email, customer_phone, doctors_email, service_name,<br/>app_start_time, app_end_time, app_date, app_reminder, room_name</p>
        </div>
        <form style="margin-top:30px;" method="post" enctype= "multipart/form-data">
            <table>
                <tr>
                    <th>select file To import appointments</th>
                    <td><input type="file" name="csv_file" /></td>
                </tr>
                <tr>
                    <th style="text-align:left;">
                <p class="submit">
                    <input type="hidden" name="process_csv" value="true" />
                    <input type="button" id="submit" value="submit" class="button-primary uploadBtn" data-target_url="<?php echo plugins_url("glab_clinic/glab_ajax_clinic.php"); ?>" name="cus_new_submit">
                </p>
                </th>
                <td></td>
                </tr>
            </table>
        </form>
        <div class="working">
            <div class="loading">processing..</div>
        </div>
        <style>
            .working{
                display:none;
                width: 700px;
                height: 500px;
                z-index: 2;
                background: gray;
                position: absolute;
                top: 100px;
                opacity: .6;
            }
            .loading{
                width: 100%;
                height: 50px;
                margin: 0 auto;
                text-align: center;
                display: block;
                /* background: orange; */
                /* opacity: 0.4; */
                color: white;
                padding-top: 20px;
                position: absolute;
                top: 50%;
                z-index: 50;
                /* font-weight: bold; */
                font-size: 20px;
            }
            .import-status{
                display:none;
            }
        </style>
        <?php
    }

    static function frontend_settings($args) {
        extract($args);
        ?>
        <div class="frontend_settings">
            <form  method="post" id="fsForm" action="">
                <table class="form-table th-medium">
                    <tr valign="top">
                        <th scope="row">Color of Widget</th>
                        <td style="padding-bottom:10px;"><input type="color" name="frame_color" value="<?php echo $info['frame_color']; ?>" placeholder="#ffffff" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Frame Width</th>
                        <td ><input type="number" min="430" class="glab_wp_text width-114" id="frame_width" name="frame_width" value="<?php echo $info['frame_width']; ?>" placeholder="eg. 430" /> (please input 430 for 430px)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Frame Height</th>
                        <td><input type="number" class="glab_wp_text width-114" id="frame_height" name="frame_height" value="<?php echo $info['frame_height']; ?>" placeholder="eg. 500" /> (please input 500 for 500px)</td>
                    </tr>
                    <tr>
                        <th>
                        <p class="submit">
                            <input type="submit" value="Update" id="submit" class="button-primary" name="update_frontend">
                        </p>
                    </th>
                    <td></td>
                    </tr>
                </table>
                 <?php wp_nonce_field('fs_add', 'fs_add_nonce'); ?>
            </form>
        </div>
        <div class="example_code">
            <h3>Embed code based on your configuration </h3>
            <xmp><iframe src="<?php echo site_url(); ?>" name="ScheduleOnceIframe" scrolling="auto" frameborder="0" hspace="0" 
                         marginheight="0" marginwidth="0" height="<?php echo $info['frame_height']; ?>px" width="<?php echo $info['frame_width']; ?>px" vspace="0" 
                         style="border-radius: 7px; -webkit-border-radius: 7px;border:3px solid <?php echo $info['frame_color']; ?>;"></iframe>
            </xmp>
        </div>
        <?php
    }

}
