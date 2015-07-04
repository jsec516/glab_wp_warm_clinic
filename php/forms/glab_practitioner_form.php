<?php
require_once plugin_dir_path(__FILE__).'../helper/glab_html_helper.php';

class glab_practitioner_form {
	
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
        <?php wp_nonce_field('prac_schedule', 'prac_schedule_nonce'); ?>
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
    
	static function add($args) {
		$settings=array(
				'media_buttons' => false,
				'textarea_name' => 'prac_description',
				'textarea_rows' => 10,
				
		);
?>
<form method="post" id="pracForm" action="">
	<table class="form-table th-medium">
		<tr valign="top">
			<th scope="row">First Name</th>
			<td><input type="text" name="prac_first_name" class="glab_wp_text large" placeholder="first name" required /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Last Name</th>
			<td><input type="text" name="prac_last_name" class="glab_wp_text large" placeholder="last name" required /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Email</th>
			<td><input type="email" name="prac_email" class="glab_wp_text large" placeholder="example@email.com" required /></td>
		</tr>

		
		<tr valign="top">
			<th scope="row">Select Treatments That This<br/>Practitioners Performs From<br/>Available Treatments List</th>
			<td>
				<select size="5" name="prac_treat_list[]" class="glab_wp_select large" multiple >
					<?php echo glab_html_helper::prac_service_option($args['all_services']);?>
				</select>
			</td>
		</tr>
		
		
		
		<tr valign="top">
			<th scope="row">Biography Of The Practitioner</th>
			<td><?php wp_editor( '', 'prac_description',$settings );?></td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><?php submit_button( 'Submit' );?></th>
			<td>&nbsp;</td>
		</tr>

		
	</table>
	<?php wp_nonce_field('prac_add','prac_add_nonce'); ?>
</form>
<?php
	}
	
	static function edit($args) {
		extract($args);
		$settings=array(
				'media_buttons' => false,
				'textarea_name' => 'prac_description',
				'textarea_rows' => 10,
	
		);
		?>
	<form method="post" id="pracForm" action="">
	<table class="form-table th-medium">
		<tr valign="top">
			<th scope="row">First Name</th>
			<td><input type="text" name="prac_first_name" class="glab_wp_text large" placeholder="first name" value="<?php echo $data['first_name']?>" required /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Last Name</th>
			<td><input type="text" name="prac_last_name" class="glab_wp_text large" placeholder="last name" value="<?php echo $data['last_name']?>" required /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Email</th>
			<td><input type="email" name="prac_email" class="glab_wp_text large" placeholder="example@email.com" value="<?php echo $data['email']?>" required /></td>
		</tr>

		
		<tr valign="top">
			<th scope="row">Select Treatments That This<br/>Practitioners Performs From<br/>Available Treatments List</th>
			<td>
				<select size="5" name="prac_treat_list[]" multiple class="glab_wp_select large" required>
					<?php echo glab_html_helper::prac_service_option($all_services,$selected_services);?>
				</select>
			</td>
		</tr>
		
		
		
		<tr valign="top">
			<th scope="row">Biography Of The Practitioner</th>
			<td><?php wp_editor( $data['description'], 'prac_description',$settings );?></td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><?php submit_button( 'Submit' );?></th>
			<td>&nbsp;</td>
		</tr>

		
	</table>
	<?php wp_nonce_field('prac_edit','prac_edit_nonce'); ?>
</form>
	<?php
		}
}
