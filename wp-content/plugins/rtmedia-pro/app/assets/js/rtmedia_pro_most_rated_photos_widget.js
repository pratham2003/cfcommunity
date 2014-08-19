var div_id = "";
function change_gallery_tabs(element,wdtype,type,widgetid) {
    var div_id = wdtype+'-media-tabs-'+type+'-'+widgetid;    
    jQuery(element).parent().siblings().removeClass('active-tab');
    jQuery(element).parent().addClass('active-tab');
    jQuery(element).parent().parent().siblings().removeClass('active-div');    
    jQuery('#'+div_id).addClass("active-div");
    return false;
}