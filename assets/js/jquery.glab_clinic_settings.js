function showCloseOpt(room,treatments){
	document.getElementById("close_"+room+"_"+treatments).style.visibility="visible";
}
					
function hideCloseOpt(room,treatments){
	document.getElementById("close_"+room+"_"+treatments).style.visibility="hidden";
}

function deleteRoomTreat(room,treatments){
	var answer = confirm("Do you want to delete treatments?");
	if(answer){
		var dataString = "room_id="+room+"&action=DELETE_ROOM_SERVICE&treat_id="+treatments;
		$.ajax({
			url : base_url + "/php/glab_clinic_settings_post.php",
			type:"POST",
			data:dataString,
			success: function(data) {
				$("#"+room+"_"+treatments).hide();
				alert(data);
			}
		});
	}
}

jQuery(document).ready(function($){
	$('.handlediv').click(function(event){
		var elem = $(this).next().next();
		if(elem.is('ul')){
			if(elem.is(":hidden")){
				$('.c_room_treatments').css('position','static');
				elem.slideToggle();
			}else{
				$('.c_room_treatments').css('position','relative');
				elem.slideToggle();
			}
					
		} else {
			alert('not done');
		}
	});
        
        
	$('.toggle_temp_menu').click(function(event){
		event.preventDefault();
		var id=$(this).attr('id');
		var slice_id=id.indexOf("_");
		var room_id=id.substring(0,slice_id);
		var action_type=id.substring(slice_id+1,id.length);
		if(action_type=='del')
			delete_room(room_id);
		else
			duplicate_room(room_id);
	});
});


function delete_room(id){
	var answer = confirm("Do you really want to delete?");
	if(answer){
		$.ajax({
			type: "POST",
			url: base_url+"/php/glab_clinic_settings_post.php",
			data: "action=DELETE_ROOM&id="+id,
			success: function(response){
			if(response){
					$('#'+id).css('display','none');
					window.location.href = current_url;
					return false;
				} else{
					alert('failed');
					return false;
				}
			}
		});
	}	
}

function resort_all_room(){
	$('#clinic_visible_rooms').fadeOut('slow', function() {
       $.ajax({
		type: "POST",
		url: base_url+"/php/glab_clinic_settings_post.php",
		data: "action=RETRIVE_ALL_ROOM",
		success: function(response){
				if(response){
					$('#clinic_visible_rooms').html(response);
					$('#clinic_visible_rooms').fadeIn('slow', function() {
					});
				} else{
					$('#clinic_visible_rooms').html("No room service.");
				}
			}
		});
    });
}

function delete_room_treatment(){
	$.ajax({
		type: "POST",
		url: base_url+"/php/glab_clinic_settings_post.php",
		data: "action=CREATE_ROOM&name="+roomName,
		success: function(response){
			var id=response;
			if(response){
				addNewRoom(response);
				$('.'+id).css('display','block');
				return false;
			} else{
				alert('failed');
				return false;
			}
		}
	});
}

function duplicate_room(id){
	var roomName=window.prompt("Enter Room Title","");
	if(roomName){
		$.ajax({
			type: "POST",
			url: base_url+"/php/glab_clinic_settings_post.php",
			data: "action=CREATE_ROOM&name="+roomName+"&duplicate_id="+id,
			success: function(response){
				var id=response;
				if(response){
					window.location.href = current_url;
				} else{
					alert('failed');
					return false;
				}
			}
		});
	}else{
	}
}

function ajaxNewRoom(){

	var roomName=$('#room_name').val();
	$.ajax({
		type: "POST",
		url: base_url+"/php/glab_clinic_settings_post.php",
		data: "action=CREATE_ROOM&name="+roomName,
		success: function(response){
			var id=response;
			if(response){
				addNewRoom(response);
				$('.'+id).css('display','block');
				return false;
			} else{
				alert('failed');
				return false;
			}
		}
	});
	return false;
}

function addNewRoom(id){
	$.ajax({
		type: "POST",
		url: base_url+"/php/glab_clinic_settings_post.php",
		data: "action=RETRIVE_ROOM&id="+id,
		success: function(response){
			if(response){
				$('.clinic_settings').append(response);
				$('.'+id).css('display','none');
				$('.'+id).fadeIn('slow');
			} else{
				alert('failed');
			}
		}
	});
}
