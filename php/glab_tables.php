<?php
class glab_tables{
	
	private $wpdb;
	function __construct(){
		global $wpdb;
		$this->wpdb=$wpdb;
		$this->wpdb_prefix='glab_cas_';
	}
	
	function appointment(){
	
		$table_name = $this->wpdb_prefix."appointments";
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id mediumint(9) NOT NULL,
		first_name VARCHAR(255) NOT NULL,
		last_name VARCHAR(255) NOT NULL,
		email VARCHAR(255) NOT NULL,
		practitioner_id mediumint(9) NOT NULL,
		service_id mediumint(9) NOT NULL,
		service_duration mediumint(9) NOT NULL,
		app_date DATE NOT NULL,
		app_time VARCHAR(25) NOT NULL,
		app_end_time VARCHAR(25) NOT NULL,
		room_id mediumint(9) NOT NULL,
		reminder_type VARCHAR(1) NOT NULL,
		reminder_status ENUM('N','Y','B') NOT NULL DEFAULT 'N',
		reminder_sent DATE NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		pt_comments mediumtext,
		dr_comments mediumtext,
		is_synced VARCHAR(1) NOT NULL DEFAULT '1',
		cancel_reason mediumtext,
		app_type mediumint(1) NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."waiting_appointments";
		$sql .= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id mediumint(9) NOT NULL,
		practitioner_id mediumint(9) NOT NULL,
		service_id mediumint(9) NOT NULL,
		mon VARCHAR(100),
		tue VARCHAR(100),
		wed VARCHAR(100),
		thu VARCHAR(100),
		fri VARCHAR(100),
		sat VARCHAR(100),
		sun VARCHAR(100),
		app_reminder varchar(1) NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		create_at int(11),
		update_at int(11),
		blog_id int(11),
		blog_user_id int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		return $sql;
	}
	
	function config_clinic(){
		$table_name = $this->wpdb_prefix."hours";
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		hour time NOT NULL,
		num_hour float NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."poll_options";
		$sql .= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		poll_format text NOT NULL,
		element_format text NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."minutes";
		$sql .= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name VARCHAR(100) NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."clinic_hours";
		$sql .= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		mon VARCHAR(100),
		tue VARCHAR(100),
		wed VARCHAR(100),
		thu VARCHAR(100),
		fri VARCHAR(100),
		sat VARCHAR(100),
		sun VARCHAR(100),
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."mail_contents";
		$sql.= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		mail_for varchar(255) NOT NULL,
		mail_subject varchar(255) NOT NULL,
		mail_content text NOT NULL,
		mail_attachment varchar(255) NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."gc_accounts";
		$sql.= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		g_account varchar(255) NOT NULL,
		g_password varchar(255) NOT NULL,
		calendar_id varchar(255) NOT NULL,
		calendar_type varchar(15) NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."synced_accounts";
		$sql.= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		g_account varchar(255) NOT NULL,
		g_password varchar(255) NOT NULL,
		calendar_id varchar(255) NOT NULL,
		calendar_type varchar(15) NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."synced_calendars";
		$sql.= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		email varchar(255) NOT NULL,
		calendar_id varchar(255) NOT NULL,
		calendar_name varchar(255) NOT NULL,
		account_id mediumint(9) NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."synced_appointments";
		$sql.= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		calendar_id varchar(255) NOT NULL,
		event_id varchar(255) NOT NULL,
		event_title varchar(255),
		start_time varchar(255),
		end_time varchar(255),
		event_where varchar(255),
		event_content text,
		event_author_name varchar(255),
		event_author_email varchar(255),
		blog_id int(11),
		blog_user_id int(11),
		status VARCHAR(2) NOT NULL DEFAULT '1',
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
                
                $table_name = $this->wpdb_prefix."frontend_settings";
		$sql.= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		frame_color varchar(255) NOT NULL,
		frame_height mediumint(9) NOT NULL,
		frame_width mediumint(9) NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		return $sql;
		
	}
	
	function practitoner(){
		$table_name = $this->wpdb_prefix."practitioners";
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		first_name VARCHAR(255) NOT NULL,
		last_name VARCHAR(255) NOT NULL,
		email VARCHAR(255) NOT NULL,
		description text,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."practitioner_services";
		$sql .= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		practitioner_id mediumint(9) NOT NULL,
		service_id mediumint(9) NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."practitioner_hours";
		$sql .= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		practitioner_id mediumint(9) NOT NULL,
		mon VARCHAR(100),
		tue VARCHAR(100),
		wed VARCHAR(100),
		thu VARCHAR(100),
		fri VARCHAR(100),
		sat VARCHAR(100),
		sun VARCHAR(100),
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		return $sql;
	}
	
	function reminder(){
		
		$table_name = $this->wpdb_prefix."reminders";
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		practitioner_id mediumint(9) NOT NULL,
		service_ids VARCHAR(255) NOT NULL,
		subject VARCHAR(255) NOT NULL,
		email_content mediumtext,
		call_format mediumtext,
		call_element_format mediumtext,
		attach_file VARCHAR(400),
		is_file_played VARCHAR(3),
		reminder_day int(3) DEFAULT 1 NOT NULL,
		voice_type VARCHAR(8),
		reminder_type int(1),
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		return $sql;
	}
	
	function room(){
	
		$table_name = $this->wpdb_prefix."rooms";
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		$table_name = $this->wpdb_prefix."room_services";
		$sql .= "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		room_id mediumint(9) NOT NULL,
		service_id mediumint(9) NOT NULL,
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		
		return $sql;
	}
	
	
	function service(){
		$table_name = $this->wpdb_prefix."services";
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		description text,
		flag VARCHAR(255) NOT NULL,
		duration mediumint(5) NOT NULL DEFAULT '0',
		allow_multiple mediumint(2) NOT NULL,
		betn_minutes mediumint(5) NOT NULL DEFAULT '0',
		color_value varchar(20) NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		return $sql;
	}
	
	function user(){
		$table_name = $this->wpdb_prefix."users";
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		first_name VARCHAR(255) NOT NULL,
		last_name VARCHAR(255) NOT NULL,
		email VARCHAR(255) NOT NULL,
		password VARCHAR(255) NOT NULL,
		primary_doctor mediumint(9) NOT NULL,
		street varchar(255) NOT NULL,
		province varchar(255) NOT NULL,
		city varchar(255) NOT NULL,
		postal_code varchar(20) NOT NULL,
		address1 varchar(255) DEFAULT NULL,
		address2 varchar(255) DEFAULT NULL,
		activation_key varchar(100) NOT NULL,
		phone varchar(255) DEFAULT NULL,
		contact_me mediumint(1) DEFAULT NULL COMMENT '1 is phone, 2 is email, 3 for both',
		cell varchar(100) NOT NULL,
		work varchar(100) NOT NULL,
		primary_phone enum('PHONE','CELL','WORK') NOT NULL,
		status VARCHAR(2) NOT NULL DEFAULT '1',
		blog_id int(11),
		blog_user_id int(11),
		create_at int(11),
		update_at int(11),
		PRIMARY KEY  (id),
		UNIQUE KEY id (id)
		);";
		return $sql;
	}
	
	
}