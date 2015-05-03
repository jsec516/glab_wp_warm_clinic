(function($) {
    $.fn.glab_upload = function(options) {

        var defaults = {
            reminder_selection: "select[name='reminder_type']"
        };

        var settings = $.extend({}, defaults, options);

        var loadReminderType = function() {
            var selected_type = $(this).val();

            if (selected_type == '2') {
                $("#tbl-email-reminder").hide();
                $("#tbl-call-reminder").fadeIn('slow');
            } else {
                $("#tbl-call-reminder").hide();
                $("#tbl-email-reminder").fadeIn('slow');
            }
        };

        $(document).on("change", settings.reminder_selection, loadReminderType);
    };
}(jQuery));
