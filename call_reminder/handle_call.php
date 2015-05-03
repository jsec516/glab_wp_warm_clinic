<?php 
require_once '../mysql_db_connect.php';
$con = mysql_connect(CLINIC_SERVER,CLININC_USERNAME,CLINIC_PASSWORD);
		if (!$con){
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db(CLINIC_DB, $con);
		$response_body=mysql_real_escape_string($_REQUEST['saying_text']);
		$sql="insert into tmp_call_script set voice_type='$_REQUEST[voice_type]',response_body='$response_body'";
		$result = mysql_query($sql);
		mysql_close($con);
//echo $_SESSION['voice_type'];exit;
require_once 'dial.php';
$dialer = new dial_twilio();
$dialer->make_call(TRUE, $_REQUEST['dial_url'], $_REQUEST['to_number'], $_REQUEST['voice_type'],$_REQUEST['saying_text'],NULL);
?>