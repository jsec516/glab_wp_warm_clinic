var picId="";
var id;

jQuery(document).ready(function($)
{

	$(function() {
		$('.c_treat_option').draggable({revert: "invalid"});
		$('.c_room_treatments').draggable({disabled:true});
	});

	var treat_id='';

	$('.c_room_treatments').mousedown(function(){
		treat_id=$(this).attr('id');
	});

	$('.c_treat_option').mousedown(function(){
	});	

	var aryClassElements = new Array();	

	$(".room_inside").droppable({
		drop: function(event, ui) {var room_id=$(this).attr('id'); movePicture(ui.draggable,room_id); }
	});

	$("#private_gallery").droppable({
		drop: function(event, ui) { movePicture(ui.draggable,'private'); }
	});
});

function dragPicture(picId) {

	var div = '#box102_'+picId;
	$(div).draggable();
	$("#private_gallery").droppable({
		drop: function() { alert('dropped'+picId); }
	});
}



function dropPicture(picId) {
	$("#private_gallery").droppable({
		drop: function() { alert('dropped'+picId); }
	});
}

var status;

function movePicture(obj,room_id){

	var src = $(obj).attr('id');
	var explodestring=src.split("_");
	src	=	explodestring[1];
	if(isNaN(src)){
	}else{
		var dataString = 'id='+src+'&action=ADD_ROOM_SERVICE&room_id='+room_id;
		$.ajax({
			url : base_url + "/php/glab_clinic_settings_post.php",
			type:'POST',
			data:dataString,
			success: function(data) {
				resort_all_rooms(room_id,src);
			}
		});
	}
}



function resort_all_rooms(room_id,service_id){

	$('.sortable_room_'+room_id).fadeOut('fast', function() {

		$.ajax({
			type: "POST",
			url: base_url+"/php/glab_clinic_settings_post.php",
			data: "action=RETRIVE_ALL_ROOM_SERVICES&target_room_id="+room_id,
			success: function(response){

				if(response){
					$('#treat_'+service_id).html("");
					$('#treat_'+service_id).hide();
					$('.sortable_room_'+room_id).html(response);
					$('.sortable_room_'+room_id).fadeIn('fast', function() {
					});
					refresh_allServices();
				} else{

					$('#'+service_id).html("");
					$('#'+service_id).hide();
					$('.sortable_room_'+room_id).html("No room service.");
				}
			}
		});
	});
}



function refresh_allServices() {

	$.ajax({
		type: "POST",
		url: base_url+"/php/glab_clinic_settings_post.php",
		data: "action=REFRESH_ROOM",
		success: function(data){
			if(data){
				$('#treat_box').html(data); 
				$('.c_treat_option').draggable({revert: "invalid"});
				$('.c_room_treatments').draggable({disabled:true});
			} else{
			}
		}
	});
}



function reloadRoomServices(room_id,service_id){

	$('#'+room_id).fadeOut('slow', function() {
		$.ajax({
			type: "POST",
			url: base_url+"/php/glab_clinic_settings_post.php",
			data: "action=RETRIVE_ALL_ROOM_SERVICES&target_room_id="+room_id,
			success: function(response){
				if(response){
					$('#'+service_id).html("");
					$('#'+service_id).hide();
					$('#'+room_id).html(response);
					$('#'+room_id).fadeIn('slow', function() {
					});
				} else{
					$('#'+room_id).html("No room service.");
				}
			}
		});
	});
}



function activate_all_droppable(){
	$(".room_inside").droppable({
		drop: function(event, ui) { 
			var room_id=$(this).attr('id'); movePicture(ui.draggable,room_id); 
		}
	});
}