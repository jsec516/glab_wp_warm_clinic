jQuery(document).ready(function() {
    /*jQuery("#wp-admin-bar-wait-indicator").on('click', function(e) {
        e.preventDefault();
        // jQuery("#waitingListDiv")
        if (jQuery("#waitingListDiv").length == false)
        {
            var $currentObj = jQuery("#waitingListDivClone");
            var elemStyle = $currentObj.attr('style');
            var htmlVal = $currentObj.html();
            var elemHtml = '<div id="waitingListDiv" style="' + elemStyle + '">' + htmlVal + '</div>';
            jQuery("#wp-admin-bar-wait-indicator").append(elemHtml);
        }
        jQuery("#waitingListDiv").toggle();
    });
    if (jQuery("#waitingListDiv").length == false)
    {
        var $ = jQuery;
        var $currentObj = $("#waitingListDivClone");
        var elemStyle = $currentObj.attr('style');
        var htmlVal = $currentObj.html();
        var elemHtml = '<li><div id="waitingListDiv" style="' + elemStyle + '">' + htmlVal + '</div></li>';
        $("#wp-admin-bar-root-default").append(elemHtml);
    }
    
	*/
	
    jQuery(".uploadBtn").click(function(e) {
        e.preventDefault();
        var elem = jQuery(this);
        var form = jQuery(this).parents('form')[0];
        var request_url = elem.data('target_url');
        var formData = new FormData(form);
        jQuery('.working').show();
        jQuery.ajax({
            url: request_url, //Server script to process data
            type: 'POST',
            dataType: 'json',
            xhr: function() {  // Custom XMLHttpRequest
                var myXhr = jQuery.ajaxSettings.xhr();
                if (myXhr.upload) { // Check if upload property exists
                    // myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                }
                return myXhr;
            },
            //Ajax events
            complete: function(data) {
                jQuery('.working').hide();
                jQuery(".import-status").html("<p><strong>Completed!</strong></p>").css("display", "block");
            },
            
            // Form data
            data: formData,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false,
            contentType: false,
            processData: false
        });
        //		    $('.loader').hide();
        return false;
    });
});
function addLoader(){
	$=jQuery;
	var asset_url=$("input[name='glab_asset']").val();
	$('body').append("<div id='loaderImg' style='position:absolute;top:0;z-index:99999;width:100%;height:100%;display:block;background:#fff;opacity:0.40;'><img style='margin-top:20%;margin-left:50%;' src='"+asset_url+"images/ajax-loader.gif' /></div>");
}
				
function removeLoader(){
	$=jQuery;
	$("#loaderImg").remove();
}