/* 
 * @author: jahidul islam
 * a jQuery plugin to handle drag drop and save the information to database
 */

;
(function($) {

	var defaults = {
		draggable_selector : ".service_item",
		droppable_selector : ".glab_available_room",
		room_service_selector : ".glab_room_service",
		ajax_request_url : "",
		service_alloc_url : "",
		room_resort_url : ""
	};

	$.fn.glab_dragdrop = function(options) {
		var config = $.extend({}, defaults, options);
		initialize(config);
		return this;
	};

	var initialize = function(config) {
		$(config.draggable_selector).draggable({
			revert : "invalid",
			helper : 'clone'
		});
		$(config.room_service_selector).draggable({
			disabled : true
		});
		$(config.droppable_selector).droppable({
			drop : function(event, ui) {
				var service_id = ui.draggable.data('id');
				var room_id = $(event.target).data('id');
				console.log("service id: " + service_id);
				console.log("room id: " + room_id);
				allocate_service_on_room(service_id, room_id);
			}
		});
	};

	var allocate_service_on_room = function(service_id, room_id) {
		addLoader();
		var data = {
			'service_id' : service_id,
			'room_id' : room_id,
			'service_alloc' : true
		};

		// save room service via ajax request
		jQuery.post(glab_ajax_url, data, function(response) {
			// resort room services again
			resort_room_services(room_id);
			
		});
	};

	var resort_room_services = function(room_id) {
		var data = {
			'room_id' : room_id,
			'room_resort' : true
		};

		// save room service via ajax request
		jQuery.post(glab_ajax_url, data, function(response) {
			
			var param = jQuery.parseJSON(response);
			// resort room services again
			if (param.status)
				$("#glab_room_" + room_id + " .inside").html(param.data);
				
			location.reload();
		});
	};
}(jQuery));
