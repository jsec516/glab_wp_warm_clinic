jQuery(document).ready(function($)
{

    if ($.isFunction($.fn.wpColorPicker)) {
        $("#colorpicker1").wpColorPicker();
    }

    /*if ($("#serviceForm").length > 0) {
        $('#serviceForm').validate();
    }*/
	if($(".glab-validate-frm").length > 0){
		$(".glab-validate-frm").validate();
	}
	
	$(".glab-validate-frm").on('submit',function(e){
		var $currentObj=$(this);
		//alert($currentObj.find('select[name="service_hour"]').length);
		if($currentObj.find('select[name="service_hour"]').length>0){
			var hour = $('select[name="service_hour"]').val();
			var minute = $('select[name="service_minute"]').val();
			var total = parseInt(hour) + parseInt(minute);
			if(total<=0){
				$(".service_duration_fields").append('<label for="service_duration" class="error">This field is required.</label>');
				e.preventDefault();
			}
		}
		
	});
    if (typeof $("input[name='service_multi_client']") !== undefined) {
        $("input[name='service_multi_client']").click(function() {
            var currentObj = $(this);
            var selected = $.trim(currentObj.val());
            if (selected === 'Y') {
                $(".interval-opt").slideDown("slow");
            } else {
                $(".interval-opt").slideUp("slow");
            }
        });

    }

    if ($("#scheduleForm").length > 0 && $.isFunction($.fn.glab_schedule)) {
        $("#scheduleForm").glab_schedule();
    }
    //$("#calendar-head-option").length > 0 && 
    if ($.isFunction($.fn.glab_calendar)) {
        $("body").glab_calendar();
    }

    if ($.fn.glab_user) {
        $("body").glab_user();
    }

    if ($.fn.glab_appointment) {
        $("body").glab_appointment();
    }
    
    if($.fn.glab_waiting){
        $("body").glab_waiting();
    }

    if($.fn.glab_reminder){
        $("body").glab_reminder();
    }
    
    if ($.fn.glab_poll) {
        $("body").glab_poll();
    }
});

function close_popupdiv()
{
    jQuery("#popup_overlay").remove();
    jQuery("#popup_container").remove();
}