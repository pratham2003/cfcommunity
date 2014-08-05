jQuery(document).ready(function(){
    /**
     * Default Avatar image uploader
     */
    jQuery('.bpgt_type_upload_avatar').click(function( e ){
        e.preventDefault();
        var button = jQuery(this);
        var file_frame;

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: button.data( 'uploader_title' ),
            library : { type : 'image'},
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get('selection').first().toJSON();
            // Do something with attachment.id and/or attachment.url
            // hidden input to store the data
            jQuery('input[name="type_avatar"]').val(attachment.id);

            // show preview
            jQuery('.bpgt_type_avatar_preview img').attr('src', attachment.url);
            jQuery('.bpgt_type_avatar_preview').show();
        });

        // Finally, open the modal
        file_frame.open();
    });

    jQuery('.bpgt_type_upload_avatar_cancel').click(function(e){
        e.preventDefault();
        jQuery('input[name="type_avatar"]').val('');

        // show preview
        jQuery('.bpgt_type_avatar_preview img').attr('src', '');
        jQuery('.bpgt_type_avatar_preview').hide();
    });

    avatarPreview();
});

/**
 * Display original image in types list on hover
 */
function avatarPreview(){
    var xOffset = 10;
    var yOffset = 20;
    var image   = jQuery(".preview_avatar");
    /* END CONFIG */
    image.hover(function(e){
        jQuery("body").append('<p id="preview_def_avatar"><img src="'+ this.src +'"/></p>');
            jQuery("#preview_def_avatar")
            .css("top",(e.pageY - xOffset) + "px")
            .css("left",(e.pageX + yOffset) + "px")
            .fadeIn("fast");
    },
    function(){
        jQuery("#preview_def_avatar").remove();
    });

    image.mousemove(function(e){
        jQuery("#preview_def_avatar")
            .css("top",(e.pageY - xOffset) + "px")
            .css("left",(e.pageX + yOffset) + "px");
    });
}