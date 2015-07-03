<?php
	//require_once('db.inc.php');
	//require_once('plugins_function.php');
	require_once('../../clinic-calender/mysql_db_connect.php');
	/*define("CLINIC_SERVER", "sql111.xtreemhost.com");
	define("CLININC_USERNAME", "xth_7552898");
	define("CLINIC_PASSWORD", "al638ext");
	define("CLINIC_DB", "xth_7552898_appointment");
	define("CLINIC_SERVER", "localhost");
	define("CLININC_USERNAME", "root");
	define("CLINIC_PASSWORD", "");
	define("CLINIC_DB", "test");*/
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='CREATE_ROOM' && isset($_REQUEST['name']) && !empty($_REQUEST['name'])){
		$sql='INSERT INTO room (room_name) VALUES ("'.addslashes($_REQUEST['name']).'")';
		$room_id=room_insert($sql);
		if($room_id!=0){
			if($_REQUEST['duplicate_id']){
				$dupRoomServices=service_retrieve('SELECT room_services FROM room where id="'.trim($_REQUEST['duplicate_id']).'"');
				$sql='UPDATE room set room_services="'.$dupRoomServices[0]['room_services'].'" WHERE id="'.$room_id.'"';
				mysql_query($sql);
			}
			echo $room_id;exit;
		}
		else{
			$arr=array('status'=>'failed');
			echo json_encode($arr);exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='REFRESH_ROOM'){
		$con = mysql_connect(CLINIC_SERVER,CLININC_USERNAME,CLINIC_PASSWORD);
		if (!$con) {
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db(CLINIC_DB, $con);
		
		//$content	=	"<div>";
		$chk='SELECT * from treatments WHERE status="1"';
		$avail_treatments=mysql_query($chk);
		while($row=mysql_fetch_array($avail_treatments)) {
			$content	.= '<span class="c_treat_option" id="treat_'.$row["cod_treatment"].'">'.$row["flag_name"].'</span>';
		} 
		//$content	.= "</div>";
		echo $content;
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='RETRIVE_ROOM' && isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		$id=$_REQUEST['id'];
		$sql="SELECT * FROM room where id='$id'";
		$result=room_retrieve($sql);
		$html='';
		foreach($result as $row){
			$html.=clinic_management_box($row['room_name'],'',$row['id']);
		}
		echo $html;exit;
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='DELETE_ROOM_SERVICE'){
		$room_id='';
		$treat_id='';
		if(isset($_REQUEST['room_id'])){
			$room_id=trim($_REQUEST['room_id']);
		}else{
			echo "failed please try again";exit;
		}
		if(isset($_REQUEST['treat_id'])){
			$treat_id=trim($_REQUEST['treat_id']);
		}else{
			echo "failed please try again";exit;
		}
		$sql="SELECT * FROM room where id='$room_id'";
		$result=room_retrieve($sql);
		foreach($result as $row){
			$services=explode(',',$row['room_services']);
		}
		$key = array_search($treat_id, $services);
		if($key!=''){
			unset($services[$key]);
		}else{
			echo "failed to found service,please try again";exit;
		}
		$room_services=implode(',',$services);
		$sql='update room set room_services="'.$room_services.'" where id="'.$room_id.'"';
		if(room_update($sql)){
			echo "Room Updated!!";exit;
		}else{
			echo "failed, please try again";
		}
		
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='DELETE_ROOM' && isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		$sql='DELETE FROM room where id="'.$_REQUEST['id'].'"';
		$room_id=room_update($sql);
		echo 'done';exit;
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='ADD_ROOM_SERVICE' && isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
		$id=$_REQUEST['id'];
		$room_id=$_REQUEST['room_id'];
		
		$sql="SELECT * FROM room where id='$room_id' and status='1' limit 1";
		$result=room_retrieve($sql);
		$room_service=array();
		
		foreach($result as $row){
			$room_service=explode(',',$row['room_services']);
		}
		$key = array_search($id, $room_service);
		if($key!=''){
			echo 'This service already exist.';
		}else{
			array_push($room_service,$_REQUEST['id']);
			$str=implode(',',$room_service);
			$up_sql='UPDATE room set room_services="'.$str.'" WHERE id="'.$room_id.'"';
			if(room_update($up_sql)){
				$sql="SELECT t.cod_treatment as id,t.* FROM treatments as t where cod_treatment='$id'";
				$result=room_retrieve($sql);
				$service_name='';
				foreach($result as $row){
					$service_name=$row['flag_name'];
				}
				echo 'Added Successfully';exit;
			} else{
				echo 'Failed to Add,Please try again';exit;
			}
		}
		
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='RETRIVE_ALL_ROOM_SERVICES' && isset($_REQUEST['target_room_id'])){
	
		$room_id=$_REQUEST['target_room_id'];
		/*$room_services=service_retrieve('SELECT room_services FROM room WHERE status="1" and id="'.$room_id.'" limit 1');
		$room_service=$room_services[0]['room_services'];
		$sql='SELECT cod_treatment,flag_name from treatments  WHERE cod_treatment IN ('.$room_service.')';
		$result=service_retrieve($sql);
		$treatments='';
		foreach($result as $row){
			$treatments.='<span id= "'.$room_id.'_'.$row['cod_treatment'].'"class="c_room_treatments t_'.$row['cod_treatment'].'">'.$row['flag_name'].'</span>';
		}
		echo $treatments;exit;*/
		echo get_mysql_room_treatments($room_id);
		
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='RETRIVE_ALL_ROOM'){
		$sql="SELECT * FROM room where status='1'";
		$result=room_retrieve($sql);
		$html='';
		foreach($result as $row){
			$box_title=$row['room_name']; 
			if($box_title=='')
				$box_title='Room';
			$data_var='<div>'.get_mysql_room_treatments($row['id']).'</div>';
			$html.=get_clinic_management_box($box_title,$data_var,$row['id']);
		}
		echo $html;exit;
	}
	
	function get_clinic_management_box($box_title='',$data='',$box_id=''){
		$clinic_box='<div id="dashboard-widgets-wrap"  class="" style="width:270px;padding-left:20px;padding-top:20px;margin-left:10px;float:left;position:relative;">
		<div class="metabox-holder" id="dashboard-widgets">
		<div class="inner-sidebar" id="side-info-column">
		<div class="meta-box-sortables ui-sortable" id="side-sortables" style="min-height: 0pt; position: relative; height: auto;">
		</div></div>
		<div id="post-body" class="">
		<div class="has-sidebar-content" id="dashboard-widgets-main-content">
		<div class="meta-box-sortables ui-sortable" id="normal-sortables" style="position: relative;">
		<div class="">
		<div class="postbox closed room_inside c_room_class ui-droppable" id="'.$box_id.'" style="min-height:250px;">
			<div title="Click to See Menu" class="handlediv"><br></div><h3 class="hndle"><span>'.$box_title.'</span></h3>
			<ul style="display:none;position:absolute;"><li style="padding-left:185px;">
			<a id="'.$box_id.'_del" href="javascript:void(0);" class="toggle_temp_menu">Delete</a>
			<a id="'.$box_id.'_dup" href="javascript:void(0);" class="toggle_temp_menu">Duplicate</a>
			</li></ul>
			<div class="inside sortable_room"  style="display:block;">
		';
		$clinic_box.=$data;
		$clinic_box.='
			</div>
		</div></div></div></div>
		</div>
		<form action="" method="get" style="display: none;">
		<p>
			<input type="hidden" value="a76be45936" name="closedpostboxesnonce" id="closedpostboxesnonce"><input type="hidden" value="b3aa782c9b" name="meta-box-order-nonce" id="meta-box-order-nonce"></p>
		</form>
		</div>
		<div class="clear"></div>
		</div>
		';
		return $clinic_box;
	}
	
	function get_mysql_room_treatments($room_id=0){
		$room_services=service_retrieve('SELECT room_services FROM room WHERE status="1" and id="'.$room_id.'" limit 1');
		$room_service=$room_services[0]['room_services'];
		$sql='SELECT cod_treatment,flag_name from treatments  WHERE cod_treatment IN ('.$room_service.')';
		$result=service_retrieve($sql);
		$treatments='';
		foreach($result as $row){
			//$treatments.='<span id= "'.$room_id.'_'.$row['cod_treatment'].'"class="c_room_treatments t_'.$row['cod_treatment'].'">'.$row['flag_name'].'</span>';
			$treatments.='<div onmouseover="javascript:showCloseOpt('.$room_id.','.$row['cod_treatment'].');" onmouseout="javascript:hideCloseOpt('.$room_id.','.$row['cod_treatment'].');" id= "'.$room_id.'_'.$row['cod_treatment'].'"class="c_room_treatments t_'.$row['cod_treatment'].'" style="position:relative;">'.$row['flag_name'].'<span style="position:absolute;right:5px;visibility:hidden;" id="close_'.$room_id.'_'.$row['cod_treatment'].'" class="treat_close"><a href="javascript:deleteRoomTreat('.$room_id.','.$row['cod_treatment'].');" style="text-decoration:none;color:#6D6D6D;padding-left:3px;padding-right:3px;font-weight:normal;-moz-border-radius: 100px;-khtml-border-radius:100px;-webkit-border-radius: 100px;border-radius: 100px;">x</a><span></div>';
		}
		return $treatments;
	}
	
	function room_insert($sql){
		$con = mysql_connect(CLINIC_SERVER,CLININC_USERNAME,CLINIC_PASSWORD);
		if (!$con) {
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db(CLINIC_DB, $con);
		mysql_query($sql);
		return mysql_insert_id();
	}
	
	function room_retrieve($sql=''){
	
		$con = mysql_connect(CLINIC_SERVER,CLININC_USERNAME,CLINIC_PASSWORD);
		if (!$con){
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db(CLINIC_DB, $con);
		$result = mysql_query($sql);
		$data=array();
		while($row = mysql_fetch_array($result)){
			$data[$row['id']]=$row;
		}
		mysql_close($con);
		return $data;
	}
	
	function service_retrieve($sql=''){
		$con = mysql_connect(CLINIC_SERVER,CLININC_USERNAME,CLINIC_PASSWORD);
		if (!$con){
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db(CLINIC_DB, $con);
		$result = mysql_query($sql);
		$data=array();
		$i=0;
		while($row = mysql_fetch_array($result)){
			$data[$i]=$row;
			$i++;
		}
		//mysql_close($con);
		return $data;
	}
	
	function room_update($up_sql=''){
		$con = mysql_connect(CLINIC_SERVER,CLININC_USERNAME,CLINIC_PASSWORD);
		if (!$con){
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db(CLINIC_DB, $con);
		return mysql_query($up_sql);
		
	}
	
	
	
?>