function showAppDetails(id){
	if(!isNaN(id)){
		$.ajax({
			type: "POST",
			url: base_url+"/showAppDetails.php",
			data: "id="+id,
			success: function(msg){
				$("#divAppDetails2").html(msg);
				$("#divAppDetails").css("visibility","visible");
				$("#divAppDetails1").css("visibility","visible");
				$("#divAppDetails2").css("visibility","visible");
			}
		});
	}
}

function close_appDetails(){
	$("#divAppDetails").css("visibility","hidden");
	$("#divAppDetails1").css("visibility","hidden");
	$("#divAppDetails2").css("visibility","hidden");
	close_tb();
}
		

function close_tb(){
	$("#divHourDate").css("visibility","hidden");
	$("#divListHours").css("visibility","hidden");
	$("#divHours").css("visibility","hidden");
}

function edit_an_appointment(id){
	if(!isNaN(id)){
		close_tb();
		close_appDetails();
		$.ajax({
			type: "POST",
			url: base_url+"/editAppDetails.php",
			data: "id="+id,
			success: function(msg){
				$("#divEditAppDetails2").html(msg);
				$("#divEditAppDetails").css("visibility","visible");
				$("#divEditAppDetails1").css("visibility","visible");
				$("#divEditAppDetails2").css("visibility","visible");
			}
		});
	}
}

function delete_appointment(id){
	$.ajax({
		type: "POST",
		url: base_url+"/deleteApp.php",
		data: "id="+id,
		success: function(msg){
			alert(msg);
			close_tb();
			close_appDetails();
		}
	});
}

function edit_app_test(id){
	$(".eReminder").val(id);
}

function editAppointmentSubmit(){

	var pracId=$(".ePracId").val();
	var treatId=$(".eTreatId").val();
	var selDate=$(".eDate").val();
	var hour_minute_period=$(".eHour").val()+"-"+$(".eMinute").val()+"-"+$(".ePeriod").val();
	var eReminder=$(".eReminder").val();
	var eAppId=$("#editAppId").val();
	var docNotes=$(".docComments").val();
	$.ajax({
		type: "POST",
		url: base_url+"/editAppSubmit.php",
		data: "pracId="+pracId+"&treatId="+treatId+"&selDate="+selDate+"&hour_minute_period="+hour_minute_period+"&eReminder="+eReminder+"&eAppId="+eAppId+"&docNotes="+docNotes,
		success: function(msg){
			alert(msg);
		}
	});
}

jQuery(document).ready(function($){

	Set_Cookie( "filterDocId", "", "", "/", "", "" );
	$("#view_type").val("MONTHLY");
});

function close_edit_appointment(){
	$("#divEditAppDetails").css("visibility","hidden");
	$("#divEditAppDetails1").css("visibility","hidden");
	$("#divEditAppDetails2").css("visibility","hidden");
}

function Set_Cookie( name, value, expires, path, domain, secure ){
	// set time, it\'s in milliseconds
	var today = new Date();
	today.setTime( today.getTime() );

	/*
		if the expires variable is set, make the correct
		expires time, the current script below will set
		it for x number of days, to make it for hours,
		delete * 24, for minutes, delete * 60 * 24
	*/
	if ( expires ){
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name + "=" +escape( value ) +
				( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
				( ( path ) ? ";path=" + path : "" ) +
				( ( domain ) ? ";domain=" + domain : "" ) +
				( ( secure ) ? ";secure" : "" );
}

// this fixes an issue with the old method, ambiguous values
// with this test document.cookie.indexOf( name + "=" );
function Get_Cookie( check_name ) {
	
	var a_all_cookies = document.cookie.split( ";" );
	var a_temp_cookie = "";
	var cookie_name = "";
	var cookie_value = "";
	var b_cookie_found = false; // set boolean t/f default f
	for ( i = 0; i < a_all_cookies.length; i++ ){

		// now we will split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split( "=" );

		// and trim left/right whitespace while we are at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, "");

		// if the extracted name matches passed check_name
		if ( cookie_name == check_name ){
			b_cookie_found = true;
			// we need to handle case where cookie has no value but exists (no = sign, that is):
			if ( a_temp_cookie.length > 1 ){
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, "") );
			}

			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = "";
	}
	if ( !b_cookie_found ){
		return null;
	}
}