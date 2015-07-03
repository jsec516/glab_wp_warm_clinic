jQuery(document).ready(function($) {
   // $("body").glab_dragdrop();

	$(".room_services").on('click', function(e) {
		var $currentObj = $(this);
		var room_id=$currentObj.data("room");
        $("#room_id").val(room_id);
    });
	
	$(".room_services,.service_change").fancybox({
		
		});

	$(".service_change").on('click', function(e) {
		//var $currentObj = $(this);
		/*$.fancybox({
			
			content: "<a href='' class='close-btn'>close</a>",
			// fix inline bug
			hideOnOverlayClick: false,
			
	    });
	    return false;*/
    });
		
	
	
    $(".glab_available_room .glab-option-menu").on('click', function(e) {
        e.preventDefault();
        var $currentObj = $(this);
        var next_elem = $currentObj.next();
        var willSlideDown = (next_elem.css('display') == 'none') ? true : false;
        $(".glab-toggle-menu").slideUp();
        if (willSlideDown)
            next_elem.slideDown();
    });

    $(".glab-toggle-menu a").on('click', function(e) {
        
        var $currentObj = $(this);
        var room_id = $currentObj.data('room_id');
        var action_type = $currentObj.data('target');
        if (action_type == 'deactivate') {
			e.preventDefault();
            if (confirm("Are You Sure to Deactivate the room?"))
                deactivate_room(room_id);
        } else if(action_type == 'duplicate') {
			e.preventDefault();
            duplicate_room(room_id);
        } else{
        	$.get( glab_ajax_url+"?get_room_service_html=true&room_id="+room_id, function( data ) {
        		$.fancybox({
        			content: data,
        	    });
        	});
        	
			return false;
		}
    });

    $(".glab-remove-service").on('click', function(e) {
        e.preventDefault();
        var $currentObj = $(this);
        var service_id = $currentObj.data('service_id');
        var room_id = $currentObj.data('room_id');
        if (confirm("Are You Sure to Delete?"))
            delete_room_service(room_id, service_id);
    });
    //$(".glab_available_room").draggable({disabled: true});
});

var duplicate_room = function(id) {
    var $ = jQuery;
    var roomName = window.prompt("Enter Room Title", "");
    if (roomName) {
        $.ajax({
            type: "POST",
            url: glab_ajax_url,
            data: "CREATE_ROOM=true&new_name=" + roomName + "&duplicate_id=" + id,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    location.reload();
                } else {
                    alert('failed');
                    return false;
                }
            }
        });
    } else {
        alert("please define room name before duplication.");
    }
}

var deactivate_room = function(id) {
    var $ = jQuery;
    if (id) {
        $.ajax({
            type: "POST",
            url: glab_ajax_url,
            data: "DEACTIVATE_ROOM=true&room_id=" + id,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    location.reload();
                } else {
                    alert('failed');
                    return false;
                }
            }
        });
    } else {
        alert("please define room name before duplication.");
    }
}

var delete_room_service = function(room_id, service_id) {
    var $ = jQuery;
    $.ajax({
        type: "POST",
        url: glab_ajax_url,
        data: "DELETE_ROOM_SERVICE=true&room_id=" + room_id + "&service_id=" + service_id,
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                location.reload();
            } else {
                alert('failed');
                return false;
            }
        }
    });
};