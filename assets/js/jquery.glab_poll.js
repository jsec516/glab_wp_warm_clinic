(function($) {

    $.fn.glab_poll = function(options) {

        var defaults = {
            add_btn: ".add-elem",
            remove_btn: ".remove-elem"
        };
        var settings = $.extend({}, defaults, options);

        var addElementTag = function(e) {
            e.preventDefault();
            addPollField();
        };
        
        var addPollField = function(e) {
            var poll_content = "<li><input type='text' name='options[]' class='glab_wp_text large' placeholder='type option' /><a href='#' style='padding-left:10px;' class='remove-elem'>Remove</a></li>";
            $("#content_table").append(poll_content);
        };

        var removeElementTag = function(e) {
            e.preventDefault();
            var $currentObj = $(this);
            $currentObj.parent().remove();
        };

        $(document).on("click", settings.add_btn, addElementTag);
        $(document).on("click", settings.remove_btn, removeElementTag);
    };
}(jQuery));

