(function($) {
    $.fn.glab_user = function(options) {
        var defaults = {
            user_type: "#usrval",
            user_firstname: ".firstName",
            user_lastname: ".lastName",
            from_selector: "#break_st_box",
            customer_option: ".suggested_customer"
        };
        var settings = $.extend({}, defaults, options);
        var displayUserOption = function() {
            var type = $(this).val();
            if (type == '1') {
                newusr1();
            } else {
                hidedetails1();
            }
        };

        var newusr1 = function() {
            $("#uname1").css("display", "table-row");
            $("#email1").css("display", "table-row");
            $("#phone1").css("display", "table-row");
            $("#pwd12").css("display", "table-row");
            $("#pwd11").css("display", "table-row");
            $("#user_type").val('1');
        };

        var hidedetails1 = function() {
            $("#uname1").css("display", "none");
            $("#email1").css("display", "none");
            $("#phone1").css("display", "none");
            $("#pwd12").css("display", "none");
            $("#pwd11").css("display", "none");
            $("#user_type").val('2');
        };

        var suggestUserFromFirstName = function() {
            if ($('#user_type').val() == '2') {
                var fName = $(this).val();
                var data = {
                    'query_based_customer': true,
                    'from_first_name': true,
                    'firstName': fName
                };
                jQuery.post(glab_ajax_url, data, function(response) {
                    if (response != "") {
                        $(".firstAutoComplete ul").html(response);
                        $(".firstAutoComplete").css({"visibility": "visible", "display": "block"});
                    } else {
                        $(".firstAutoComplete").css({"visibility": "hidden", "display": "none"});
                    }
                });
            }
        };

        var suggestUserFromLastName = function() {
            if ($('#user_type').val() == '2') {
                var lName = $(this).val();
                var fName = $('.firstName').val();
                var data = {
                    'query_based_customer': true,
                    'from_last_name': true,
                    //'firstName': fName,
                    'lastName': lName
                };
                jQuery.post(glab_ajax_url, data, function(response) {
                    if (response != "") {
                        $(".lastAutoComplete ul").html(response);
                        $(".lastAutoComplete").css({"visibility": "visible", "display": "block"});
                    } else {
                        $(".lastAutoComplete").css({"visibility": "hidden", "display": "none"});
                    }
                });
            }
        };

        var setCustomerValues = function() {
            var $currentObj = $(this);
            var customer_id = $currentObj.val();
            $("#first_name").val($currentObj.data('firstname'));
            $("#last_name").val($currentObj.data('lastname'));
            $("#email").val($currentObj.data('email'));
            $("#selected_user").val($currentObj.data('id'));
            $("#phone").val($currentObj.data('phone'));
            $("#cell").val($currentObj.data('cell'));
            $("#work").val($currentObj.data('work'));
            $(".firstAutoComplete").css("display", "none");
        };

        var loadToTimeBasedOnAttr = function() {
            var $currentObj = $(this);
            var current_hour = $currentObj.val();
            var current_hour_array = current_hour.split(":");
            var last_hour = $("#break_st_box option:last-child").attr("value");
            var last_hour_array = last_hour.split(":");
            var options = '<option value="">--select time--</option>';
            current_hour = parseInt(current_hour_array[0]);
            last_hour = parseInt(last_hour_array[0]);
            var tmp_hour = current_hour + 1;
            while (tmp_hour <= last_hour) {
                if (tmp_hour <= 9) {
                    options += '<option value="' + tmp_hour + ':00">' + "0" + tmp_hour + ":00 AM" + '</option>';
                } else if (tmp_hour > 9 && tmp_hour < 12) {
                    options += '<option value="' + tmp_hour + ':00">' + tmp_hour + ":00 AM" + '</option>';
                } else if (tmp_hour == 12) {
                    options += '<option value="' + tmp_hour + ':00">' + tmp_hour + ":00 PM" + '</option>';
                } else if (tmp_hour > 12 && tmp_hour < 22) {
                    options += '<option value="' + tmp_hour + ':00">' + "0" + (tmp_hour - 12) + ":00 PM" + '</option>';
                } else {
                    options += '<option value="' + tmp_hour + ':00">' + (tmp_hour - 12) + ":00 PM" + '</option>';
                }
                tmp_hour++;
            }
            $("#break_to_box").html(options);
        };

        $(document).on("change", settings.user_type, displayUserOption);
        $(document).on("keyup", settings.user_firstname, suggestUserFromFirstName);
        $(document).on("keyup", settings.user_lastname, suggestUserFromLastName);
        $(document).on("change", settings.from_selector, loadToTimeBasedOnAttr);
        $(document).on("click", settings.customer_option, setCustomerValues);
    };
}(jQuery));