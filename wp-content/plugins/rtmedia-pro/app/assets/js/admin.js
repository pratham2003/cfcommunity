/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var loading_img = "<img src='" + rtmedia_loading_file + "' />";
function delete_global_album(element) {
    var album_element_array = jQuery(element).attr('id').split('_');
    var album_id = album_element_array[album_element_array.length - 1];
    var conf = confirm("Are you sure you want to delete this album?");
    if (conf == true) {
        jQuery(element).parent().append(loading_img);
        jQuery.post(ajaxurl,
            {
                action: 'delete_global_album',
                album_id: album_id
            },
            function (data) {
                jQuery(element).parent().find('img').remove();
                data = data.trim();
                if (data == '1') {
                    jQuery(element).parent().parent().remove();
                }
            });
    }
}

function rename_global_album(element) {
    var global_album_name = prompt("Enter Global Album name : ", jQuery(element).parent().siblings('.global-album-name').text().trim());
    if (global_album_name !== "" && global_album_name !== null) {
        var album_element_array = jQuery(element).attr('id').split('_');
        var album_id = album_element_array[album_element_array.length - 1];
        jQuery(element).parent().append(loading_img);
        jQuery.post(ajaxurl,
            {
                action: 'rename_global_album',
                album_id: album_id,
                album_name: global_album_name
            },
            function (data) {
                jQuery(element).parent().find('img').remove();
                data = data.trim();
                if (data == '1') {
                    jQuery(element).parent().siblings(".global-album-name").text(global_album_name);
                }
            });
    }
}

jQuery(document).ready(function () {
    var global_album_radios = jQuery("input[name='rtmedia-options[default_global]']");
    var current_global_ablum = "";
    for (var i = 0; i < global_album_radios.length; i++) {
        if (global_album_radios[i].checked) {
            current_global_ablum = global_album_radios[i].id;
            break;
        }
    }
    jQuery("input[name='rtmedia-options[default_global]']").change(function () {
        var element = this;
        var album_element_array = jQuery(element).attr('id').split('_');
        var album_id = album_element_array[album_element_array.length - 1];
        jQuery(element).parent().append(loading_img);
        jQuery.post(ajaxurl,
            {
                action: 'change_default_global_album',
                album_id: album_id
            },
            function (data) {
                data = data.trim();
                if (data == '1') {
                    jQuery('#' + current_global_ablum).parent().siblings('.delete-td').children('.delete-label').css('display', 'inline');
                    jQuery(element).parent().find('img').remove();
                    jQuery(element).parent().siblings('.delete-td').children('.delete-label').css('display', 'none');
                    current_global_ablum = element.id;
                }
            });
    });
	 // To check for enable - disable number of media items in feed option onload
     if (jQuery('#rtmedia-bp-enable-podcasting').is(":checked")) {
        jQuery(".rtmedia-bp-feed-setting").prop("disabled", false);
        jQuery(".podcasting-driven-disable label .rt-switch").bootstrapSwitch("setActive", true);
    } else {
	   jQuery(".rtmedia-bp-feed-setting").prop("disabled", true);
		   if ( jQuery( ".podcasting-driven-disable label .rt-switch" ).length > 0 ) {
				jQuery(".podcasting-driven-disable label .rt-switch").bootstrapSwitch("setActive", false);
			}
    }
	
	 jQuery('#rtmedia-bp-enable-podcasting').on("click", function(e){
	if (jQuery(this).is(":checked")) {
	    jQuery(".rtmedia-bp-feed-setting").prop("disabled", false);
	} else {
	    jQuery(".rtmedia-bp-feed-setting").prop("disabled", true);
	}
    });
	
	
});

function rtmedia_delete_media(element) {

    var el_id = element.id;

    if (typeof el_id === 'undefined') {
        el_id = jQuery(element).attr('id');
    }

    if (typeof el_id === 'undefined') return;

    var id_array = el_id.split("-");

    if (confirm("Are you sure to delete this media?")) {
        var param = {action: "rtmedia_moderate_delete_media", media_id: id_array[id_array.length - 1]};
        jQuery.post(ajaxurl, param, function (data) {
            data = data.trim();
            if (data === 'true') {
                //Close thickbox if there
                if (jQuery('.tb-close-icon').length > 0) {
                    jQuery('.tb-close-icon').click();
                }
                jQuery(element).parent("span").parent("div").parent("td").parent("tr").remove();
            }
        });
    }
}

function rtmedia_allow_content(element) {
    var el_id = element.id;
    var id_array = el_id.split("-");
    if (confirm("Are you sure to Allow this media?")) {
        var param = {action: "rtmedia_moderate_allow_media", media_id: id_array[id_array.length - 1]};
        jQuery.post(ajaxurl, param, function (data) {
            data = data.trim();
            if (data === 'true') {
                jQuery(element).parent("span").parent("div").parent("td").parent("tr").remove();
            } else {
                alert("Can't Allow this media.");
            }
        });
    }
}


function rtmedia_block_user(author_id) {
    if (confirm("Are you sure to block this user?")) {
        var param = {action: "rtmedia_block_user", author_id: author_id};
        jQuery.post(ajaxurl, param, function (data) {
            data = data.trim();
            if (data === 'true') {
                alert("User blocked.");
            } else {
                alert("Can't block this user.")
            }
        });
    }
}

function rtmedia_unblock_user(element) {
    var el_id = element.id;
    var id_array = el_id.split("-");
    if (confirm("Are you sure to Unblock this user?")) {
        var param = {action: "rtmedia_unblock_user", author_id: id_array[id_array.length - 1]};
        jQuery.post(ajaxurl, param, function (data) {
            data = data.trim();
            if (data === 'true') {
                jQuery(element).parent("td").parent("tr").remove();
                alert("User Unblocked.");
            } else {
                alert("Can't Unblock this user.")
            }
        });
    }
}
/**
 * Updates Album count
 */
function rtmedia_update_album_count() {
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'rtm_album_count'
        },
        success: function (count) {
            count = jQuery.parseJSON(count);
            if (count) {
                jQuery('.album-count-all').html(count['all']);
                jQuery('.album-count-profile').html(count['profile']);
                jQuery('.album-count-other').html(count['other']);
                if (count['group']) {
                    jQuery('.album-count-group').html(count['group']);
                }
            }
        },
        error: function (data) {
            console.log(data);
        }
    });
}
/**
 * Updates Album Media Count
 */
function rtmedia_update_album_media_count(current_element) {
    if (current_element.parents().eq(3).siblings('tr').first().length) {
        $next_media_id = current_element.parents().eq(3).siblings('tr').first().find('.delete a').attr('id').split('-');
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'rtm_album_media_count',
                delete_media_id: $next_media_id[$next_media_id.length - 1]
            },
            success: function (count) {
                jQuery('.media-total-count').html(count);
            },
            error: function (data) {
                console.log(data);
            }
        });
    } else {
        jQuery('.media-total-count').html('0')
    }
}
/**
 * Updates Album or media count as per delete request
 * @returns {undefined}
 */
function rtmedia_update_count(current_element) {
    $current_parent = current_element.parents().eq(1);
    if ($current_parent.attr('class') == 'album-row-actions') {
        rtmedia_update_album_count();
    } else {
        rtmedia_update_album_media_count(current_element);
    }
}
jQuery(document).ready(function ($) {
    if (jQuery('#rtmedia-album-enable').is(":checked")) {
        jQuery(".rtmedia-album-setting").prop("disabled", false);
        jQuery(".rtmedia-album-setting input[type=checkbox]").prop("disabled", false);
        jQuery(".rtmedia-album-setting label .rt-switch").bootstrapSwitch("setActive", true);
    } else {
        jQuery(".rtmedia-album-setting").prop("disabled", true);
        jQuery(".rtmedia-album-setting input[type=checkbox]").prop("disabled", true);
        if (jQuery(".rtmedia-album-setting label .rt-switch").length > 0) {
            jQuery(".rtmedia-album-setting label .rt-switch").bootstrapSwitch("setActive", false);
        }
        jQuery('.rtmedia-album-setting').parent().parent().css("display", "none");
    }

    jQuery('#rtmedia-album-enable').on("click", function (e) {
        if (jQuery(this).is(":checked")) {
            jQuery(".rtmedia-album-setting input[type=checkbox]").prop("disabled", false);
            jQuery(".rtmedia-album-setting input[type=checkbox]").click();
            jQuery(".rtmedia-album-setting").prop("disabled", false);
            jQuery(".rtmedia-album-setting label .rt-switch").bootstrapSwitch("setActive", true);
            jQuery('.rtmedia-album-setting').parent().parent().css("display", "block");
        } else {
            if (jQuery(".rtmedia-album-setting input[type=checkbox]").is(":checked"))
                jQuery(".rtmedia-album-setting input[type=checkbox]").click();
            jQuery(".rtmedia-album-setting input[type=checkbox]").prop("disabled", true);
            jQuery(".rtmedia-album-setting").prop("disabled", true);
            jQuery(".rtmedia-album-setting label .rt-switch").bootstrapSwitch("setActive", false);
            jQuery('.rtmedia-album-setting').parent().parent().css("display", "none");
        }
    });

    if (!jQuery('.rtm_allow_other_upload input[type=checkbox]').is(":checked")) {
        jQuery("#rtmedia-other-types-warning").css("display", 'none');
        jQuery("#rtm_other_extensions").attr("disabled", true);
    }

    jQuery('.rtm_allow_other_upload input[type=checkbox]').on("click", function (e) {
        if (jQuery(this).is(":checked")) {
            jQuery("#rtm_other_extensions").prop("disabled", false);
            jQuery("#rtmedia-other-types-warning").css("display", 'block');

        } else {
            jQuery("#rtm_other_extensions").prop("disabled", true);
            jQuery("#rtmedia-other-types-warning").css("display", 'none');
        }
    });


    if (!jQuery('.rtm_enable_bbpress input[type=checkbox]').is(":checked")) {
        jQuery("#rtmedia_gd_bbp_attachement_notice").css("display", 'none');
        jQuery("#rtmedia_bbp_attachment_allow_other").css("display", 'none');
        jQuery(".rtm_bbpress_default_view input").prop("disabled", true);
        jQuery('.rtm_enable_bbpress').parent().parent().siblings('div.row').css("display", "none");
    }

    jQuery('.rtm_enable_bbpress input[type=checkbox]').on("click", function (e) {
        if (jQuery(this).is(":checked")) {
            jQuery(".rtm_bbpress_default_view input").prop("disabled", false);
            jQuery("#rtmedia_gd_bbp_attachement_notice").css("display", 'block');
            jQuery("#rtmedia_bbp_attachment_allow_other").css("display", 'block');
            jQuery('.rtm_enable_bbpress').parent().parent().siblings('div.row').css("display", "block");
        } else {
            jQuery(".rtm_bbpress_default_view input").prop("disabled", true);
            jQuery("#rtmedia_gd_bbp_attachement_notice").css("display", 'none');
            jQuery("#rtmedia_bbp_attachment_allow_other").css("display", 'none');
            jQuery('.rtm_enable_bbpress').parent().parent().siblings('div.row').css("display", "none");
        }
    });

    if (jQuery('.rtm_enable_comment_form input[type=checkbox]').is(":checked")) {
        jQuery(".rtm_enable_anonymous_comment").prop("disabled", false);
        jQuery(".rtm_enable_anonymous_comment input[type=checkbox]").prop("disabled", false);
        if (jQuery(".rtm_enable_anonymous_comment label .rt-switch").length > 0) {
            jQuery(".rtm_enable_anonymous_comment .rt-switch").bootstrapSwitch("setActive", false);
        }
    } else {
        jQuery(".rtm_comment_default_view input").prop("disabled", true);
        jQuery(".rtm_enable_anonymous_comment input[type=checkbox]").prop("disabled", true);
        if (jQuery(".rtm_enable_anonymous_comment label .rt-switch").length > 0) {
            jQuery(".rtm_enable_anonymous_comment .rt-switch").bootstrapSwitch("setActive", true);
        }
        jQuery('.rtm_enable_comment_form').parent().parent().siblings('div.row').css("display", "none");
    }

    jQuery('.rtm_enable_comment_form input[type=checkbox]').on("click", function (e) {
        if (jQuery(this).is(":checked")) {
            jQuery(".rtm_enable_anonymous_comment input[type=checkbox]").prop("disabled", false);
            //jQuery(".rtm_enable_anonymous_comment input[type=checkbox]").click();
            jQuery(".rtm_comment_default_view input").prop("disabled", false);
            jQuery(".rtm_enable_anonymous_comment label .rt-switch").bootstrapSwitch("setActive", true);
            jQuery('.rtm_enable_comment_form').parent().parent().siblings('div.row').css("display", "block");
        } else {
            if (jQuery(".rtm_enable_anonymous_comment input[type=checkbox]").is(":checked"))
                jQuery(".rtm_enable_anonymous_comment input[type=checkbox]").click();
            jQuery(".rtm_enable_anonymous_comment input[type=checkbox]").prop("disabled", true);
            jQuery(".rtm_comment_default_view input").prop("disabled", true);
            jQuery(".rtm_enable_anonymous_comment label .rt-switch").bootstrapSwitch("setActive", false);
            jQuery('.rtm_enable_comment_form').parent().parent().siblings('div.row').css("display", "none");
        }
    });

    jQuery('#rtmedia-settings-submit').on("click", function (e) {
        e.preventDefault();
        var otherExtensions = $('#rtm_other_extensions');
        if (!otherExtensions.prop('disabled') && $.trim(otherExtensions.val()) == "") {
            alert(rtmedia_empty_extension_msg);
            return false;
        }
        var regex = /^[a-zA-Z0-9, ]*$/;
        if (!regex.test(otherExtensions.val())) {
            alert(rtmedia_invalid_extension_msg);
            return false;
        }
        jQuery(this).closest('form').submit();
    });

    jQuery('#rtm-deactivate-gd-bbpress').on("click", function (e) {
        var param = {action: "rtmedia_deactivate_GD_bbPress", deactivate_GD: true};
        jQuery.post(ajaxurl, param, function (data) {
            data = data.trim();
            if (data == "true") {
                window.location.reload();
            }
        });
    });
//Admin Album Delete Click
    jQuery('body').on('click', '.album-row-actions .delete a, .album-media-row-actions .delete a', function () {
        rtmedia_delete_media(jQuery(this));
        rtmedia_update_count(jQuery(this));
    });

//Merge Album
    jQuery('body').on('click', '.album-row-actions .merge a', function () {
        var merge_album_id = jQuery(this).attr('id');
        $current_option = jQuery('.rtmedia-merge-user-album-list option[value=' + merge_album_id + ']');
        jQuery('.merge-album-id').val(merge_album_id);
        jQuery('.rtmedia-merge-user-album-list option').show();
        $current_option.hide();
        if ($current_option.is(':last-child')) {
            jQuery('.rtmedia-merge-user-album-list option:first-child').attr('selected', 'selected');
        } else {
            jQuery('.rtmedia-merge-user-album-list option[value=' + merge_album_id + ']').next('option').attr('selected', 'selected');
        }
        jQuery('#rtmedia-merge').css('visibility', 'visible').show();
    });
//Merge albums
    jQuery('body').on('click', '.rtmedia-move-selected', function (e) {

        e.preventDefault();
        var el_id = jQuery('.merge-album-id').val();

        if (confirm("Are you sure to merge this media?")) {
            var param = {
                action: "rtmedia_merge_album",
                merge_album: el_id,
                album: jQuery('.rtmedia-merge-user-album-list').val()
            };
            jQuery.post(ajaxurl, param, function (data) {
                data = data.trim();
                if (data === 'merged') {
                    jQuery('.close-reveal-modal').click();
                    jQuery('#' + el_id).parents().eq(3).remove();
                    location.reload();
                }
            });
        }
    });

    jQuery('#album-form').on('submit', function () {
        return false;
    });


    jQuery('.close-reveal-modal').on('click', function () {
        jQuery(this).parent().fadeOut();
    });

    jQuery(document).keyup(function (e) {
        if (e.keyCode == 27) {
            jQuery('.close-reveal-modal').click();
        }   // esc
    });

    jQuery('.rtmedia-container .rtmedia-user-album-list').on('change', function () {
        var context = jQuery('.rtmedia-container .rtmedia-user-album-list :selected').parent().attr('value');
        if (jQuery.isNumeric(context)) {
            context = "profile";
        }
        jQuery('#rtmedia-uploader-form input[name=context]').val(context);
    });
});

// validations for upload limits
jQuery('#bp-media-settings-boxes').on('submit', '#bp_media_settings_form,#rtmedia-settings-submit', function (e) {

    // return true if no limit set i.e. unlimited
    var return_value = true;
    var rtmedia_storage_limit_daily = parseInt(jQuery('#rtmedia_storage_limit_daily').val());
    var rtmedia_storage_limit_monthly = parseInt(jQuery('#rtmedia_storage_limit_monthly').val());
    var rtmedia_storage_limit_lifetime = parseInt(jQuery('#rtmedia_storage_limit_lifetime').val());
    var rtmedia_files_limit_daily = parseInt(jQuery('#rtmedia_files_limit_daily').val());
    var rtmedia_files_limit_monthly = parseInt(jQuery('#rtmedia_files_limit_monthly').val());
    var rtmedia_files_limit_lifetime = parseInt(jQuery('#rtmedia_files_limit_lifetime').val());
    if (rtmedia_storage_limit_daily == 0 && rtmedia_storage_limit_monthly == 0 && rtmedia_storage_limit_lifetime == 0 && rtmedia_files_limit_daily == 0 && rtmedia_files_limit_monthly == 0 && rtmedia_files_limit_lifetime == 0) {
        return true;
    }

    // upload: lifetime >= monthly and lifetime >= daily
    if (rtmedia_storage_limit_lifetime != 0 && ( !( rtmedia_storage_limit_lifetime >= rtmedia_storage_limit_daily && rtmedia_storage_limit_lifetime >= rtmedia_storage_limit_monthly ) )) {
        alert("Life time storage limit must be greater than daily limit and monthly limit.");
        return_value = false;
    }
    // upload: monthly >= daily
    if (rtmedia_storage_limit_monthly != 0 && ( !( rtmedia_storage_limit_monthly >= rtmedia_storage_limit_daily ) )) {
        alert("Monthly storage limit must be greater than daily limit.");
        return_value = false;
    }
    // storage: lifetime >= monthly and lifetime >= daily
    if (rtmedia_files_limit_lifetime != 0 && ( !( rtmedia_files_limit_lifetime >= rtmedia_files_limit_daily && rtmedia_files_limit_lifetime >= rtmedia_files_limit_monthly ) )) {
        alert("Life time upload limit must be greater than daily limit and monthly limit.");
        return_value = false;
    }
    // storage: monthly >= daily
    if (rtmedia_files_limit_monthly != 0 && ( !( rtmedia_files_limit_monthly >= rtmedia_files_limit_daily ) )) {
        alert("Monthly upload limit must be greater than daily limit.");
        return_value = false;
    }
    if (!return_value) {
        e.preventDefault();
    }
    return return_value;
});

// Open thickbox for editing album media
jQuery( '#rtmedia-edit-wp-gallery-button' ).on( 'click', function() {
    var context_id = rtmediaGetParameterByName( 'post' );
    
    tb_show( jQuery( '#title' ).val(), ajaxurl + '?height=450&width=1000&context_id=' + context_id + '&action=rtm_edit_wp_album_gallery' );    
});

// to view details of clicked media when editing album media
jQuery( 'body' ).on( 'click', '#rtmedia-edit-wp-gallery-content ul li div', function() {
    // Hide Media deleted success msg
    jQuery( '.rtmedia-attachment-delete-success' ).hide();
    jQuery( '.rtmedia-attachment-save-success' ).hide();
    
    // To assign some css properties
    jQuery( this ).parent().toggleClass( 'selected' );
    
    // Checking if any li tag hase selected class
    if( jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).length > 0 ) {
        jQuery( '.rtmedia-delete-attachment' ).show();
    } else {
        jQuery( '.rtmedia-delete-attachment' ).hide();
    }
    
    // If only one li has selected class then display details of it
    if( jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).length == 1 ) {
        jQuery( '.media-sidebar h3, .media-sidebar .setting, .media-sidebar .save-button' ).show();
        // Checking if clicked div's parent li has selected class
        if( jQuery( this ).parent().hasClass( 'selected' ) ) {
            jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_title' ).val( jQuery( this ).parent().find( '.rtm_media_title' ).val() );
            jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_description' ).val( jQuery( this ).parent().find( '.rtm_media_description' ).val() );
        } else {
            jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_title' ).val( jQuery( this ).parent().siblings( 'li.selected:last' ).find( '.rtm_media_title' ).val() );
            jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_description' ).val( jQuery( this ).parent().siblings( 'li.selected:last' ).find( '.rtm_media_description' ).val() );
        }
    } else {
        jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_title' ).val('');
        jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_description' ).html('');
        jQuery( '.media-sidebar h3, .media-sidebar .setting, .media-sidebar .save-button' ).hide();
    }
});

// After changes save the title and description
jQuery( 'body' ).on( 'click', '.save-button', function() {
    // Get media data 
    var rtm_id = jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).find( '.rtm_id' ).val();
    var rtm_media_id = jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).find( '.rtm_media_id' ).val();
    var rtm_media_title = jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_title' ).val();
    var rtm_media_description = jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_description' ).val();
    
    // Update hidden field values
    jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).find( '.rtm_media_title' ).val( rtm_media_title );
    jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).find( '.rtm_media_description' ).val( rtm_media_description );
    
    // Update label below img
    jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).find( 'label' ).text( rtm_media_title.substring( 0, 20 ) );
    jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).find( 'label' ).attr( 'title', rtm_media_title );
    
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: { action: 'rtm_wp_gallery_media_update', 'id': rtm_id, 'media_id': rtm_media_id, 'media_title': rtm_media_title, 'description': rtm_media_description },
        success: function (result) {
            jQuery( '.rtmedia-attachment-save-success' ).show();
            setTimeout( function() {                
                jQuery( '.rtmedia-attachment-save-success' ).hide( 'slow' );
            }, 2000 );
        },
        error: function (data) {
            console.log(data);
        }
    });
});

// Delete selected media from the album when editing that album
jQuery( 'body' ).on( 'click', '.rtmedia-delete-attachment', function() {
    if( confirm( 'Are you sure you want to delete?' ) ) {
        var rtm_id_array = new Array();

        // store selected media id in array
        jQuery( '#rtmedia-edit-wp-gallery-content ul li.selected' ).each( function() {
            rtm_id_array.push( jQuery( this ).find( '.rtm_id' ).val() );
        });

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: { action: 'rtm_wp_gallery_media_delete', 'selected': rtm_id_array },
            success: function (result) {            
                jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_title' ).val('');
                jQuery( '.media-sidebar' ).find( '#rtmedia_attachment_description' ).html('');
                jQuery( '.media-sidebar h3, .media-sidebar .setting, .media-sidebar .save-button' ).hide();
                jQuery( '.rtmedia-delete-attachment' ).hide();
                jQuery( '.rtmedia-attachment-delete-success' ).show();
                setTimeout( function() {                
                    jQuery( '.rtmedia-attachment-delete-success' ).hide( 'slow' );
                }, 2000 );

                for( m = 0; m < rtm_id_array.length; m++ ) {
                    jQuery( '#' + rtm_id_array[ m ] ).remove();
                }
            },
            error: function (data) {
                console.log(data);
            }
        });
    }
});