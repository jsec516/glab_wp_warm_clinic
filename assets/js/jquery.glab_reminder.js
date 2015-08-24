(function($) {
    $.fn.glab_reminder = function(options) {

        var defaults = {
            reminder_selection: "select[name='reminder_type']",
            elem_selector: ".elem-event",
            remove_selector: ".remove-elem",
            file_selector: "#att_file_play",
            call_reminder_submit: ".call-submit-btn",
            test_call_btn: "#make_call",
            prac_selector: "#practitioner",
            cancel_upload_selector: "#cancel_upload",
            delete_file_selector: ".delete_file"
        };

        var settings = $.extend({}, defaults, options);

        var loadReminderType = function() {
            var selected_type = $(this).val();
            if (selected_type == '1') {
                location.reload();
            }
            // fetch reminder html vaia ajax call
            var data = {
                'load_reminder_html': true,
                'reminder_type': selected_type,
            };
            jQuery.post(glab_ajax_url, data, function(response) {
                $(".reminder-table").remove();
                $("#reminderTypeForm").append(response);
            });

        };

        var addElementTag = function() {
            var $currentObj = $(this);
            var type = $currentObj.data('event_type');
            //alert(type);
            switch (type) {
                case 'half_sec_pause':
                    var pause_number_5 = parseInt(document.getElementById("5pauseNumber").value) + 1;
                    var pause_content_5 = "<tr id=\"pause5_" + pause_number_5 + "\"><td><div style=\"position:relative;\"><input type=\"text\" style=\"padding-right:30px;\" id=\"pause5_text_" + pause_number_5 + "\" name=\"format_text[]\" value=\"&lt;0.5 second pause&gt;\" onfocus=\"if (this.value == '&lt;0.5 second pause&gt;') {this.value = '';}\" onblur=\"if (this.value == '') {this.value = '&lt;0.5 second pause&gt;';}\" class=\"call_box\"><div class='remove-elem' data-target_type='regular' data-target_id='pause5_" + pause_number_5 + "'  id=\"close_file_text\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
                    $("#call_content").append(pause_content_5);
                    document.getElementById('5pauseNumber').value = pause_number_5;
                    addItemOnSequence('pause5_' + pause_number_5);
                    break;
                case '1_sec_pause':
                    var pause_number = parseInt(document.getElementById("pauseNumber").value) + 1;
                    var pause_content = "<tr id=\"pause_" + pause_number + "\"><td><div style=\"position:relative;\"><input type=\"text\" style=\"padding-right:30px;\" id=\"pause_text_" + pause_number + "\" name=\"format_text[]\" value=\"&lt;1 second pause&gt;\" onfocus=\"if (this.value == '&lt;1 second pause&gt;') {this.value = '';}\" onblur=\"if (this.value == '') {this.value = '&lt;1 second pause&gt;';}\" class=\"call_box\"><div class='remove-elem' data-target_type='regular' data-target_id='pause_" + pause_number + "' id=\"close_file_text\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
                    $("#call_content").append(pause_content);
                    document.getElementById('pauseNumber').value = pause_number;
                    addItemOnSequence('pause_' + pause_number);
                    break;
                case 'line':
                    var line_number = parseInt(document.getElementById('lineNumber').value) + 1;
                    var line_content = "<tr id='line_" + line_number + "'><td><div style='position:relative;'><input type='text' style='padding-right:30px;' id='msg_for_text_" + line_number + "' name='format_text[]' value='this message is for' class='call_box' onfocus=\"if (this.value == 'this message is for') {this.value = '';}\" onblur=\"if (this.value == '') {this.value = 'this message is for';}\"><div class='remove-elem' data-target_type='regular' data-target_id='line_" + line_number + "' id=\"close_for_msg\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
                    $("#call_content").append(line_content);
                    document.getElementById('lineNumber').value = line_number;
                    addItemOnSequence('line_' + line_number);
                    break;
                case 'file':
                    //for file attachment
                    uploadCallFile();
                    break;
                case 'fullname':
                    var name_number = parseInt(document.getElementById('fullnameNumber').value) + 1;
                    var name_content = "<tr id=\"fullname_" + name_number + "\"><td><div style=\"position:relative;\"><input type=\"text\" style=\"padding-right:30px;\" id=\"name_text_" + name_number + "\" name='format_text[]' value=\"&lt;full name&gt;\" class=\"call_box\" onfocus=\"if (this.value == '&lt;full name&gt;') {this.value = '';}\" onblur=\"if (this.value == '') {this.value = '&lt;full name&gt;';}\"><div class='remove-elem' data-target_type='regular' data-target_id='fullname_" + name_number + "'  id=\"close_name_text\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
                    $("#call_content").append(name_content);
                    document.getElementById("fullnameNumber").value = name_number;
                    addItemOnSequence('fullname_' + name_number);
                    break;
                case 'date':
                    var date_number = parseInt(document.getElementById("dateNumber").value) + 1;
                    var date_content = "<tr id=\"date_" + date_number + "\"><td><div style=\"position:relative;\"><input type=\"text\" style=\"padding-right:30px;\" id=\"app_date_text_" + date_number + "\" name='format_text[]' value=\"&lt;appointment date&gt;\" class=\"call_box\" onfocus=\"if (this.value == '&lt;appointment date&gt;') {this.value = '';}\" onblur=\"if (this.value == '') {this.value = '&lt;appointment date&gt;';}\"><div class='remove-elem' data-target_type='regular' data-target_id='date_" + date_number + "'  id=\"close_app_date_text\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
                    $("#call_content").append(date_content);
                    document.getElementById("dateNumber").value = date_number;
                    addItemOnSequence('date_' + date_number);
                    break;


            }
            $(".call-content-container").css("display","table-row");
            return false;
        };

        var removeItemOnSequence = function(id){
			var sequenceIds=document.getElementById('formatOrder').value;
            document.getElementById('formatOrder').value='';
            if(sequenceIds){
                var sequenceArray=sequenceIds.split(',');
                for(var i=0; i<sequenceArray.length; i++) {
                    if (sequenceArray[i] != id){
                        if(!document.getElementById('formatOrder').value){
                            document.getElementById('formatOrder').value=sequenceArray[i];
                        }else{
                            document.getElementById('formatOrder').value+=","+sequenceArray[i];
                        }
                    }
                }
                return false;
            }
		};
        
        var addItemOnSequence = function(id) {
            if (document.getElementById("formatOrder").value) {
                document.getElementById("formatOrder").value += "," + id;
            } else {
                document.getElementById("formatOrder").value += id;
            }
        };

        var uploadCallFile = null;
        if (typeof cr_add != 'undefined') {
            uploadCallFile = new ss.SimpleUpload({
                button: 'att_file_play', // upload button
                url: glab_plugin_url + "/upload_code/uploadHandler.php", // URL of server-side upload handler
                name: 'att_file', // parameter name of the uploaded file
                responseType: 'json',
                //allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'pdf'],
                maxSize: 5024, // kilobytes
                onSubmit: function() {
                    //this.setProgressBar( $('#progressBar') ); // designate elem as our progress bar
                },
                onComplete: function(filename, response) {
                    if (!response) {
                        alert(filename + 'upload failed');
                        return false;
                    }
                    // do something with response...
                    var new_filename = response.file;
                    if (document.getElementById("fileNumber").value != '1') {
                        addEmptyFileTag('file');
                    }
                    var file_number = document.getElementById("fileNumber").value;

                    if (!document.getElementById("file_text_" + file_number)) {
                        addEmptyFileTag('file');
                        file_number = document.getElementById("fileNumber").value;
                    }
                    document.getElementById("file_text_" + file_number).value = filename;
                    document.getElementById("attached_" + file_number).value = new_filename;
                    if (document.getElementById("fileNumber").value == '1')
                        document.getElementById("fileNumber").value = parseInt(file_number) + 1;
                    $("#cancel_upload").removeAttr('disabled');
                }
            });
        }

        var removeItem = function() {
            var $currentObj = $(this);
            var target_id = $currentObj.data('target_id');
            if ($currentObj.data('target_type') == 'regular') {
                removeElementTag(target_id);
            } else {
                removeFileTag(target_id);
            }
            var content_length=$("#call_content tr").length;
            if(content_length<=0){
            	$(".call-content-container").css("display","none");
            }
        };

        var removeElementTag = function(id) {
            $('#' + id).remove();
            removeItemOnSequence(id);
            return false;
        }

        var removeFileTag = function(number) {
            cancel_file_upload(number);
            $('#file_' + number).remove();
            $('#attached_' + number).remove();
            removeItemOnSequence('file_' + number);
            return false;
        }

        var addEmptyFileTag = function() {
            var file_number = parseInt(document.getElementById("fileNumber").value) + 1;
            var file_content = "<tr id=\"file_" + file_number + "\"><td><div style=\"position:relative;\"><input type=\"text\" style=\"padding-right:30px;\" id=\"file_text_" + file_number + "\" name='format_text[]' value=\"&lt;file:acupuncture_appt_reminder.mp3&gt;\" onfocus=\"if (this.value == '&lt;file:acupuncture_appt_reminder.mp3&gt;') {this.value = '';}\" onblur=\"if (this.value == '') {this.value = '&lt;file:acupuncture_appt_reminder.mp3&gt;';}\" class=\"call_box\"><div class='remove-elem' data-target_type='file' data-target_id='" + file_number + "' id=\"close_file_text\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
            var attached_file_content = "<input type=\"hidden\" name=\"attached_file_names[]\" value=\"\" id=\"attached_" + file_number + "\">";
            $("#call_content").append(file_content);
            $("#file_names").append(attached_file_content);
            document.getElementById('fileNumber').value = file_number;
            addItemOnSequence('file_' + file_number);
        };

        var validateSubmission = function(e) {
            if (document.getElementById('prac_option').value && document.getElementById('treat_ids').value) {
                var saying_script = getSayingScript();
                document.getElementById("saying_text_msg").value = saying_script;
            } else {
                alert("Select One Practioner and also at least one treatment to submit");
                e.preventDefault();
            }
        };

        var makeTestCall = function() {
            var saying_script = getSayingScript();
            if (document.getElementById("number_text").value) {
                //showLoading();
                $.ajax({
                    type: "POST",
                    url: glab_plugin_url + "/call_reminder/handle_call.php",
                    cache: false,
                    data: "dial_url=" + dial_url + "&to_number=" + document.getElementById("number_text").value + "&voice_type=" + document.getElementById("voice_type").value + "&saying_text=" + saying_script,
                    dataType: "html",
                    success: function(data) {
                        //hideLoading();
                        alert("Successfully Done");
                    }});
            } else {
                alert("Please Enter a Number.");
            }
            return false;
        };
        
        var loadPracServices=function(){
        	addLoader();
        	var $currentObj=$(this);
        	var prac_id=$currentObj.val();
        	var data = {
                    'load_prac_reminder_services': true,
                    'prac_id': prac_id,
                    'reminder_type': $("input[name='reminder_type']").val()
                };
        	$.post(glab_ajax_url, data, function(response) {
               
                if (response != "") {
                	 $("#service").html(response);
                } else {
                	$("#service").html('');
                   alert("No service available for that practitioner");
                }
                removeLoader();
            });
        };

        var getSayingScript = function() {
            var msg = '';
            if (document.getElementById("formatOrder").value) {
                var sequenceIds = document.getElementById("formatOrder").value;
                var sequenceArray = sequenceIds.split(',');
                for (var i = 0; i < sequenceArray.length; i++) {
                    var partialArray = sequenceArray[i].split('_');
                    msg += ' ' + getMsgText(partialArray[0], partialArray[1]) + '*-';
                }
                return msg;
            } else {
                return false;
            }
        };

        var getMsgText = function(prefix, number) {
            var msg = '';
            switch (prefix) {
                case 'pause5':
                    msg = "_twilio1SecPause_0.5_!twilio1SecPause_";
                    break;
                case 'pause':
                    msg = "_twilio1SecPause_1_!twilio1SecPause_";
                    break;
                case 'line':
                    msg = document.getElementById("msg_for_text_" + number).value;
                    break;
                case 'file':
                    msg = '928afile_' + document.getElementById("attached_" + number).value + '_endafile';
                    break;
                case 'date':
                    msg = document.getElementById("app_date_text_" + number).value;
                    break;
                case 'fullname':
                    msg = document.getElementById("name_text_" + number).value;
                    break;
            }
            return msg;
        };
        
        var cancelUpload = function(e){
        	var input = $("#att_file");
        	 input.replaceWith(input.val('').clone(true));
        	 e.preventDefault();
        };

        var deleteFile = function(e){
        	var $that = $(this);
        	var url = $that.attr('data-url');
        	//console.log(url);
        	e.preventDefault();
        };
        
        $(document).on("change", settings.reminder_selection, loadReminderType);
        $(document).on("click", settings.elem_selector, addElementTag);
        $(document).on("click", settings.file_selector, uploadCallFile);
        $(document).on("click", settings.remove_selector, removeItem);
        $(document).on("click", settings.cancel_upload_selector, cancelUpload);
        $(document).on("click", settings.delete_file_selector, deleteFile);
        $(document).on("click", settings.call_reminder_submit, validateSubmission);
        $(document).on("click", settings.test_call_btn, makeTestCall);
        $(document).on("change", settings.prac_selector, loadPracServices);

    };
}(jQuery));
