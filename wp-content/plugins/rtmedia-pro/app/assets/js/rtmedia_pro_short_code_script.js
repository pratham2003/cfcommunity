var sel_el = document.getElementById( 'shortcode_select' );
var gal_el = document.getElementById( 'gallery_container' );
var upl_el = document.getElementById( 'uploader_container' );
var gal_cxt_el = document.getElementById( 'gal_context' );
var upl_cxt_el = document.getElementById( 'upl_context' );
var m_type_el = document.getElementById( 'media_type' );
var a_id_el = document.getElementById( 'author_id' );
var gal_cxt_id_el = document.getElementById( 'gal_context_id' );
var upl_cxt_id_el = document.getElementById( 'upl_context_id' );
var glb_el_true = document.getElementById( 'global_media_true' );
var glb_el_false = document.getElementById( 'global_media_false' );
var album_list_el = document.getElementById( 'album_list' );
var privacy_el = document.getElementById( 'privacy' );
var redirect_el = false;
var rtmedia_gal_upload_el = document.getElementById( 'rtmedia_gallery_uploader' );
var media_type_upload_el = document.getElementById( 'media_type_upload' );
var favlist_el = document.getElementById( 'favlist_id' );
var lighbox_el = document.getElementById( 'rtmedia_gallery_lightbox' );
var media_title_el = document.getElementById( 'rtmedia_gallery_media_title' );

function change_redirect( status ) {
    if ( status )
        redirect_el = true;
    else
        redirect_el = false;
}
sel_el.onchange = function () {
    if ( sel_el.value == 'gallery' ) {
        gal_el.style.display = "block";
        upl_el.style.display = "none";
    }
    else {
        gal_el.style.display = "none";
        upl_el.style.display = "block";
    }
};

function change_global( glb_el ) {
    if ( glb_el.value == "false" ) {
        document.getElementById( 'context_container' ).style.display = "block";
        document.getElementById( 'context_id_container' ).style.display = "block";
    }
    else {
        document.getElementById( 'context_container' ).style.display = "none";
        document.getElementById( 'context_id_container' ).style.display = "none";
    }
}


var rtmediaproshortcode = {
    init: { forced_root_blocks: false, convert_newlines_to_brs: true },

    insert: function () {
        tinyMCEPopup.editor.execCommand( 'mceInsertContent', true, tinyMCEPopup.dom.encode( rtmedia_pro_generate_shortcode() ) );
        tinyMCEPopup.close();
        return false;
    }
};


function rtmedia_pro_generate_shortcode() {
    var content;
    if ( sel_el.value == "gallery" ) {
        content = "[rtmedia_gallery";
        if ( glb_el_true.checked ) {
            content += " global=true";
        }
        else {
            if ( gal_cxt_el.value != "default" )
                content += " context=" + gal_cxt_el.value;
            if ( gal_cxt_id_el.value != "" )
                content += " context_id=" + gal_cxt_id_el.value;
        }
        if ( m_type_el.value != "all" ) {
            content += " media_type=" + m_type_el.value;
        }
        if ( a_id_el.value != "" ) {
            content += " media_author=" + a_id_el.value;
        }
        if ( favlist_el.value != "" ) {
            content += " favlist_id=" + favlist_el.value;
        }
        if ( rtmedia_gal_upload_el.value != "false" ) {
            content += " uploader=" + rtmedia_gal_upload_el.value;
        }
        if ( lighbox_el.value == "false" ) {
            content += " lightbox=false";
        }
        if ( media_title_el.value == "false" ) {
            content += " media_title=false";
        }
    }
    else {
        content = "[rtmedia_uploader";
        if ( upl_cxt_el.value != "default" )
            content += " context=" + upl_cxt_el.value;
        if ( upl_cxt_id_el.value != "" )
            content += " context_id=" + upl_cxt_id_el.value;
        if ( media_type_upload_el.value != "all" ) {
            content += " media_type=" + media_type_upload_el.value;
        }
        if ( album_list_el.value != "" )
            content += " album_id=" + album_list_el.value;
        if ( privacy_el.value != "" )
            content += " privacy=" + privacy_el.value;
        if ( redirect_el )
            content += " redirect=true";
    }
    content += "]";
    return content;
}
tinyMCEPopup.onInit.add( rtmediaproshortcode.init, rtmediaproshortcode );
