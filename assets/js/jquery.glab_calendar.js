(function($) {

    $.fn.glab_calendar = function(options) {
        $.removeCookie('filterDocId');
        var defaults = {
            next_calendar: ".next-calendar",
            prev_calendar: ".prev-calendar",
            next_number: ".next-number",
            prev_number: ".prev-number",
            prac_filter: ".filter-practitioner",
            new_appt_btn: ".btn-new-appt",
            appt_type: "#typeOfAppt",
            new_appt_prac_dropdown: "#doc_name",
            new_appt_service_dropdown: "#statediv2",
            new_appt_date: "#datepick19",
            entire_day_off: "#mark_as_off",
            form_selector: ".add_appt",
            close_btn: "#cl_btn",
            suggested_customer: ".suggested_customer",
            sync_calendar: ".sync-calendar",
            set_pattern: ".set-pattern",
            cal_day: ".calendar-day"

        };
        var settings = $.extend({}, defaults, options);
        var MoveNextCalendar = function(e) {
            // loader will be activated later
            //addLoader();
            var $current_obj = $(this);
            e.preventDefault();
            //var $current_obj = $(this);
            switch ($current_obj.data('view_type')) {
                case 'weekly':
                    weekViewSlider();
                    break;
                case 'daily':
                    dailyViewSlider();
                    break;
                case 'monthly':
                    monthViewSlider();
                    break;
            }

        };
        var MovePrevCalendar = function(e) {
            // loader will be activated later
            //addLoader();
            var $current_obj = $(this);
            e.preventDefault();
            //var $current_obj = $(this);
            switch ($current_obj.data('view_type')) {
                case 'weekly':
                    weekViewSlider();
                    break;
                case 'daily':
                    dailyViewSlider();
                    break;
                case 'monthly':
                    monthViewSlider();
                    break;
            }
        };

        var MoveNextNumber = function(e) {
            // var $current_obj=$(this);
        	//addLoader();
            e.preventDefault();
            var current_view_type = $("#calendar_view_type").html();
            switch (current_view_type.toLowerCase()) {
                case 'weekly':
                    getNextWeek();
                    break;
                case 'daily':
                    getNextDay();
                    break;
                case 'monthly':
                    getNextMonth();
                    break;
            }


        };
        var MovePrevNumber = function(e) {
            // var $current_obj=$(this);
        	//addLoader();
            e.preventDefault();
            var current_view_type = $("#calendar_view_type").html();
            switch (current_view_type.toLowerCase()) {
                case 'weekly':
                    getPreviousWeek();
                    break;
                case 'daily':
                    getPreviousDay();
                    break;
                case 'monthly':
                    getPreviousMonth();
                    break;
            }
        };
        var FilterPractitioner = function(e) {
        	//addLoader();
            var $current_obj = $(this);
            var docId = $current_obj.val();
            //$.cookie('filterDocId', docId);
            var viewType = $("#view_type").val().toLowerCase();
            if (viewType == "monthly") {
                monthViewSlider();
            } else if (viewType == "weekly") {
                weekViewSlider();
            } else {
                dailyViewSlider();
            }
        };

        /*
         name:getPreviousMonth
         purpose:get the previous month of current month
         */
        var getPreviousMonth = function() {
        	//addLoader();
            var month = $("#month").val();
            var year = $("#year").val();
            month = month - 1;
            if (month < 1) {
                month = 12;
                year = year - 1;
            }
            var months = new Array();
            months[1] = "January";
            months[2] = "February";
            months[3] = "March";
            months[4] = "April";
            months[5] = "May";
            months[6] = "June";
            months[7] = "July";
            months[8] = "August";
            months[9] = "September";
            months[10] = "October";
            months[11] = "November";
            months[12] = "December";
            $("#curr_month_year").html(months[month] + " " + year);
            $("#month").val(month);
            $("#year").val(year);
            var viewType = $("#view_type").val();
            if (viewType == "WEEKLY") {
                weekViewSlider();
            } else {
                monthViewSlider();
            }
        }; //end of getPreviousMonth()

        /*
         name:getNextMonth
         purpose:get the next month of current month
         */
        var getNextMonth = function() {
        	//addLoader();
            var month = $("#month").val();
            var year = $("#year").val();
            month = parseInt(month) + 1;
            if (month > 12) {
                month = 1;
                year = parseInt(year) + 1;
            }
            var months = new Array();
            months[1] = "January";
            months[2] = "February";
            months[3] = "March";
            months[4] = "April";
            months[5] = "May";
            months[6] = "June";
            months[7] = "July";
            months[8] = "August";
            months[9] = "September";
            months[10] = "October";
            months[11] = "November";
            months[12] = "December";
            $("#curr_month_year").html(months[month] + " " + year);
            $("#month").val(month);
            $("#year").val(year);
            var viewType = $("#view_type").val();
            if (viewType == "WEEKLY") {
                weekViewSlider();
            } else {
                monthViewSlider();
            }
        };

        /*
         name:getPreviousWeek
         purpose:get the previous week number of current week
         */
        var getPreviousWeek = function() {
        	//addLoader();
            var current_week = $("#week_num").val();
            var current_day = $("#day").val();
            current_week = parseInt(current_week) - 1;
            $("#week_num").val(current_week);
            current_day = parseInt(current_day) - 7;
            $("#day").val(current_day);
            weekViewSlider();
        };	//end of getPreviousWeek
        /*
         name:getNextWeek
         purpose:get the next week number of current week
         */
        var getNextWeek = function() {
        	//addLoader();
            var wnObj = $("#week_num");
            var dayObj = $("#day");
            var current_week = wnObj.val();
            var current_day = dayObj.val();
            current_week = parseInt(current_week) + 1;
            wnObj.val(current_week);
            current_day = parseInt(current_day) + 7;
            dayObj.val(current_day);
            weekViewSlider();
        };	//end of getNextWeek()

        var getPreviousDay = function() {
        	//addLoader();
            var months = new Array();
            months[1] = "January";
            months[2] = "February";
            months[3] = "March";
            months[4] = "April";
            months[5] = "May";
            months[6] = "June";
            months[7] = "July";
            months[8] = "August";
            months[9] = "September";
            months[10] = "October";
            months[11] = "November";
            months[12] = "December";
            currentDay = parseInt($("#day").val());
            currentMonth = parseInt($("#month").val());
            currentYear = parseInt($("#year").val());
            var myDate = new Date(currentMonth + "/" + currentDay + "/" + currentYear);
            myDate.setDate(myDate.getDate() - 1);
            var expected_day = myDate.getDate();
            var expected_month = myDate.getMonth();
/////////////////////////rahul edited/////////////////////////////////////////////////////			
            /*			The current code considers 01 as month january and so on but the getMonth() function gives 0 as january and so on 
             so inorder to keep pace with the given codes condition is used to change the months*/
            if (expected_month == 0) {
                expected_month = 1;
            } else if (expected_month == 1) {
                expected_month = 2;
            } else if (expected_month == 2) {
                expected_month = 3;
            } else if (expected_month == 3) {
                expected_month = 4;
            } else if (expected_month == 4) {
                expected_month = 5;
            } else if (expected_month == 5) {
                expected_month = 6;
            } else if (expected_month == 6) {
                expected_month = 7;
            } else if (expected_month == 7) {
                expected_month = 8;
            } else if (expected_month == 8) {
                expected_month = 9;
            } else if (expected_month == 9) {
                expected_month = 10;
            } else if (expected_month == 10) {
                expected_month = 11;
            } else if (expected_month == 11) {
                expected_month = 12;
            }
            //////////////////////////////////////////////////////////////////////////////////////////////           			
            var expected_year = myDate.getFullYear();
            $("#day").val(expected_day);
            $("#month").val(expected_month);
            $("#year").val(expected_year);
            var dailyLabel = expected_day + "-" + months[expected_month];
            $("#dailyLabel").val(dailyLabel);
            dailyViewSlider();
        };

        var getNextDay = function() {
        	//addLoader();
            var months = new Array();
            months[1] = "January";
            months[2] = "February";
            months[3] = "March";
            months[4] = "April";
            months[5] = "May";
            months[6] = "June";
            months[7] = "July";
            months[8] = "August";
            months[9] = "September";
            months[10] = "October";
            months[11] = "November";
            months[12] = "December";
            currentDay = parseInt($("#day").val());
            var newDate = new Date();
            currentMonth = parseInt($("#month").val());
            currentYear = parseInt($("#year").val());
            var myDate = new Date(currentMonth + "/" + currentDay + "/" + currentYear);
            myDate.setDate(myDate.getDate() + 1);
            var expected_day = myDate.getDate();
            var expected_month = myDate.getMonth();
            if (expected_month == 0) {
                expected_month = 1;
            } else if (expected_month == 1) {
                expected_month = 2;
            } else if (expected_month == 2) {
                expected_month = 3;
            } else if (expected_month == 3) {
                expected_month = 4;
            } else if (expected_month == 4) {
                expected_month = 5;
            } else if (expected_month == 5) {
                expected_month = 6;
            } else if (expected_month == 6) {
                expected_month = 7;
            } else if (expected_month == 7) {
                expected_month = 8;
            } else if (expected_month == 8) {
                expected_month = 9;
            } else if (expected_month == 9) {
                expected_month = 10;
            } else if (expected_month == 10) {
                expected_month = 11;
            } else if (expected_month == 11) {
                expected_month = 12;
            }
            var expected_year = myDate.getFullYear();
            $("#day").val(expected_day);
            $("#month").val(expected_month);
            $("#year").val(expected_year);
            var dailyLabel = expected_day + "-" + months[expected_month];
            $("#dailyLabel").val(dailyLabel);
            dailyViewSlider();
        };

        var weekViewSlider = function(e) {
            var month = $("#month").val();
            var year = $("#year").val();
            var day = $("#day").val();
            var $cvmObj = $("#calendar_view_main");
            var data = {
                'change_calendar_view': true,
                'view_type': 'weekly',
                'selected_prac': $(".filter-practitioner").val(),
                'month': month,
                'year': year,
                'day': day
            };
            addLoader();
            // fetch new calendar view html & replace with old view
            jQuery.post(glab_ajax_url, data, function(response) {

                // replace new html
                if (response) {
                    removeLoader();
                    $cvmObj.css("display", "block");
                    $("#calendar_view_main1").css("display", "none");
                    $('#calendar_view_type').html("WEEKLY");
                    $('#asd').html("");
                    $("#asdf").css("display", "none");
                    $('#view_type').val("WEEKLY");
                    $("#cal_opt").css("display", "none");
                    $("#daily_opt").css("display", "none");
                    var week_number = $("#week_num").val();
                    week_number = week_number + "th week";
                    $("#week_opt_number").html(week_number);
                    $("#week_opt").css("display", "inline");
                    $cvmObj.css({'position': 'absolute', 'width': '97%'});
                    $('#adminmenu').css('z-index', '1000');
                    $cvmObj.animate({right: -1000}).animate({
                        opacity: 'toggle'
                    }, 100, 'linear', function() {
                        $cvmObj.html(response).css('right', '700px');
                        setTimeout(function() {
                            $cvmObj.animate({right: 28})
                        }, 1);
                        $cvmObj.animate({
                            opacity: 'toggle'
                        }, 100, 'linear', function() {
                            //$(".view_left_cursor").html("<a class='prev-calendar' href='#' data-view_type='monthly'> <img src='" + glab_asset_url + "/images/nav-left.png'/></a>");
                            $(".prev-calendar").data('view_type', 'monthly');
                            //$(".view_right_cursor").html("<a class='next-calendar' href='#' data-view_type='daily'> <img src='" + glab_asset_url + "/images/nav-right.png'/></a>");
                            $(".next-calendar").data('view_type', 'daily');
                        });
                    });
                }

            });
        };
        var monthViewSlider = function(e) {
            var month = $("#month").val();
            var year = $("#year").val();
            var cvmObj = $("#calendar_view_main");
            // TODO: Need to create loader
            addLoader();
            var data = {
                'change_calendar_view': true,
                'from_calendar_ajax': true,
                'selected_prac': $(".filter-practitioner").val(),
                'view_type': 'monthly',
                'month': month,
                'year': year,
            };

            // fetch monthly calendar view
            jQuery.post(glab_ajax_url, data, function(response) {
                var msg = response;
                if (msg != "") {
                    removeLoader();
                    $('#calendar_view_type').html("MONTHLY");
                    $('#asd').html("");
                    $("#asdf").css("display", "none");
                    $('#view_type').val("MONTHLY");
                    $("#week_opt").css("display", "none");
                    $("#daily_opt").css("display", "none");
                    var month_num = $("#month").val();
                    var year_num = $("#year").val();
                    var current_html = getMonthName(month_num) + " " + year_num;
                    $("#curr_month_year").html(current_html);
                    $("#cal_opt").css("display", "inline");
                    cvmObj.css({"display": "block", "position": "absolute", "width": "97%"});
                    $("#calendar_view_main1").css("display", "none");
                    $('#adminmenu').css('z-index', '1000');
                    cvmObj.animate({right: -1000}).animate({
                        opacity: 'toggle'
                    }, 100, 'linear', function() {
                        cvmObj.html(msg).css('right', '700px');
                        setTimeout(function() {
                            cvmObj.animate({right: 28})
                        }, 1);
                        cvmObj.animate({
                            opacity: 'toggle'
                        }, 100, 'linear', function() {
                            //$(".view_left_cursor").html("<a href='#' onClick='daily_view_slider(); return false;'> <img src='"+gc_url+"img/nav-left.png'/></a>");
                            $(".prev-calendar").data('view_type', 'daily');
                            //$(".view_right_cursor").html("<a href='#' onClick='week_view_slider(); return false;'> <img src='"+gc_url+"img/nav-right.png'/></a>");
                            $(".next-calendar").data('view_type', 'weekly');
                        });
                    });
                } else {
                }
            });
        };
        var dailyViewSlider = function(e) {
            var month = $("#month").val();
            var year = $("#year").val();
            var day = $("#day").val();
            var $cvmObj = $("#calendar_view_main");

            var data = {
                'change_calendar_view': true,
                'from_calendar_ajax': true,
                'view_type': 'daily',
                'selected_prac': $(".filter-practitioner").val(),
                'selected_month': month,
                'selected_year': year,
                'selected_day': day
            };

            addLoader();
            // fetch new calendar view html & replace with old view
            jQuery.post(glab_ajax_url, data, function(response) {
                // replace new html
                if (response) {
                    removeLoader();
                    $('#calendar_view_type').html("DAILY");
                    $("#asd").css("display", "block");
                    $('#asd').html('<a class="btn-new-appt" data-view_type="add-appointment-form">New Appointment</a>');
                    $('#view_type').val("DAILY");
                    $("#cal_opt").css("display", "none");
                    $("#week_opt").css("display", "none");
                    var week_number = $("#week_num").val();
                    week_number = week_number + "th week";
                    var daily_label1 = $("#dailyLabel").val();
                    var mySplitResult = daily_label1.split("-");
                    var ad = mySplitResult[0];
                    var mo = ad++;
                    var mont = mo;
                    var mon = mySplitResult[1];
                    var daily_label = mont + "-" + mon;
                    $("#daily_opt_number").html(daily_label);
                    $("#daily_opt").css("display", "inline");
                    $cvmObj.css({"display": "block", "position": "absolute", "width": "97%"});
                    $("#calendar_view_main1").css("display", "none");
                    $('#adminmenu').css('z-index', '1000');
                    $cvmObj.animate({right: -1000}).animate({
                        opacity: 'toggle'
                    }, 100, 'linear', function() {
                        $cvmObj.html(response).css('right', '700px');
                        setTimeout(function() {
                            $('#calendar_view_main').animate({right: 28})
                        }, 1);
                        $cvmObj.animate({
                            opacity: 'toggle'
                        }, 100, 'linear', function() {
                            //$(".view_left_cursor").html("<a class='prev-calendar' href='#' data-view_type='weekly'> <img src='" + glab_asset_url + "/images/nav-left.png'/></a>");
                            $(".prev-calendar").data('view_type', 'weekly');
                            //$(".view_right_cursor").html("<a class='next-calendar' href='#' data-view_type='monthly'> <img src='" + glab_asset_url + "/images/nav-right.png'/></a>");
                            $(".next-calendar").data('view_type', 'monthly');
                        });
                    });
                }
            });
        };

        var CreateNewAppt = function(e) {
            e.preventDefault();
            var month = $("#month").val();
            var year = $("#year").val();
            var day = $("#day").val();
            var $cvmObj = $('#calendar_view_main');
            var $cvmObj1 = $('#calendar_view_main1');

            var data = {
                'add_new_appointment': true,
                'selected_month': month,
                'selected_year': year,
                'selected_day': day
            };
            addLoader();
            // file request daily_slider.php

            jQuery.post(glab_ajax_url, data, function(response) {
                if (response) {
                    $("#divAppointment1").html(response);
                    $cvmObj.animate({right: -1000});
                    $cvmObj1.css({"position": "absolute", "width": "97%", "display": "block"});
                    $('#adminmenu').css('z-index', '1000');
                    $cvmObj1.animate({
                        opacity: 'toggle'
                    }, 100, 'linear', function() {
                        $cvmObj1.css('right', '700px');
                        setTimeout(function() {
                            $cvmObj1.animate({right: 28})
                        }, 1);
                        $cvmObj1.animate({
                            opacity: 'toggle'
                        }, 100, 'linear', function() {
                            $cvmObj.css("display", "none");
                        });
                    });
                    $("#divAppointment1").show();
                }
                removeLoader();
            });
        };

        var ChangeApptType = function() {
            var type = $(this).val();
            switch (type) {
                case '1':
                    $(".type2").slideUp();
                    $(".type3").slideUp();
                    $(".type1").slideDown();
                    $(".user-elem1").slideUp();
                    break;
                case '2':
                    $(".type1").slideUp();
                    $(".type3").slideUp();
                    $(".type2").slideDown();
                    break;
                default:
                    $(".type1").slideUp();
                    $(".type2").slideUp();
                    $(".type3").slideDown();
                    updateClinicHour($('#datepick19').val());
            }
        };

        var updateClinicHour = function(date) {

            var date_parts = date.split("/");
            var data = {
                'update_clinic_hour': true,
                'month': date_parts[0],
                'day': date_parts[1],
                'year': date_parts[2]
            };
            jQuery.post(glab_ajax_url, data, function(response) {
                $("#break_st_box").html(response);
            });
        };

        var ModifyPracAttr = function() {
            var value = $(this).val();
            var is_prac_type_app = false;
            if ($("#typeOfAppt").length) {
                var app_type = $("#typeOfAppt").val();
                is_prac_type_app = (app_type == '2') ? true : false;
            }
			addLoader();
            var filter_type = (is_prac_type_app) ? "date_based_slot" : "only_services";
            var serialize_data = "id=" + value + "&filter_type=" + filter_type + "&new_app_prac_filter=" + '1';
            if (is_prac_type_app) {
                var selected_date = $("#datepick19").val();
                var date_parts = selected_date.split("/");
                /*var data = {
                 'filter_type': filter_type,
                 'new_app_prac_filter': true,
                 'id': value,
                 'month': date_parts[0],
                 'day': date_parts[1],
                 'year': date_parts[2]
                 };*/
                serialize_data += "&month=" + date_parts[0] + "&day=" + date_parts[1] + "&year=" + date_parts[2];

            }
            $.ajax({
                type: "POST",
                url: glab_ajax_url,
                data: serialize_data,
                success: function(msg) {
                    //alert(msg);
					removeLoader();
                    if (msg != "") {
                        if (is_prac_type_app) {
                            $("#break_st_box").html(msg);
                        } else {
                            $("#statediv2").html(msg);
                        }

                    }
                }
            });
        };

        var loadAvailableHours1 = function() {
            var $current_obj = $(this);
            var service = $current_obj.val();
            $("#shwtime").show();
            var practitioner = $('.app_practitioners1').val();
            var app_date = $('.samplePicker1').val();
            var data = {
                'load_available_slot': true,
                'practitioners': practitioner,
                'app_date': app_date,
                'services': service
            };
			addLoader();
            jQuery.post(glab_ajax_url, data, function(response) {
				removeLoader();
                $("#break_st_box").html(response);
                if (response != "") {
                    $("#a_hour_chk").html(response);
                } else {
                    var errorHtml = "<span>Hours are not currently available for this selection.</span>";
                    $('.app_error').html(errorHtml).css('display', 'block');
                    $("#shwtime").hide();
                }
            });
        };

        var showDatePick = function() {
            $("#datepick19").datepicker().datepicker("show");
        };

        var runDateValidation = function() {
            var date = $("#datepick19").val();
            var app_type = $("#typeOfAppt").val();
            var is_prac_type_app = (app_type == '2') ? true : false;
            var is_clinic_type_app = (app_type == '3') ? true : false;
            if (is_prac_type_app) {
                updatePractitionerHour(date);
            } else if (is_clinic_type_app) {
                updateClinicHour(date);
            } else {
                updatePractitionerHourWithFilter(date);
            }
        }

        var updatePractitionerHour = function(date) {
            var doc_id = $("#doc_name").val();
            var date_parts = date.split("/");
            var data = {
                'new_app_prac_filter': true,
                'filter_type': 'date_based_slot',
                'id': doc_id,
                'month': date_parts[0],
                'day': date_parts[1],
                'year': date_parts[2]
            };
			addLoader();
            jQuery.post(glab_ajax_url, data, function(response) {
				removeLoader();
                $("#break_st_box").html(response);
            });
        };



        var updatePractitionerHourWithFilter = function(date) {
            if (date == "") {
                $("#shwtime").hide();
            } else {
                $("#shwtime").show();

                var practitioner = $('.app_practitioners1').val();
                var app_date = date;
                var service = $('.app_treatments1').val();
                var data = {
                    'load_available_slot': true,
                    'practitioners': practitioner,
                    'app_date': app_date,
                    'services': service
                };
				addLoader();
                jQuery.post(glab_ajax_url, data, function(response) {
					removeLoader();
                    var msg = response.trim();
                    if (msg != "") {
                        if (msg == "WrongDr") {
                            var errorHtml = "<span>Doctor not available for this treatment</span>";
                            $('.app_error').html(errorHtml);
                            $('.app_error').css('display', 'block');
                            $("#shwtime").hide();
                        } else if (msg == "hrs") {
                            var errorHtml = "<span>Hours are not currently available for that Doctor.</span>";
                            $('.app_error').html(errorHtml);
                            $('.app_error').css('display', 'block');
                        } else if (msg == "hrdr") {
                            var errorHtml = "<span>Hours are not currently available for that Doctor.</span>";
                            $('.app_error').html(errorHtml);
                            $('.app_error').css('display', 'block');
                            $("#shwtime").hide();
                        } else if (msg == "daypr") {
                            var errorHtml = "<span>In this day Your selected Practitioner is not available, please choose another day.</span>";
                            $('.app_error').html(errorHtml);
                            $('.app_error').css('display', 'block');
                            $("#shwtime").hide();
                        } else if (msg == "cloff") {
                            var errorHtml = "<span>Sorry this day clinic is off, please choose another day.</span>";
                            $('.app_error').html(errorHtml);
                            $('.app_error').css('display', 'block');
                            $("#shwtime").hide();
                        } else {
                            $('.app_error').css('display', 'none');
                        }
                        $(".app_hour1").html(msg);
                    }
                });
            }
        };

        var changeOffValue = function() {
            if (this.checked) {
                $("#off_flag").val("YES");
            } else {
                $("#off_flag").val("");
            }
        };

        var apptSubmit = function(e) {
            e.preventDefault();
            var $currentObj = $(this);
            appointmentSubmit($currentObj);

        };

        var savePattern = function() {
            $("head").append("<script  src='" + glab_asset_url + "/js/jquery.alerts.js'  /></script>");
            $("head").append("<link  href='" + glab_asset_url + "/css/jquery.alerts.css' type='text/css' rel='stylesheet' />");

            var app_type = document.getElementById("typeOfAppt").value;
            switch (app_type) {
                case '2':
                    breakPatternPractitioner();
                    break;
                case '3':
                    blockPatternClinic();
                    break;
                default:
                    submitPatternRegularApp();
            }
        };

        var breakPatternPractitioner = function() {

        };

        var blockPatternClinic = function() {

        };

        var submitPatternRegularApp = function() {

        };

        var appointmentSubmit = function($currentObj) {
            $("head").append("<script  src='" + glab_asset_url + "/js/jquery.alerts.js'  /></script>");
            $("head").append("<link  href='" + glab_asset_url + "/css/jquery.alerts.css' type='text/css' rel='stylesheet' />");
            var app_type = document.getElementById("typeOfAppt").value;
            switch (app_type) {
                case '2':
                    breakPractitioner($currentObj);
                    break;
                case '3':
                    blockClinic($currentObj);
                    break;
                default:
                    submitRegularApp($currentObj);
            }
        };

        var breakPractitioner = function($currentObj) {
            var is_patterned = $currentObj.data('target-pattern');
            var practitioner = $("#doc_name").val();
            var date = $("#datepick19").val();
            var break_from = $("#break_st_box").val();
            var break_to = $("#break_to_box").val();
            var off_flag = $("#off_flag").val();
            var target_pattern = (is_patterned == '1') ? $("#patternId").val() : null;
            var app_id = '';
            if (document.getElementById("app_id"))
                app_id = document.getElementById("app_id").value;
            var ad = '';
            if (practitioner == "") {
                jAlert('Please Select a practitioner ...',
                        'Validation Response');
                $('#popup_content')
                        .append(
                                '<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                return false;
            } else if (off_flag != "YES" && (break_from == "" || break_to == "")) {
                jAlert('Please Select an interval.', 'Validation Response');
                $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                return false;
            }
            if (document.getElementById('calendar_view_type'))
                ad = document.getElementById('calendar_view_type').innerHTML;

            /* new post request for break_practitioner.php */
            var data = {
                'break_practitioner': true,
                'off_flag': off_flag,
                'date': date,
                'practitioner': practitioner,
                'break_from': break_from,
                'break_to': break_to,
                'app_id': app_id,
                'patternId': target_pattern
            };
			addLoader();
            jQuery.post(glab_ajax_url, data, function(response) {
				removeLoader();
                var msg = response;
                jAlert('practitioner break added successfully, redirecting...',
                        'Response Message');
                msg = msg.trim();
                document.getElementById('a_hour_chk').innerHTML = msg;
                setTimeout(function() {
                    $("#popup_overlay").remove();
                    $("#popup_container").remove();

                    if (ad == "DAILY") {
                        dailyViewSlider();
                    } else if (ad == "WEEKLY") {
                        weekViewSlider();
                    } else {
                        location.reload();
                    }
                }, 2000);
            });
            /* end of new post request */
        };

        var blockClinic = function($currentObj) {
            var is_patterned = $currentObj.data('target-pattern');
            var date = document.getElementById("datepick19").value;
            var break_from = document.getElementById("break_st_box").value;
            var break_to = document.getElementById("break_to_box").value;
            if (document.getElementById("off_flag")) {
                var off_flag = document.getElementById("off_flag").value;
            } else {
                var off_flag = '';
            }
            var target_pattern = (is_patterned == '1') ? $("#patternId").val() : null;
            var app_id = '';
            if (document.getElementById("app_id"))
                app_id = document.getElementById("app_id").value;
            var ad = '';
            if (document.getElementById('calendar_view_type'))
                ad = document.getElementById('calendar_view_type').innerHTML;

            if (off_flag != "YES" && (break_from == "" || break_to == "")) {
                jAlert('Please Select an interval.', 'Validation Response');
                $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                return false;
            }
            /* block_clinic.php call */
            var data = {
                'block_clinic': true,
                'off_flag': off_flag,
                'date': date,
                'break_from': break_from,
                'break_to': break_to,
                'app_id': app_id,
                'patternId': target_pattern
            };
			addLoader();
            jQuery.post(glab_ajax_url, data, function(response) {
				removeLoader();
                var msg = response;
                jAlert('Block of clinic hours added successfully, redirecting...', 'Response Message');
                msg = msg.trim();
                document.getElementById('a_hour_chk').innerHTML = msg;
                setTimeout(function() {
                    $("#popup_overlay").remove();
                    $("#popup_container").remove();

                    if (ad == "DAILY") {
                        dailyViewSlider();
                    } else if (ad == "WEEKLY") {
                        weekViewSlider();
                    } else {
                        location.reload();
                    }
                }, 2000);
            });
            /* end of block_clinic.php call */
        };

        var submitRegularApp = function($currentObj) {
            var is_patterned = $currentObj.data('target-pattern');
            var docName = document.getElementById("doc_name").value;
            var treatName = document.getElementById("statediv2").value;
            var time = document.getElementById("a_hour_chk").value;
            var firstName = document.getElementById("app_first_name1").value;
            var uid = document.getElementById("user_id").value;
            var lastName = document.getElementById("app_last_name2").value;
            var username = document.getElementById("cus_username1").value;
            var userpass = document.getElementById("cus_password12").value;
            var userpass1 = document.getElementById("cus_password11").value;
            var cus_email = document.getElementById("cus_email1").value;
            var cus_phone = document.getElementById("cus_phone1").value;
            if (document.getElementById("off_flag")) {
                var off_flag = document.getElementById("off_flag").value;
            } else {
                var off_flag = '';
            }
            var target_pattern = (is_patterned == '1') ? $("#patternId").val() : null;
            var app_id = '';
            if (document.getElementById("app_id"))
                app_id = document.getElementById("app_id").value;

            var request_type = '';
            if (app_id) {
                request_type = "edit_appointment";
            } else {
                request_type = "add_appointment";
            }
            var errorHtml = "";
            var duration = parseInt(document.getElementById("usrval").value);
            if (off_flag == "YES") {
                var date = document.getElementById("datepick19").value;
                var data = {
                    'submit_regular_app': true,
                    'request_type': request_type,
                    'off_flag': off_flag,
                    'date': date,
                    'patternId': target_pattern
                };
				addLoader();
                jQuery.post(glab_ajax_url, data, function(response) {
					removeLoader();
                    var msg = response;
                    jAlert('Mark the day as off redirecting...', 'Response Message');
                    msg = msg.trim();
                    document.getElementById('a_hour_chk').innerHTML = msg;
                    setTimeout(function() {
                        $("#popup_overlay").remove();
                        $("#popup_container").remove();
                        if (ad == "DAILY") {
                            dailyViewSlider();
                        } else if (ad == "WEEKLY") {
                            weekViewSlider();
                        } else {
                            refreshSlider();
                        }
                    }, 2000);
                });

            } else if (duration == 2)
            {
                if (docName == "")
                {
                    jAlert('Please Select a practitioner.', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (treatName == "")
                {
                    jAlert('Please select a treatment', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (time == "----Select Time----")
                {
                    jAlert('Time should not be blank.', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }

                else if (firstName == "")
                {
                    jAlert('First Name should not be blank.', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }


                else if (lastName == "")
                {
                    jAlert('Last Name should not be blank', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');

                }
                else if (errorHtml == "")
                {
                    var doc = document.getElementById("doc_name").value;
                    var treat = document.getElementById("statediv2").value;
                    var date = document.getElementById("datepick19").value;
                    var slelected_time = document.getElementById("a_hour_chk").value;
                    var reminder = document.getElementById("app_reminder1").value;
                    var user_type = duration;
                    if (document.getElementById('calendar_view_type'))
                        var ad = document.getElementById('calendar_view_type').innerHTML;
                    if (user_type == 1)
                    {

                        var data = {
                            'submit_regular_app': true,
                            'request_type': request_type,
                            'doc_name': doc,
                            'treat': treat,
                            'date': date,
                            'slelected_time': slelected_time,
                            'reminder': reminder,
                            'user_type': user_type,
                            'firstName': firstName,
                            'lastName': lastName,
                            'username': username,
                            'userpass': userpass,
                            'cus_email': cus_email,
                            'cus_phone': cus_phone,
                            'app_id': app_id,
                            'off_flag': off_flag,
                            'patternId': target_pattern
                        };
						addLoader();
                        jQuery.post(glab_ajax_url, data, function(response) {
							removeLoader();
                            jAlert('Appointment Successfully Added redirecting...', 'Response Message');
                            msg = response.trim();
                            document.getElementById('a_hour_chk').innerHTML = msg;
                            setTimeout(function() {
                                $("#popup_overlay").remove();
                                $("#popup_container").remove();

                                if (ad == "DAILY") {
                                    dailyViewSlider();
                                } else if (ad == "WEEKLY") {
                                    weekViewSlider();
                                } else {
                                    refreshSlider();
                                }
                            }, 2000);
                        });

                    } else {
                        var data = {
                            'submit_regular_app': true,
                            'request_type': request_type,
                            'doc_name': doc,
                            'treat': treat,
                            'date': date,
                            'slelected_time': slelected_time,
                            'reminder': reminder,
                            'user_type': user_type,
                            'firstName': firstName,
                            'uid': uid,
                            'lastName': lastName,
                            'app_id': app_id,
                            'off_flag': off_flag,
                            'patternId': target_pattern
                        };
						addLoader();
                        jQuery.post(glab_ajax_url, data, function(response) {
							removeLoader();
                            var msg = response.trim();
                            document.getElementById('a_hour_chk').innerHTML = msg;
                            if (ad == "DAILY") {
                                dailyViewSlider();
                            } else if (ad == "WEEKLY") {
                                weekViewSlider();
                            } else {
                                refreshSlider();
                            }
                        });

                    }
                }

            }
            else
            {
                if (firstName == "")
                {
                    jAlert('First Name should not be blank.', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (lastName == "")
                {
                    jAlert('Last Name should not be blank', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (username == "")
                {
                    jAlert('User Name should not be blank', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (userpass == "")
                {
                    jAlert('User password should not be blank', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (userpass1 == "")
                {
                    jAlert('confirm User password should not be blank', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (userpass != userpass1)
                {
                    jAlert('password should be same', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (userpass == "")
                {
                    jAlert('User password should not be blank', 'Validation Response');
                    ('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (cus_email == "")
                {
                    jAlert('mail should not be blank', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }
                else if (cus_phone == "")
                {
                    jAlert('phone should not be blank', 'Validation Response');
                    $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                }

                else if (errorHtml == "")
                {
                    var doc = document.getElementById("doc_name").value;
                    var treat = document.getElementById("statediv2").value;
                    var date = document.getElementById("datepick19").value;
                    var slelected_time = document.getElementById("a_hour_chk").value;
                    var reminder = document.getElementById("app_reminder1").value;
                    var user_type = duration;
                    var ad = document.getElementById('calendar_view_type').innerHTML;
                    if (user_type == 1)
                    {
                        var data = {
                            'submit_regular_app': true,
                            'request_type': request_type,
                            'doc_name': doc,
                            'treat': treat,
                            'date': date,
                            'slelected_time': slelected_time,
                            'reminder': reminder,
                            'user_type': user_type,
                            'firstName': firstName,
                            'lastName': lastName,
                            'username': username,
                            'userpass': userpass,
                            'cus_email': cus_email,
                            'cus_phone': cus_phone,
                            'app_id': app_id,
                            'off_flag': off_flag,
                            'patternId': target_pattern
                        };
						addLoader();
                        jQuery.post(glab_ajax_url, data, function(response) {
							removeLoader();
                            var msg = response;
                            if (msg == "")
                            {

                                jAlert('Room is not available . please choose another Time', 'Validation Response');
                                $('#popup_content').append('<div id="popup_panel"><input type="button" id="popup_ok" onclick="close_popupdiv()" value="&nbsp;OK&nbsp;"></div>');
                            }
                            else {

                                jAlert('Appointment Successfully Added redirecting...', 'Response Message');
                                msg = msg.trim();
                                document.getElementById('a_hour_chk').innerHTML = msg;
                                setTimeout(function() {
                                    $("#popup_overlay").remove();
                                    $("#popup_container").remove();

                                    if (ad == "DAILY") {
                                        dailyViewSlider();
                                    } else if (ad == "WEEKLY") {
                                        weekViewSlider();
                                    } else {
                                        refreshSlider();
                                    }
                                }, 2000);


                            }
                        });

                    } else {
                        var data = {
                            'submit_regular_app': true,
                            'request_type': request_type,
                            'doc_name': doc,
                            'treat': treat,
                            'date': date,
                            'slelected_time': slelected_time,
                            'reminder': reminder,
                            'user_type': user_type,
                            'firstName': firstName,
                            'lastName': lastName,
                            'uid': uid,
                            'app_id': app_id,
                            'off_flag': off_flag,
                            'patternId': target_pattern
                        };
						addLoader();
                        jQuery.post(glab_ajax_url, data, function(response) {
							removeLoader();
                            var msg = response.trim();
                            document.getElementById('a_hour_chk').innerHTML = msg;
                            if (ad == "DAILY") {
                                dailyViewSlider();
                            } else if (ad == "WEEKLY") {
                                weekViewSlider();
                            } else {
                                refreshSlider();
                            }
                        });

                    }
                }


            }
        };

        var refreshSlider = function() {
            // load monthly calendar from month_ajax.php
            var month = $("#month").val();
            var year = $("#year").val();
            var cvmObj = $("#calendar_view_main");
            var data = {
                'refresh_calendar': true,
                'change_calendar_view': true,
                'from_calendar_ajax': true,
                'view_type': 'monthly',
                'month': month,
                'year': year
            };
			addLoader();
            jQuery.post(glab_ajax_url, data, function(response) {
				removeLoader();
                var msg = response;
                if (msg != "") {
                    cvmObj.css({
                        "display": "block",
                        "position": "absolute",
                        "width": "97%"
                    });
                    $("#calendar_view_main1").css("display",
                            "none");
                    $('#calendar_view_type').html("MONTHLY");
                    $('#asd').html('');
                    $("#asdf").css("display", "none");
                    $('#view_type').val("MONTHLY");
                    $("#week_opt").css("display", "none");
                    $("#daily_opt").css("display", "none");
                    var month_num = $("#month").val();
                    var year_num = $("#year").val();
                    var current_html = getMonthName(month_num)
                            + " " + year_num;
                    $("#curr_month_year").html(current_html);
                    $("#cal_opt").css("display", "inline");

                    $('#adminmenu').css('z-index', '1000');
                    cvmObj.animate({right: -1000})
                            .animate({opacity: 'toggle'},
                            100,
                                    'linear',
                                    function() {
                                        cvmObj.html(msg).css('right', '700px');
                                        setTimeout(function() {
                                            cvmObj.animate({
                                                right: 28
                                            })
                                        }, 1);
                                        cvmObj.animate({opacity: 'toggle'},
                                        100,
                                                'linear',
                                                function() {
                                                    $(".view_left_cursor").html(
                                                            "<a href='#' onClick='dailyViewSlider(); return false;'> <img src='"
                                                            + glab_asset_url
                                                            + "/images/nav-left.png'/></a>");
                                                    $(".view_right_cursor").html(
                                                            "<a href='#' onClick='weekViewSlider(); return false;'> <img src='"
                                                            + glab_asset_url
                                                            + "/images/nav-right.png'/></a>");
                                                });
                                    });
                } else {
                }
            });
        };

        var getMonthName = function(num) {
            var months = new Array();
            months[1] = "January";
            months[2] = "February";
            months[3] = "March";
            months[4] = "April";
            months[5] = "May";
            months[6] = "June";
            months[7] = "July";
            months[8] = "August";
            months[9] = "September";
            months[10] = "October";
            months[11] = "November";
            months[12] = "December";
            return months[num];
        };

        var closeFrm = function() {
            var targetPatternStatus = $(this).data('target-pattern');
            if (targetPatternStatus == '1') {
                $("#patternFrm").html('').slideUp();
            } else {
                refreshSlider();
            }
        };

        var liClick = function() {
            var $currentObj = $(this);
            $(".firstName").val($currentObj.data('firstname'));
            $(".lastName").val($currentObj.data('lastname'));
            $(".email").val($currentObj.data('email'));
            $(".userId").val($currentObj.data('id'));
            $(".firstAutoComplete").css("display", "none");
            $(".lastAutoComplete").css("display", "none");
        };

        var syncEvents = function(e) {
            e.preventDefault();
            //$(".sync_loader").show();
            var $currentObj = $(this);
            var targetUrl = $currentObj.attr("href");
			addLoader();
            $.ajax({
                url: targetUrl,
                type: "POST",
                dataType: "html",
                success: function(response, textStatus, XMLHttpReques) {
                    $currentObj.hide();
                    $currentObj.prev().html('complete').css('color', 'red');
                    //$(".sync_loader").hide();
                    return false;
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert(textStatus);
                },
                complete: function() {
                    //$(".sync_loader").hide();
					removeLoader();
                }
            });
        };

        var loadPatternForm = function(e) {
            e.preventDefault();
            var $obj = $(this);
            var targetId = $obj.attr('data-id');
            var day = $obj.attr('data-day');
            var month = $obj.attr('data-month');
            var year = $obj.attr('data-year');
			addLoader();
            $.ajax({
                type: "POST",
                dataType: "html",
                url: glab_ajax_url, //+ "pattern_daily_slider.php",
                data: "targetPattern=" + targetId
                        + "&selected_day=" + day
                        + "&selected_month=" + month
                        + "&selected_year=" + year
                        + "&load_pattern_form=true",
                success: function(msg) {
					removeLoader();
                    if (msg != "") {
                        //$("#patternFrm").prev().fadeOut().prev().fadeOut();
                        $(".form-wrapper").fadeOut();
                        $("#patternFrm").html(msg).fadeIn();
                    } else {
                        alert("Failed\nPlease try again")
                    }
                }
            });
        };
        
        var monthlyClickHandler = function(){
        	var selected_day = $(this).attr("id");
        	$("#day").val(selected_day);
        	var current_label_array=$("#dailyLabel").val().split('-');
        	var formatted_day=selected_day;
        	if(selected_day.length<2)
        		formatted_day='0'+selected_day;
        	$("#dailyLabel").val(formatted_day+'-'+current_label_array[1]);
        	dailyViewSlider();
        };

        $(document).on("click", settings.next_calendar, MoveNextCalendar);
        $(document).on("click", settings.prev_calendar, MovePrevCalendar);
        $(document).on("click", settings.next_number, MoveNextNumber);
        $(document).on("click", settings.prev_number, MovePrevNumber);
        $(document).on("click", settings.new_appt_btn, CreateNewAppt);
        $(document).on("change", settings.prac_filter, FilterPractitioner);
        $(document).on("change", settings.appt_type, ChangeApptType);
        $(document).on("change", settings.new_appt_prac_dropdown, ModifyPracAttr);
        $(document).on("change", settings.new_appt_service_dropdown, loadAvailableHours1);
        $(document).on("click", settings.new_appt_date, showDatePick);
        $(document).on("change", settings.new_appt_date, runDateValidation);
        $(document).on("change", settings.entire_day_off, changeOffValue);
        $(document).on("submit", settings.form_selector, apptSubmit);
        $(document).on("click", settings.close_btn, closeFrm);
        $(document).on("click", settings.suggested_customer, liClick);
        $(document).on("click", settings.sync_calendar, syncEvents);
        $(document).on("click", settings.set_pattern, loadPatternForm);
        $(document).on("click", settings.cal_day, monthlyClickHandler);

    };
}(jQuery));

function showAppConfrmPage(day, month, year, time) {
    var $ = jQuery;
    addLoader();

    $.ajax({
        type: "POST",
        url: glab_ajax_url, // "showAppConfirmation.php",
        data: "selected_month=" + month + "&selected_year=" + year + "&selected_day_bunch=" + parseInt(day) + "&time=" + time + "&showAppConfirmation=true",
        success: function(msg) {
            if (msg != "")
            {
                removeLoader();
                document.getElementById("divAppointment1").innerHTML = msg;
            }
        }
    });

    $("#calendar_view_main").css("display", "none");
    $("#calendar_view_main1").css("display", "block");
    $('#calendar_view_main1').css('position', 'absolute');
    $('#calendar_view_main1').css('width', '82%');
    $('#divAppointment1').show();
    $('#adminmenu').css('z-index', '1000');
    $('#calendar_view_main1').animate({right: -1000});

    $('#calendar_view_main1').animate({
        opacity: 'toggle'
    }, 100, 'linear', function()
    {
        $('#calendar_view_main1').css('right', '700px');
        setTimeout(function() {
            $('#calendar_view_main1').animate({right: 54})
        }, 1);

        $('#calendar_view_main1').animate({
            opacity: 'toggle'
        }, 100, 'linear', function() {
        });
    });
}

function grabPatientInfo(waiting_id) {
    var $ = jQuery;
    var days = $("#w_day").val();
    var months = $("#w_month").val();
    var years = $("#w_year").val();
    var diff = "-";

    if (waiting_id != '') {
        var fulldate = years.concat(diff).concat(months).concat(diff).concat(days);
        //alert(fulldate);
        addLoader(); // load waiting div for fancy manupulation
        $.ajax({
            dataType: 'text',
            type: "POST",
            data: "waiting_id=" + waiting_id + "&exp_day=" + $("#expected_week_day").val() + "&waitingInfo=true",
            success: function(string) {
                data = $.parseJSON(string);

                $("#w_paractitioner").html(data.first_name + ' ' + data.last_name);
                $("#w_treatments").html(data.service);
                var contact_with = (data.app_reminder == '1') ? "Email" : "Phone";
                //alert(contact_with);
                $("#w_contact_with").html(contact_with);
                $("#w_selected_patient").val(data.patient);
                $("#w_selected_firstname").val(data.patient_fname);
                $("#w_selected_lastname").val(data.patient_lname);
                $("#w_selected_doctor").val(data.practitioner);
                $("#w_selected_treatment").val(data.treatId);
                $("#w_app_reminder").val(data.app_reminder);
                $("#w_id").val(waiting_id);
                setApptTime(data.practitioner, data.treatId, data.waiting_duration, fulldate);
                /*$.ajax({
                 dataType: 'text',
                 type: "POST",
                 data: "doc=" + data.practitioner + "&treat=" + data.treatId + "&duration=" + data.waiting_duration + "&fullydate=" + fulldate+"&waitTime=true",
                 success: function(string) {
                 alert('here');
                 $("#appointment_time").html(string);
                 },
                 url: glab_ajax_url // + "calender-files/waittime.php"
                 });*/
                removeLoader(); //remove loading div from DOM
            },
            url: glab_ajax_url // + "calender-files/waitingInfo.php"
        });
    }
    else {
        $("#w_paractitioner").html('');
        $("#w_treatments").html('');
        //var contact_with=(data.app_reminder=='1')?"Email":"Phone";
        //alert(contact_with);
        $("#w_contact_with").html('');
        $("#w_selected_patient").val('');
        $("#w_selected_firstname").val('');
        $("#w_selected_lastname").val('');
        $("#w_selected_doctor").val('');
        $("#w_selected_treatment").val('');
        $("#w_app_reminder").val('');
        $("#w_id").val('');

        alert("please Choose patient !");
    }
}

function setApptTime(doc, treat, duration, fullydate) {
    var $ = jQuery;
    $.ajax({
        dataType: 'text',
        type: "POST",
        data: "doc=" + doc + "&treat=" + treat + "&duration=" + duration + "&fullydate=" + fullydate + "&waitTime=true",
        success: function(string) {
            $("#appointment_time").html(string);
        },
        url: glab_ajax_url // + "calender-files/waittime.php"
    });
}

function Appointment_view_slider12(day, month, year, time, app_id) {
    var $ = jQuery;
	addLoader();
    $.ajax({
        type: "POST",
        url: glab_ajax_url, //+target_url,daily_slider.php or edit_daily_slider.php
        data: "selected_month=" + month + "&selected_year=" + year + "&selected_day=" + day + "&time=" + time + "&app_id=" + app_id + "&add_new_appointment=true",
        success: function(msg) {
			removeLoader();
            if (msg != "") {
                document.getElementById("divAppointment1").innerHTML = msg;
            }
        }
    });
    var cvmObj1 = $("#calendar_view_main1");
    $("#calendar_view_main").css("display", "none");
    cvmObj1.css({"display": "block", "position": "absolute", "width": "82%"});
    $('#adminmenu').css('z-index', '1000');
    cvmObj1.animate({right: -1000}).animate({
        opacity: 'toggle'
    }, 100, 'linear', function() {
        cvmObj1.css('right', '700px');
        setTimeout(function() {
            cvmObj1.animate({right: 54})
        }, 1);
        cvmObj1.animate({
            opacity: 'toggle'
        }, 100, 'linear', function() {
        });
    });
    $("#divAppointment1").show();
}

function finalize_appointment() {
    var $ = jQuery;
    addLoader();

    var app_id = $("#w_id").val();
    if (!isNaN(app_id)) {
        //alert("hiiiii");
        if (true) {
            $.ajax({
                type: "POST",
                url: glab_ajax_url, //+"finalizeApp.php",
                data: "waiting_id=" + $("#w_id").val() + "&app_month=" + $('#w_month').val() + '&app_day=' + $('#w_day').val() + '&app_year=' + $('#w_year').val() + "&app_time=" + $('#appointment_time').val() + "&finalizeWaitingApp=true",
                success: function(msg) {
					removeLoader();
                    alert(msg);
                    window.location.reload();
                }
            });
        } else {
            alert("Please select a patient first.");
        }
    } else {
    }
    //removeLoader();
    return false;
}

function showAppDetails(id, event) {
	if(event)
		event.stopPropagation();
    var $ = jQuery;
    if (!isNaN(id)) {
		addLoader();
        $.ajax({
            type: "POST",
            url: glab_ajax_url, // + "showAppDetails.php",
            data: "id=" + id + "&showAppDetails=true",
            success: function(msg) {
                //close_tb();
				removeLoader();
                document.getElementById("divAppointment1").innerHTML = msg;
                document.getElementById("slide_view_id").value = id;
            }
        });
        $("#calendar_view_main").css("display", "none");
        /*$("#asd").css("display","none");
         $("#asdf").css("display","block");*/
        var cvMainObj = $("#calendar_view_main1");
        cvMainObj.css({'display': 'block', 'position': 'absolute', 'width': '82%'});
        $("#adminmenu").css("z-index", "1000");
        cvMainObj.animate({right: -1000}).animate({
            opacity: "toggle"
        }, 100, "linear", function() {
            cvMainObj.css("right", "700px");
            setTimeout(function() {
                cvMainObj.animate({right: 54})
            }, 1);
            cvMainObj.animate({
                opacity: "toggle"
            }, 100, "linear", function() {
            });
        });
        $("#divAppointment1").show();
    }
}

function delete_appointment(id) {
    var $ = jQuery;
    if(confirm("Are you sure to cancel?")===false)
        return;
	addLoader();
    $.ajax({
        type: "POST",
        url: glab_ajax_url, //+"deleteApp.php",
        data: "id=" + id+"&deleteApp=true",
        success: function(msg) {
			removeLoader();
            alert(msg);
            location.reload(true);
        }
    });
}

function close_appDetails() {
    location.reload(true);
}

