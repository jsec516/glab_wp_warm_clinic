(function($) {
    $.fn.glab_waiting = function(options) {

        var defaults = {
            switch_user_type: ".user-type",
            wait_prac: ".wait-prac",
            form_selector: ""
        };
        var settings = $.extend({}, defaults, options);

        var switchUserMode = function() {
            var $currentObj = $(this);
            switch ($currentObj.val()) {
                case '1':
                    loadNewUser();
                    break;
                case '2':
                    loadRegisteredUser();
                    break;
            }
        };

        var loadNewUser = function() {
            $(".only-new-cus").fadeIn();
        };

        var loadRegisteredUser = function() {
            $(".only-new-cus").fadeOut();
        };

        var loadPracSchedule = function() {
            var $currentObj = $(this);
            var prac_id = $currentObj.val();
            if (!prac_id) {
                $(".load-wait-schedule").html('').hide();
                return;
            }
            var data = {
                'prac_id': prac_id,
                'load_waiting_schedule': true
            };
            jQuery.post(glab_ajax_url, data, function(response) {
                if (response) {
                    $(".load-wait-schedule").html(response).show();
                }
            });
        };
        
        var loadPracServices = function() {
            var $currentObj = $(this);
            var prac_id = $currentObj.val();
            if (!prac_id) {
                $(".services").html('<option>select practitioner first</option>');
                return;
            }
            var data = {
                'prac_id': prac_id,
                'load_prac_services': true
            };
            jQuery.post(glab_ajax_url, data, function(response) {
                if (response) {
                    $(".services").html(response);
                }
            });
        };

        var submitWaitForm = function() {
            var error = "";
            var user_type = $("#user_type").val();
            
            if (user_type == '1') {
                //alert(user_type);
                error += $.trim($("input[name=first_name]").val()) ? "" : "First Name Can not be blank.\n";
                var new_password = $.trim($("input[name=new_password]").val());
                //alert(new_password + " 0ld "+ $.trim($("input[name=retype_pass]").val()))
                if (new_password)
                    error += (new_password == $.trim($("input[name=retype_pass]").val())) ? "" : "Retype Password Should be same with Password.\n";
                else
                    error += "Password Can not be blank.\n";
                error += $.trim($("input[name=email]").val()) ? "" : "Email Can not be blank.\n";
            } else {
                error += $.trim($("input[name=selected_user]").val()) ? "" : "Select registerd user.\n";
            }
            
            if($("select[name=wait_prac]").val()==''){
            	error += "Invalid Practitioner";
            }
            if($("select[name=service]").val()==''){
            	error += "Invalid Service";
            }
            
            if ($.trim(error)) {
                jAlert(error, 'Response Message');
                $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                return false;
            } else {
                return true;
            }
        };

        $(document).on("change", settings.switch_user_type, switchUserMode);
        $(document).on("change", settings.wait_prac, loadPracSchedule);
        $(document).on("change", settings.wait_prac, loadPracServices);
        $(document).on("submit", settings.form_selector, submitWaitForm);
    };
}(jQuery));
