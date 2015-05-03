(function($) {

    $.fn.glab_poll = function(options) {

        var defaults = {
            add_btn: ".add-elem",
            remove_btn: ".remove-elem"
        };
        var settings = $.extend({}, defaults, options);

        var addElementTag = function(e) {
            var $currentObj = $(this);
            e.preventDefault();
            if ($currentObj.data('elem_type') == 'comment') {
                addCommentField();
            } else {
                addPollField();
            }
        };

        var addCommentField = function(e) {
            var option_number = parseInt(document.getElementById("optionNumber").value) + 1;
            var option_content = "<tr id=\"option_" + option_number + "\"><td>&nbsp;</td><td style=\"padding:5px 0 0 200px;\"><div style=\"position:relative;\"><input type=\"text\" style=\"padding-right:30px;\" id=\"msg_for_text_" + option_number + "\" name=\"format_text[]\" placeholder=\"Comment Field\" value=\"Comment Field\" class=\"field_box\"><div class='remove-elem' data-target_id='line_"+option_number+"' id=\"close_for_msg\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
            $("#content_table").append(option_content);
            document.getElementById('optionNumber').value = option_number;
            addItemOnSequence('option_' + option_number);
        };

        var addPollField = function(e) {
            var poll_number = parseInt(document.getElementById("pollNumber").value) + 1;
            var poll_content = "<tr id=\"poll_" + poll_number + "\"><td>&nbsp;</td><td style=\"padding:5px 0 0 200px;\"><div style=\"position:relative;\"><input type=\"text\" style=\"padding-right:30px;\" id=\"poll_for_text_" + poll_number + "\" name=\"format_text[]\" placeholder=\"Poll Option\"  class=\"field_box\" ><div class='remove-elem' data-target_id='poll_"+poll_number+"' id=\"close_for_msg\" style=\"margin-left:-25px;width:20px;display:inline;cursor:pointer;\"><img style=\"margin-bottom:-3px;\" alt=\"x\" src=\"" + glab_asset_url + "/images/cancel.png\"></div></div></td></tr>";
            $("#content_table").append(poll_content);
            document.getElementById('pollNumber').value = poll_number;
            addItemOnSequence('poll_' + poll_number);
        };

        var addItemOnSequence = function(id) {
            if (document.getElementById("sequence").value) {
                document.getElementById("sequence").value += "," + id;
            } else {
                document.getElementById("sequence").value += id;
            }
        };

        var removeElementTag = function(e) {
            e.preventDefault();
            var $currentObj = $(this);
            var id = $currentObj.data('target_id');
            var sequenceIds = document.getElementById('sequence').value;
            document.getElementById('sequence').value = '';
            if (sequenceIds) {
                var sequenceArray = sequenceIds.split(',');
                for (var i = 0; i < sequenceArray.length; i++) {
                    console.log(sequenceArray[i] + "  --- "+id)
                    if (sequenceArray[i] != id) {
                        if (!document.getElementById('sequence').value) {
                            document.getElementById('sequence').value = sequenceArray[i];
                        } else {
                            document.getElementById('sequence').value += "," + sequenceArray[i];
                        }
                    }
                }
                return false;
            }
        };

        $(document).on("click", settings.add_btn, addElementTag);
        $(document).on("click", settings.remove_btn, removeElementTag);
    };
}(jQuery));

