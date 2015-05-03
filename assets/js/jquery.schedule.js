(function($) {

    $.fn.glab_schedule = function(options) {

        var defaults = {
            start_class: ".start",
            end_class: ".end",
            close_class: ".schedule-close",
        };
        var ChangeToValue = function() {

            var $current_obj = $(this);
            var selector = $current_obj.data('target');
            var day = $current_obj.data('day');
            var selectedValue = $current_obj.val();
            if (parseInt(selectedValue) >= 12) {
                $('#' + day + "_s_meridian").html('PM');
            }
            else {
                $('#' + day + "_s_meridian").html('AM');
            }
            /* change to */
            var option_html = '';
            var last_hour = $('.end option:last-child').val(); // 22 was static value
            var current_hour = parseInt(selectedValue) + 1;
            var final_hour = parseInt(last_hour);
            if (!selectedValue.indexOf('0')) {
                current_hour = selectedValue.substr(1);
                current_hour = parseInt(current_hour) + 1;
            }
            var new_to_hour = current_hour;
            while (current_hour <= final_hour) {
                if (true) {
                    if (current_hour > 12) {
                        if (current_hour < 22)
                            option_html += "<option value='" + current_hour + "'>0" + (current_hour - 12) + "</option>";
                        else
                            option_html += "<option value='" + current_hour + "'>" + (current_hour - 12) + "</option>";
                    } else {
                        current_hour = '' + current_hour;
                        if (current_hour.length <= 1)
                            option_html += "<option value='" + current_hour + "'>0" + current_hour + "</option>";
                        else
                            option_html += "<option value='" + current_hour + "'>" + current_hour + "</option>";
                    }

                }
                current_hour++;
            }
            if (parseInt(new_to_hour) >= 12) {
                $('#' + day + "_d_meridian").html('PM');
            }
            else {
                $('#' + day + "_d_meridian").html('AM');
            }
            $("#" + selector).html(option_html);
            /* end of change to */
            //alert($current_obj.attr("class"));
        };

        var showScheduleForm = function(e) {
            e.preventDefault();
            $("#scheduleForm").fadeIn("slow");
        };
        var hideScheduleForm = function(e) {
            e.preventDefault();
            $("#scheduleForm").fadeOut("slow");
        };
        var ChangeToMeridan = function() {
            var $current_obj = $(this);
            var selectedValue = $current_obj.val();
            var day = $current_obj.data('day');
            if (parseInt(selectedValue) >= 12) {
                $('#' + day + "_d_meridian").html('PM');
            }
            else {
                $('#' + day + "_d_meridian").html('AM');
            }
        };
        var settings = $.extend({}, defaults, options);
        var parent_obj = $(this);
        //$(parent_obj).find(settings.start_class).on( "change", ChangeToValue );
        $(document).on("change", settings.start_class, ChangeToValue);
        //$(parent_obj).find(settings.end_class).on( "change", ChangeToMeridan );
        $(document).on("change", settings.end_class, ChangeToMeridan);
        //$(parent_obj).find(settings.close_class).on( "click", hideScheduleForm );
        $(document).on("click", settings.close_class, hideScheduleForm);
        //$("body").find('.change-schedule').on("click",showScheduleForm);
        $(document).on("click", '.change-schedule', showScheduleForm);
    };

}(jQuery));