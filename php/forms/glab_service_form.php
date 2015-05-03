<?php
require_once plugin_dir_path(__FILE__).'../helper/glab_html_helper.php';

class glab_service_form {
	
	static function add() {
		$settings=array(
				'media_buttons' => false,
				'textarea_name' => 'service_description',
				'textarea_rows' => 10,
				
		);
?>
<form method="post" id="serviceForm" class="glab-validate-frm" action="">
	<table class="form-table th-medium">
		<tr valign="top">
			<th scope="row">Name <span class="required">*</span></th>
			<td><input type="text" name="service_name" class="glab_wp_text large" placeholder="service name" required /></td>
		</tr>

		<tr valign="top">
			<th scope="row">Length Of Appointment <span class="required">*</span></th>
			<td class="service_duration_fields">
				<select name="service_hour" class="glab_wp_select auto" number>
					<?php echo glab_html_helper::service_hour_option();?>
				</select>
				<select name="service_minute" class="glab_wp_select auto" number>
					<?php echo glab_html_helper::service_minute_option();?>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Allow multiple clients</th>
			<td>
				<input type="radio" name="service_multi_client" value="Y" /> Yes
				<input type="radio" name="service_multi_client" checked value="N" /> No 
				 <p class="interval-opt" style="display: none;">Please Define The Length Of Time Between Appointments 
				 <select name="service_interval" class="glab_wp_select auto">
				 	<?php echo glab_html_helper::service_interval_option();?>
				 </select>
				 </p>
				 
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Service Code <span class="required">*</span></th>
			<td><input type="text" name="service_code" class="glab_wp_text width-114" maxlength="3" placeholder="" required /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Service Color <span class="required">*</span></th>
			<td><input type="text" id="colorpicker1" name="service_color" required /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Description</th>
			<td><?php wp_editor( '', 'service_description',$settings );?></td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><?php submit_button( 'Submit' );?></th>
			<td>&nbsp;</td>
		</tr>

		
	</table>
	<?php wp_nonce_field('service_add','service_add_nonce'); ?>
</form>
<?php
	}
	
	static function edit($data) {
		$settings=array(
				'media_buttons' => false,
				'textarea_name' => 'service_description',
				'textarea_rows' => 10,
	
		);
		?>
	<form method="post" action="" class="glab-validate-frm">
		<table class="form-table th-medium">
			<tr valign="top">
				<th scope="row">Name <span class="required">*</span></th>
				<td><input type="text" name="service_name" class="glab_wp_text large" placeholder="service name" value="<?php echo $data['name'];?>"  required /></td>
			</tr>
	
			<tr valign="top">
				<th scope="row">Length Of Appointment</th>
				<td>
					<select name="service_hour" class="glab_wp_select auto" number>
						<?php $data['hours']=glab_convert_helper::only_hours($data['duration']);?>
						<?php echo glab_html_helper::service_hour_option($data['hours']);?>
					</select>
					<select name="service_minute" class="glab_wp_select auto" number>
						<?php $data['minutes']=glab_convert_helper::only_minutes($data['duration']);?>
						<?php echo glab_html_helper::service_minute_option($data['minutes']);?>
					</select>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">Allow multiple clients</th>
				<td>
					<input type="radio" name="service_multi_client" <?php echo $data['allow_multiple']?'checked':'';?> value="Y" /> Yes
					<input type="radio" name="service_multi_client" <?php echo $data['allow_multiple']?'':'checked';?> value="N" /> No 
					 <p class="interval-opt" style="<?php echo $data['allow_multiple']?'':'display: none;';?>">Please Define The Length Of Time Between Appointments 
					 <select name="service_interval" class="glab_wp_select auto">
					 	<?php echo glab_html_helper::service_interval_option($data['betn_minutes']);?>
					 </select>
					 </p>
					 
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">Service Code <span class="required">*</span></th>
				<td><input type="text" name="service_code" class="glab_wp_text width-114" maxlength="3" placeholder="maximum 3 character" value="<?php echo $data['flag'];?>" required /></td>
			</tr>
			
			<tr valign="top">
				<th scope="row">Service Color <span class="required">*</span></th>
				<td><input type="text" id="colorpicker1" name="service_color" value="<?php echo $data['color_value'];?>" /></td>
			</tr>
			
			<tr valign="top">
				<th scope="row">Description</th>
				<td><?php wp_editor( $data['description'], 'service_description',$settings );?></td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><?php submit_button( 'Update' );?></th>
				<td><input type="hidden" name="service_id" value="<?php echo base64_encode($data['id']+516);?>" /></td>
			</tr>
			
			
		</table>
		<?php wp_nonce_field('service_update','service_update_nonce'); ?>
	</form>
	<?php
		}
}
