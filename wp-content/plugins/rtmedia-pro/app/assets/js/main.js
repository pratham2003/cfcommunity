function media_rate( value ) {
    if ( rt_user_logged_in == "1" ) {
        var url = jQuery( "#rtmedia_pro_rate_media" ).parent().attr( "action" );
        var loading_img = "<img class='rtm-rate-loading' src='" + rMedia_loading_file + "' />";
        //  jQuery("#rtmedia_pro_rate_media").parent().append(loading_img);
        jQuery( '.webwidget_rating_simple' ).after( loading_img );
        jQuery.post( url,
            {
                value: value
            },
            function ( data ) {
                data = JSON.parse( data );
                jQuery( ".rtm-rate-loading" ).remove();
                jQuery( "#rtmedia_pro_media_average_rating" ).text( data['average'].toFixed( 1 ) );
                jQuery( "#rtmedia_pro_media_user_rating" ).text( value );
            }
        );
    }

}

function rtmedia_init_rating() {
    if ( jQuery( "#rtmedia_pro_rate_media" ).length ) {
        jQuery( "#rtmedia_pro_rate_media" ).webwidget_rating_simple( {
            rating_star_length: '5',
            rating_initial_value: jQuery( "#rtmedia_pro_rate_media" ).val(),
            rating_function_name: 'media_rate', //this is function name for click
            directory: rtmedia_pro_url + '/lib/rating-simple'
        } );
    }
}

function init_playlist() {
    if ( jQuery( '.rtmp-media-playlist' ).length ) {
        jQuery( '.rtmp-media-playlist' ).mediaelementplayer( {
            loop: true,
            shuffle: false,
            playlist: true,
            playlistposition: 'bottom',
            features: ['playlistfeature', 'prevtrack' , 'playpause', 'nexttrack', 'loop', 'shuffle', 'playlist', 'current' , 'progress', 'duration', 'volume']
        } );
    }
}

jQuery( document ).ready( function ( $ ) {

    // group media settings show/hide more settings based on whether media is enabled for particular group or not
    if ( $( '#rt_group_media_enabled' ).length ) {
        if ( !$( '#rt_group_media_enabled' ).is( ':checked' ) ) {
            $( '.rtmedia-group-media-settings' ).hide();
        }
        $( '#rt_group_media_enabled' ).change( function () {
            $( '.rtmedia-group-media-settings' ).slideToggle();
        } );

    }
    init_playlist();
    rtmedia_init_rating();
    rtMediaHook.register(
        'rtmedia_js_popup_after_content_added',
        function ( args ) {
            init_playlist();
            rtmedia_init_rating();
            jQuery( '.rtmedia-like-counter-wrap' ).attr( 'title', rtmedia_media_who_liked );
            if( typeof rtsocial_init_counters == "function") {
                rtsocial_init_counters();
            }
        }
    );

    jQuery( '.rtmedia-container' ).on( 'click', '.rtmedia-create-new-playlist-button', function ( e ) {
        jQuery( '#rtmedia-create-new-playlist-container' ).slideToggle();
    } );

    jQuery( '#new-playlist-modal' ).on( 'click', '#rtmedia_create_new_playlist', function ( e ) {
        $playlistname = jQuery.trim( jQuery( '#rtmedia_playlist_name' ).val() );
        $context = jQuery.trim( jQuery( '#rtmedia_playlist_context' ).val() );
        $context_id = jQuery.trim( jQuery( '#rtmedia_playlist_context_id' ).val() );
        $privacy = jQuery.trim( jQuery( '#new-playlist-modal .privacy' ).val() );
        if ( !$playlistname == "" ) {
            var data = {
                action: 'rtmedia_create_playlist',
                name: $playlistname,
                context: $context,
                context_id: $context_id,
                privacy: $privacy
            };
            jQuery( "#rtmedia_create_new_playlist" ).attr( 'disabled', 'disabled' );
            var old_val = jQuery( "#rtmedia_create_new_playlist" ).html();
            jQuery( "#rtmedia_create_new_playlist" ).prepend( "<img src='" + rMedia_loading_file + "'/>" );
            jQuery.post( rtmedia_ajax_url, data, function ( response ) {
                response = response.trim();
                if ( response ) {
                    jQuery( "#rtmedia_create_new_playlist" ).html( old_val );
                    jQuery( '#rtmedia-create-new-playlist-container' ).hide();
                    jQuery( '#rtmedia_playlist_name' ).val( "" );
                    jQuery( "#rtmedia_create_new_playlist" ).removeAttr( 'disabled' );
                    jQuery( "#rtmedia_create_new_playlist" ).after( "<span class='rtmedia-success rtmedia-create-playlist-alert'><b>" + $playlistname + "</b> " + rtmedia_playlist_created_msg + "</span>" );
                    setTimeout( function () {
                        jQuery( ".rtmedia-create-playlist-alert" ).remove()
                    }, 4000 );


                } else {
                    alert( rtmedia_playlist_creation_error_msg );
                }
            } );

        } else {
            alert( rtmedia_empty_playlist_name_msg );
        }
    } );

    //remove media from playlist
    $( document ).on( "click", '.rtmedia-remove-media-from-playlist', function ( e ) {

        e.preventDefault();
        if ( confirm( rtmedia_playlist_delete_confirmation ) ) {
            var that = this;
            var param = {
                action: 'rtmedia_remove_media_from_playlist',
                media_id: this.id,
                playlist_id: $( '.rtmedia-playlist-media-list' ).attr( 'id' )
            };
            $.ajax( {
                url: rtmedia_ajax_url,
                type: 'post',
                data: param,
                success: function ( data ) {
                    if ( $.trim( data ) === "true" ) {
                        $( that ).closest( 'tr' ).hide();
                        $( that ).closest( 'tr' ).remove();
                    }
                }
            } );
        }
    } );

    //get the "add-toplaylist" form
    $( document ).on( "click", '.rtmedia-add-to-playlist', function ( e ) {

        e.preventDefault();
        var that = this;
        $( this ).attr( 'disabled', 'disabled' );
        var url = $( this ).parent().attr( "action" );
        $( that ).prepend( "<img src='" + rMedia_loading_file + "' class='rtm-playlist-loading' />" );
        $.ajax( {
            url: url,
            type: 'post',
            data: "action=get_form",
            success: function ( data ) {

                //$(that).html("Add to Playlist");
                $( '.rtm-playlist-loading' ).remove();
                $( that ).removeAttr( 'disabled' );
                $( "#rtmp-playlist-form" ).html( data );
            }
        } );

    } );

    $( document ).on( "change", '#playlist-list', function ( e ) {

        var playlist = $.trim( $( '#playlist-list' ).val() );
        if ( playlist === '-1' ) {
            $( '#new-playlist-container' ).css( 'display', 'inline' );
            $( '#playlist-cancel' ).css( 'display', 'inline' );
            $( '#playlist-select' ).css( 'display', 'none' );

        }
        else {
            $( '#new-playlist-container' ).css( 'display', 'none' );
            $( '#playlist-cancel' ).css( 'display', 'none' );
        }
    } );

    $( document ).on( "click", '#playlist-cancel', function ( e ) {
        e.preventDefault();
        $( '#playlist-select' ).css( 'display', 'inline-block' );
        $( '#playlist-list' ).val( '0' );
        $( '#new-playlist-container' ).css( 'display', 'none' );

        $( this ).css( 'display', 'none' );
    } );

    // add media to the playlist
    $( document ).on( "click", '.add-to-rtmp-playlist', function ( e ) {

        e.preventDefault();
        var that = this;
        var playlistId = $( '#playlist-list' ).val();
        //var parameters = "json=true&action=add&playlist_id=" + playlistId;
        var data = { json: true, action: 'add', playlist_id: playlistId };

        if ( playlistId === "" || playlistId === "0" ) {
            alert( rtmedia_select_playlist_msg );
            return false;
        }

        if ( playlistId === '-1' ) {
            var playlist_name = $.trim( $( '#playlist_name' ).val() );
            var privacy = $.trim( $( '#rtSelectPrivacy' ).val() );

            if ( playlist_name == "" ) {
                alert( rtmedia_empty_playlist_name_msg );
                return false;
            }
            data['playlist_name'] = playlist_name;
            data['privacy'] = privacy;
            // parameters += "&playlist_name='" + playlist_name + "'&privacy=" + privacy;
        }
        // console.log(data);
        $( this ).attr( 'disabled', 'disabled' );
        var url = $( this ).parent().attr( "action" );
        $( that ).prepend( "<img src='" + rMedia_loading_file + "' />" );
        $.ajax( {
            url: url,
            type: 'post',
            data: data,
            success: function ( data ) {
                try {
                    data = JSON.parse( data );
                    if ( data.next == "success" ) {
                        $( '#rtmp-playlist-form' ).after( "<div class='clear rtmedia-add-to-playlist-alert'><span class='rtmedia-success'>" + rtmedia_playlist_media_added_msg + "</span></div>" );
                        setTimeout( function () {
                            jQuery( ".rtmedia-add-to-playlist-alert" ).remove()
                        }, 3000 );
                    }
                } catch ( e ) {

                }
                $( "#rtmp-playlist-form" ).html( '' );
            }
        } );

    } );


    //set cover
    $( document ).on( "click", '.rtmedia-cover', function ( e ) {

        e.preventDefault();
        var that = this;
        $( this ).attr( 'disabled', 'disabled' );
        var url = $( this ).parent().attr( "action" );
        $( that ).prepend( "<img src='" + rMedia_loading_file + "' />" );
        $.ajax( {
            url: url,
            type: 'post',
            data: "json=true",
            success: function ( data ) {
                try {
                    data = JSON.parse( data );
                } catch ( e ) {

                }
                $( that ).hide();
            }
        } );
    } );


    //set profile pic
    $( document ).on( "click", '.rtmedia-set-profile-picture', function ( e ) {
        e.preventDefault();
        var that = this;
        $( this ).attr( 'disabled', 'disabled' );
        var url = $( this ).parent().attr( "action" );
        $( that ).prepend( "<img src='" + rMedia_loading_file + "' />" );
        $.ajax( {
            url: url,
            type: 'post',
            data: "json=true",
            success: function ( data ) {
                try {
                    data = JSON.parse( data );
                } catch ( e ) {

                }
                $( that ).hide();
            }
        } );


    } );

    $( document ).on( "click", '.rtmedia-moderate', function ( e ) {
        e.preventDefault();
        var that = this;
        $( this ).attr( 'disabled', 'disabled' );
        var url = $( this ).parent().attr( "action" );
        $( that ).prepend( "<img class='moderate-loading' src='" + rMedia_loading_file + "' />" );
        $.ajax( {
            url: url,
            type: 'post',
            data: "json=true",
            success: function ( data ) {
                try {
                    data = JSON.parse( data );
                } catch ( e ) {

                }
                if ( data.rt_redirect != "" )
                    window.location.href = data.rt_redirect;
                $( that ).children( 'span' ).html( data.next );
                $( '.moderate-loading' ).remove();
                $( that ).removeAttr( 'disabled' );
            }
        } );
    } );

    function rtmedia_pro_upload_limit_check( upload_config, file_obj ) {
        var allow_flag = true;
        var td_error_message = '';
        if ( !( typeof( upload_config.rtmedia_pro_upload_limits ) != "undefined" && typeof( upload_config.rtmedia_pro_upload_limits.files ) != "undefined" && typeof( upload_config.rtmedia_pro_upload_limits.size ) != "undefined" && typeof( upload_config.rtmedia_pro_upload_limits_current_stats ) != "undefined" && typeof( upload_config.rtmedia_pro_upload_limits_current_stats.files ) != "undefined" && typeof( upload_config.rtmedia_pro_upload_limits_current_stats.size ) != "undefined" ) ) {
            return true;
        }
        // return true if no limit set
        if ( upload_config.rtmedia_pro_upload_limits.size.daily != "0" && upload_config.rtmedia_pro_upload_limits.size.monthly != "0" && upload_config.rtmedia_pro_upload_limits.size.lifetime != "0" && upload_config.rtmedia_pro_upload_limits.files.daily != "0" && upload_config.rtmedia_pro_upload_limits.files.monthly != "0" && upload_config.rtmedia_pro_upload_limits.files.lifetime != "0" ) {
            return true;
        }
        // check for daily + size limit
        if ( allow_flag && upload_config.rtmedia_pro_upload_limits.size.daily != "0" && upload_config.rtmedia_pro_upload_limits_current_stats.size.daily >= parseInt( upload_config.rtmedia_pro_upload_limits.size.daily ) * ( 1024 * 1024 ) ) {
            td_error_message = ( typeof( rtmedia_pro_upload_limit_messages ) == "object" ) ? rtmedia_pro_upload_limit_messages.size.daily : "You have exceeded daily quota for file size limit.";
            allow_flag = false;
        }
        // check for monthly + size limit
        if ( allow_flag && upload_config.rtmedia_pro_upload_limits.size.monthly != "0" && upload_config.rtmedia_pro_upload_limits_current_stats.size.monthly >= parseInt( upload_config.rtmedia_pro_upload_limits.size.monthly ) * ( 1024 * 1024 ) ) {
            td_error_message = ( typeof( rtmedia_pro_upload_limit_messages ) == "object" ) ? rtmedia_pro_upload_limit_messages.size.monthly : "You have exceeded monthly quota for file size limit.";
            allow_flag = false;
        }
        // check for lifetime + size limit
        if ( allow_flag && upload_config.rtmedia_pro_upload_limits.size.lifetime != "0" && upload_config.rtmedia_pro_upload_limits_current_stats.size.lifetime >= parseInt( upload_config.rtmedia_pro_upload_limits.size.lifetime ) * ( 1024 * 1024 ) ) {
            td_error_message = ( typeof( rtmedia_pro_upload_limit_messages ) == "object" ) ? rtmedia_pro_upload_limit_messages.size.lifetime : "You have exceeded lifetime quota for file size limit.";
            allow_flag = false;
        }
        // check for daily + file limit
        if ( allow_flag && upload_config.rtmedia_pro_upload_limits.files.daily != "0" && parseInt( upload_config.rtmedia_pro_upload_limits_current_stats.files.daily ) >= parseInt( upload_config.rtmedia_pro_upload_limits.files.daily ) ) {
            td_error_message = ( typeof( rtmedia_pro_upload_limit_messages ) == "object" ) ? rtmedia_pro_upload_limit_messages.files.daily : "You have exceeded daily quota to upload files.";
            allow_flag = false;
        }
        // check for monthly + file limit
        if ( allow_flag && upload_config.rtmedia_pro_upload_limits.files.monthly != "0" && parseInt( upload_config.rtmedia_pro_upload_limits_current_stats.files.monthly ) >= parseInt( upload_config.rtmedia_pro_upload_limits.files.monthly ) ) {
            td_error_message = ( typeof( rtmedia_pro_upload_limit_messages ) == "object" ) ? rtmedia_pro_upload_limit_messages.files.monthly : "You have exceeded monthly quota to upload files.";
            allow_flag = false;
        }
        // check for lifetime + file limit
        if ( allow_flag && upload_config.rtmedia_pro_upload_limits.files.lifetime != "0" && parseInt( upload_config.rtmedia_pro_upload_limits_current_stats.files.lifetime ) >= parseInt( upload_config.rtmedia_pro_upload_limits.files.lifetime ) ) {
            td_error_message = ( typeof( rtmedia_pro_upload_limit_messages ) == "object" ) ? rtmedia_pro_upload_limit_messages.files.lifetime : "You have exceeded lifetime quota to upload files.";
            allow_flag = false;
        }
        // if all is good than return true otherwise show error
        if ( allow_flag ) {
            return true;
        } else {
            return td_error_message;
        }
    }


    rtMediaHook.register( 'rtmedia_js_file_added',
        function ( up ) {
            var tmp_array;
            var ext = '';
            var tr = '';
            var allow_upload;
            tmp_array = up[1].name.split( "." );
            if ( tmp_array.length > 1 ) {
                ext = tmp_array[tmp_array.length - 1];
                ext = ext.toLowerCase();
                if ( typeof(up[0].settings.upload_size) != "undefined" && typeof(up[0].settings.upload_size[ext]) != "undefined" && typeof(up[0].settings.upload_size[ext]['size']) != "undefined" ) {

                    if ( (up[0].settings.upload_size[ext]["size"] == 0 || ( up[0].settings.upload_size[ext]["size"] > 0 && (up[0].settings.upload_size[ext]["size"] * 1024 * 1024) >= up[1].size )) ) {
                        // check for daily/monthly/lifetime upload limits
                        allow_upload = rtmedia_pro_upload_limit_check( up[0].settings, up[1] );
                        if ( allow_upload === true ) {
                            if( typeof( up[0].settings.rtmedia_pro_upload_limits_current_stats ) != "undefined" ) {
                                // update all the limit variables as new will be file added
                                up[0].settings.rtmedia_pro_upload_limits_current_stats.size.daily = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.size.daily ) + up[1].size ).toString();
                                up[0].settings.rtmedia_pro_upload_limits_current_stats.size.monthly = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.size.monthly ) + up[1].size ).toString();
                                up[0].settings.rtmedia_pro_upload_limits_current_stats.size.lifetime = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.size.lifetime ) + up[1].size ).toString();
                                up[0].settings.rtmedia_pro_upload_limits_current_stats.files.daily = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.files.daily ) + 1 ).toString();
                                up[0].settings.rtmedia_pro_upload_limits_current_stats.files.monthly = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.files.monthly ) + 1 ).toString();
                                up[0].settings.rtmedia_pro_upload_limits_current_stats.files.lifetime = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.files.lifetime ) + 1 ).toString();
                            }

                            return true;
                        } else {
                            tr = "<tr class='upload-error' id='" + up[1].id + "'><td>" + up[1].name + "</td><td colspan='2'>" + allow_upload + "</td><td></td><td class='close error_delete right'>&times;</td></tr>";
                            jQuery( up[2] ).append( tr );
                        }
                    } else {
                        tr = "<tr class='upload-error' id='" + up[1].id + "'><td>" + up[1].name + "</td><td> " + rtmedia_pro_max_file_size + " " + up[0].settings.upload_size[ext]["size"] + " MB <i class='rtmicon-info-circle' title='" + window.file_size_info + "'></i></td><td>" + plupload.formatSize( up[1].size ) + "</td><td></td><td class='close error_delete right'>&times;</td></tr>";
                        jQuery( up[2] ).append( tr );
                    }
                }

                jQuery( '.error_delete' ).on( 'click', function ( e ) {
                    e.preventDefault();
                    jQuery( this ).parent( 'tr' ).remove();
                } );
                return false;

            } else {
                return false;
            }
        }
    );

    rtMediaHook.register( 'rtmedia_js_file_remove',
        function ( up ) {
            if( typeof( up[0].settings.rtmedia_pro_upload_limits_current_stats ) != "undefined" ) {
                up[0].settings.rtmedia_pro_upload_limits_current_stats.size.daily = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.size.daily ) - up[1].size ).toString();
                up[0].settings.rtmedia_pro_upload_limits_current_stats.size.monthly = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.size.monthly ) - up[1].size ).toString();
                up[0].settings.rtmedia_pro_upload_limits_current_stats.size.lifetime = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.size.lifetime ) - up[1].size ).toString();
                up[0].settings.rtmedia_pro_upload_limits_current_stats.files.daily = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.files.daily ) - 1 ).toString();
                up[0].settings.rtmedia_pro_upload_limits_current_stats.files.monthly = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.files.monthly ) - 1 ).toString();
                up[0].settings.rtmedia_pro_upload_limits_current_stats.files.lifetime = ( parseInt( up[0].settings.rtmedia_pro_upload_limits_current_stats.files.lifetime ) - 1 ).toString();
            }
        }
    );

    jQuery( "form#new-post" ).attr( "enctype", "multipart/form-data" );
    jQuery( "form#new-post" ).attr( "encoding", "multipart/form-data" );
    jQuery( "form#commentform" ).attr( "enctype", "multipart/form-data" );
    jQuery( "form#commentform" ).attr( "encoding", "multipart/form-data" );
    jQuery( 'body' ).on( 'click', '.rtmedia-like-counter-wrap', function ( e ) {
        e.preventDefault();
        jQuery( '.media-likes-wrapper' ).show();
        jQuery( '.media-likes .loading-gif' ).show();
        $media_id = jQuery( '.current-media-item' ).val();
        jQuery.ajax( {
            type: 'POST',
            url: rtmedia_ajax_url,
            data: {
                action: 'rtm_media_likes',
                media_id: $media_id
            },
            success: function ( response_data ) {
                jQuery( '.media-likes .loading-gif' ).hide();
                if ( response_data ) {
                    $likes_list = '<ul class="media-like-list">';
                    $likes_list += response_data;
                    $likes_list += '</ul>';

                } else {
                    $likes_list = "<p>" + rtmedia_media_no_likes + "</p>";
                }
                jQuery( '.media-like-list' ).remove();
                jQuery( '.media-likes' ).append( $likes_list );
            },
            error: function ( response_error ) {
                console.log( response_error );
            }
        } );
    } );
    jQuery( document ).keyup( function ( e ) {
        if ( e.keyCode == 27 || e.keyCode == 37 || e.keyCode == 39 ) {
            jQuery( '.media-likes-wrapper' ).hide();
            jQuery( '.media-like-list' ).remove();
        }   // esc
    } );
    jQuery( 'body' ).on( 'click', '.media-likes .close', function () {
        jQuery( '.media-likes-wrapper' ).hide();
        jQuery( '.media-like-list' ).remove();
    } );
    jQuery( document ).mouseup( function ( e ) {
        var container = jQuery( ".media-likes" );

        if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
            container.parent().hide();
        }
    } );

    if ( jQuery( '.rtmedia-like-counter-wrap' ).length > 0 ) {
        jQuery( '.rtmedia-like-counter-wrap' ).attr( 'title', rtmedia_media_who_liked );
    }
    var rtmedia_file_extension = [];
    var rtmedia_file_size_limit = [];
    if ( typeof rtMedia_plupload_config == "object" ) {
        var extn = rtMedia_plupload_config.filters[0].extensions.split( "," );
        jQuery.each( extn, function ( key, val ) {
            rtmedia_file_extension.push( val );
        } );
        jQuery.each( rtMedia_plupload_config.upload_size, function ( key, val ) {
            rtmedia_file_size_limit[key] = val['size'];
        } );
    }


    jQuery( '#rtmedia_simple_file_input' ).change( function () {
        if ( typeof rtmedia_file_extension == "object" ) {
            var content = "<table class='rtMedia-simple-queue-list'>";
            for ( var i = 0; i < jQuery( this ).get( 0 ).files.length; ++i ) {
                var ext = jQuery( this ).get( 0 ).files[i].name.split( '.' ).pop().toLowerCase();
                if ( jQuery.inArray( ext, rtmedia_file_extension ) < 0 ) {
                    content += "<tr class='upload-error'>";
                    content += "<td>" + jQuery( this ).get( 0 ).files[i].name + "</td>";
                    content += "<td>" + rtmedia_file_extension_error_msg + "</td>";
                    content += "</tr>";
                }
                else if ( typeof rtmedia_file_size_limit[ext] != "undefined" && ( rtmedia_file_size_limit[ext] != 0 && jQuery( this ).get( 0 ).files[i].size / ( 1024 * 1024 ) > rtmedia_file_size_limit[ext] ) ) {
                    content += "<tr class='upload-error'>";
                    content += "<td>" + jQuery( this ).get( 0 ).files[i].name + "</td>";
                    content += "<td>" + rtmedia_max_file_msg + " " + rtmedia_file_size_limit[ext] + " MB</td>";
                    content += "</tr>";
                } else {
                    content += "<tr class='upload-success'>";
                    content += "<td>" + jQuery( this ).get( 0 ).files[i].name + "</td>";
                    content += "<td>&nbsp;</td>";
                    content += "</tr>";
                }
            }
            jQuery( '.rtm-file-input-container .rtMedia-simple-queue-list' ).remove();
            jQuery( '.rtm-file-input-container' ).append( content );
        }
    } );

    // delete media from gallery page under the user's profile when user clicks the delete button on the gallery item.
    jQuery( '.rtmedia-list-media' ).on( 'click', '.rtmp-delete-media' , function ( e ) {
        e.preventDefault();
        if ( confirm( rtmedia_media_delete_confirmation ) ) { // if user confirms, send ajax request to delete the selected media
            var curr_li = jQuery( this ).closest( 'li' );

            var data = {
                action: 'rtmedia_delete_user_media',
                media_id: curr_li.attr( 'id' )
            };

            jQuery.ajax( {
                url: ajaxurl,
                type: 'post',
                data: data,
                success: function ( data ) {
                    if ( data == '1' ) {
                        //media delete
                        curr_li.remove();
                        if( typeof rtmedia_masonry_layout != "undefined" && rtmedia_masonry_layout == "true" && jQuery( '.rtmedia-container .rtmedia-list.rtm-no-masonry' ).length == 0 ) {
                            rtm_masonry_reload( rtm_masonry_container );
                        }
                    } else { // show alert message
                        alert( rtmedia_file_not_deleted );
                    }
                }
            } );
        }

    } );

    // delete media-[document] from gallery page under the user's profile when user clicks the delete button on the gallery item.
    jQuery( '.rtmedia-list' ).on( 'click', '.rtmp-delete-media-document', function ( e ) {
        e.preventDefault();
        if ( confirm( rtmedia_media_delete_confirmation ) ) { // if user confirms, send ajax request to delete the selected media
            var curr_tr = jQuery( this ).closest( 'tr' );

            var data = {
                action: 'rtmedia_delete_user_media',
                media_id: curr_tr.attr( 'id' )
            };

            jQuery.ajax( {
                url: ajaxurl,
                type: 'post',
                data: data,
                success: function ( data ) {
                    if ( data == '1' ) {
                        //media delete
                        curr_tr.remove();
                    } else { // show alert message
                        alert( rtmedia_file_not_deleted );
                    }
                }
            } );
        }

    } );

    //add media to current playlist when add button is clicked for specific media under the playlist edit screen
    jQuery( '.rtmedia-playlist-media-list .rtmp-add-to-list' ).on( 'click', function ( e ) {
        e.preventDefault();

        var media = jQuery( this ).closest( 'tr' );

        var data = {
            action: 'add_media_to_playlist',
            media_id: media.attr( 'id' ),
            playlist_id: jQuery( '.rtmp-add-to-playlist-section #playlist_id' ).val(),
            nonce: jQuery( '.rtmp-add-to-playlist-section #rtmedia_media_nonce' ).val()
        };

        jQuery.ajax( {
            url: ajaxurl,
            type: 'post',
            data: data,
            success: function ( response ) {
                if ( response == '1' ) {
                    //media delete
                    media.remove();
                } else {

                }
            }
        } );


    } );

    // edit custom thumbnails
    jQuery( "#rtmedia_media_single_edit" ).attr( "enctype", "multipart/form-data" );
    jQuery( "#rtmedia_media_single_edit" ).attr( "encoding", "multipart/form-data" );

    // bulk media editing js
    //show bulk edit options when bulk edit button is clicked
    jQuery( '.rtmedia-bulk-edit' ).on( 'click', function ( e ) {
        e.preventDefault();
        jQuery( '.rtmedia-bulk-edit-options' ).show();
        //add bulk-edit-on class to the form
        jQuery( '.bulk-edit-form' ).addClass( 'bulk-edit-on' );
        jQuery( '.bulk-edit-form .rtmedia-list-item' ).each( function ( e ) { //add checkbox to available gallery items
            jQuery( this ).prepend( '<input type="checkbox" class="rtmedia-bulk-edit-item-selector bulk-action" name="selected[]" value="' + this.id + '"/>' );
        } );
        jQuery( '.bulk-edit-form .rtmedia-list-document-row' ).each( function ( e ) { //add checkbox to available gallery items
            jQuery( this ).find( 'td:nth-child(1)' ).prepend( '<input type="checkbox" class="rtmedia-bulk-edit-item-selector bulk-action" name="selected[]" value="' + this.id + '"/>' );
        } );
        jQuery( '.rtmedia_next_prev' ).addClass( 'rtm-hide' ); // hide the load more buttons
        //stop navigation to single media when bulk editing is ON.
        jQuery( '.bulk-edit-on .rtmedia-list-item > a' ).unbind( 'click' );
        jQuery( '.bulk-edit-on .rtmedia-list-item > a' ).click( function ( k ) {
            k.preventDefault();
            var that = jQuery( this ),
                checkbox = that.parent().find( ':checkbox' );

            if ( checkbox.is( ':checked' ) ) {
                checkbox.prop( 'checked', false );
                that.parent( 'li' ).removeClass( 'bulk-selected' );
                if ( jQuery( '.rtmedia-bulk-edit-options .unselect-all' ).length > 0 ) {
                    jQuery( '.rtmedia-bulk-edit-options .unselect-all' ).addClass( 'select-all' );
                    jQuery( '.rtmedia-bulk-edit-options .select-all' ).removeClass( 'unselect-all' );
                    jQuery( '.rtmedia-bulk-edit-options .select-all' ).html( '<i class="rtmicon-square-o"></i>' );
                }
            } else {
                checkbox.prop( 'checked', true );
                that.parent( 'li' ).addClass( 'bulk-selected' );
            }
        } );

        jQuery( '.rtmedia-list-media .rtmedia-bulk-edit-item-selector' ).change( function () {
            if ( jQuery( this ).is( ':checked' ) ) {
                jQuery( this ).prop( 'checked', true );
                jQuery( this ).parent( 'li' ).addClass( 'bulk-selected' );
            } else {
                jQuery( this ).prop( 'checked', false );
                jQuery( this ).parent( 'li' ).removeClass( 'bulk-selected' );
                if ( jQuery( '.rtmedia-bulk-edit-options .unselect-all' ).length > 0 ) {
                    jQuery( '.rtmedia-bulk-edit-options .unselect-all' ).addClass( 'select-all' );
                    jQuery( '.rtmedia-bulk-edit-options .select-all' ).removeClass( 'unselect-all' );
                    jQuery( '.rtmedia-bulk-edit-options .select-all' ).html( '<i class="rtmicon-square-o"></i>' );
                }
            }
        } );
        //stop the lightbox from opening when bulk edit mode is on
        jQuery( '.bulk-edit-on .rtmedia-list-item > a' ).addClass( 'no-popup' );
        // hide uploader when bulk edit is open
        jQuery( '#rtm-media-gallery-uploader' ).hide();
    } );

    jQuery( '.rtmedia-upload-media-link' ).click( function () {
        jQuery( '.bulk-edit-cancel' ).trigger( 'click' );
    } );

    //close bulk edit options when cancel button clicked
    jQuery( '.bulk-edit-cancel' ).on( 'click', function ( e ) {
        jQuery( '.rtmedia-bulk-edit-options' ).hide();
        //remove no-popup class from anchors so that lighbox can open
        jQuery( '.bulk-edit-on .rtmedia-list-item > a' ).removeClass( 'no-popup' );
        // remove class bulk-edit-on fromt the form when bulk edit mode is exited
        jQuery( '.bulk-edit-form' ).removeClass( 'bulk-edit-on' );

        //remove class 'rtm-hide' from load more button container
        jQuery( '.rtmedia_next_prev' ).removeClass( 'rtm-hide' );

        // remove check boxes
        jQuery( '.rtmedia-container .rtmedia-bulk-edit-item-selector' ).remove();
        jQuery( '.rtmedia-bulk-privacy-container' ).hide();
        jQuery( '.rtmedia-bulk-move-container' ).hide();
        jQuery( '.rtmedia-bulk-edit-attributes' ).hide();
        jQuery( '.rtmedia-bulk-action-message' ).hide();
        jQuery( '.rtmedia-list-media .rtmedia-list-item' ).removeClass( 'bulk-selected' );
    } );

    //bulk deleting
    jQuery( '.rtmedia-bulk-delete-selected' ).on( 'click', function ( e ) {
        var that = this;
        if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
            if ( confirm( rtmedia_selected_media_delete_confirmation ) ) {
                jQuery( this ).closest( 'form' ).attr( 'action', rtmedia_pro_user_domain + '/delete' ).submit();
            }
        } else {
            alert( rtmedia_no_media_selected );
        }
    } );

    //bulk media moving
    jQuery( '.rtmedia-bulk-move' ).on( 'click', function ( e ) {
        jQuery( '.rtmedia-bulk-privacy-container' ).hide();
        jQuery( '.rtmedia-bulk-edit-attributes' ).hide();
        jQuery( '.rtmedia-bulk-move-container' ).slideToggle();

    } );
    jQuery( '.rtmedia-bulk-move-selected' ).on( 'click', function ( e ) {
        if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
            if ( confirm( rtmedia_selected_media_move_confirmation ) ) {
                var media_id = new Array();
                jQuery( '.bulk-edit-on .rtmedia-list :checkbox:checked' ).each( function () {
                    media_id[ media_id.length ] = this.value;
                } );
                var new_album = jQuery( '.rtmedia-bulk-move-container .rtmedia-user-album-list' ).val();
                var nonce_field = jQuery( '.bulk-edit-form #rtmedia_media_nonce' ).val();
                var data = {
                    action: 'rtmedia_bulk_edit',
                    media_action: 'change_album',
                    medias: media_id,
                    album_id: new_album,
                    nonce: nonce_field
                };

                jQuery.post( ajaxurl, data, function ( response ) {

                    if ( response === '1' ) {
                        jQuery( '.rtmedia-bulk-action-message' ).removeClass( 'rtmedia-bulk-action-error-message' );
                        jQuery( '.rtmedia-bulk-action-message' ).addClass( 'rtmedia-bulk-action-success-message' );
                        jQuery( '.rtmedia-bulk-action-success-message' ).text( 'Media moved successfully.' );
                        jQuery( '.rtmedia-bulk-action-success-message' ).css( 'display', 'block' );
                    }
                    else {
                        jQuery( '.rtmedia-bulk-action-message' ).removeClass( 'rtmedia-bulk-action-success-message' );
                        jQuery( '.rtmedia-bulk-action-message' ).addClass( 'rtmedia-bulk-action-error-message' );
                        jQuery( '.rtmedia-bulk-action-error-message' ).text( 'Some error occured. Please try again.' );
                        jQuery( '.rtmedia-bulk-action-error-message' ).css( 'display', 'block' );
                    }
                } );
            }
        } else {
            alert( rtmedia_no_media_selected );
        }
    } );

    function rtmedia_get_media_attributes( selector ) {
        var attr_name = '';
        var new_attributes = new Array();
        jQuery( selector + ' [name^=rtmedia_attr]' ).each( function () {
            attr_name = jQuery( this ).attr( 'name' );
            if ( jQuery( this ).is( 'input:checkbox' ) ) {
                if ( jQuery( this ).is( ':checked' ) ) {
                    new_attributes.push( [ attr_name, jQuery( this ).val() ] );
                }
            }
            if ( jQuery( this ).is( 'select' ) ) {
                new_attributes.push( [ attr_name, jQuery( this ).val() ] );
            }
        } );
        return new_attributes;
    }

    // bulk change attributes
    jQuery( '.rtmedia-bulk-change-attributes' ).on( 'click', function ( e ) {
        jQuery( '.rtmedia-bulk-move-container' ).hide();
        jQuery( '.rtmedia-bulk-privacy-container' ).hide();
        jQuery( '.rtmedia-bulk-edit-attributes ' ).slideToggle();
    } );
    jQuery( '.rtmedia-bulk-media-attribute-save' ).on( 'click', function ( e ) {
        if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
            var media_id = new Array();
            var new_attributes;

            jQuery( '.bulk-edit-on .rtmedia-list :checkbox:checked' ).each( function () {
                media_id[ media_id.length ] = this.value;
            } );

            new_attributes = rtmedia_get_media_attributes( '.rtmedia-bulk-edit-attributes' );

            var nonce_field = jQuery( '.bulk-edit-form #rtmedia_media_nonce' ).val();

            var data = {
                action: 'rtmedia_bulk_edit',
                media_action: 'change_attributes',
                medias: media_id,
                media_attributes: new_attributes,
                nonce: nonce_field
            };

            jQuery.post( ajaxurl, data, function ( response ) {
                if ( response === '1' ) {
                    jQuery( '.rtmedia-bulk-action-message' ).removeClass( 'rtmedia-bulk-action-error-message' );
                    jQuery( '.rtmedia-bulk-action-message' ).addClass( 'rtmedia-bulk-action-success-message' );
                    jQuery( '.rtmedia-bulk-action-success-message' ).text( 'Attributes of the selected media changed successfully.' );
                    jQuery( '.rtmedia-bulk-action-success-message' ).css( 'display', 'block' );
                } else {
                    jQuery( '.rtmedia-bulk-action-message' ).removeClass( 'rtmedia-bulk-action-success-message' );
                    jQuery( '.rtmedia-bulk-action-message' ).addClass( 'rtmedia-bulk-action-error-message' );
                    jQuery( '.rtmedia-bulk-action-error-message' ).text( 'Some error occurred. Please try again.' );
                    jQuery( '.rtmedia-bulk-action-error-message' ).css( 'display', 'block' );
                }
            } );
        } else {
            alert( rtmedia_no_media_selected );
        }
    } );

    //bulk changing privacy
    jQuery( '.rtmedia-change-privacy' ).on( 'click', function ( e ) {
        jQuery( '.rtmedia-bulk-move-container' ).hide();
        jQuery( '.rtmedia-bulk-edit-attributes' ).hide();
        jQuery( '.rtmedia-bulk-privacy-container' ).slideToggle();
    } );
    jQuery( '.rtmedia-change-privacy-selected' ).on( 'click', function ( e ) {
        if ( jQuery( '.rtmedia-list :checkbox:checked' ).length > 0 ) {
            // if(confirm(rtmedia_selected_media_move_confirmation)){
            var media_id = new Array();
            jQuery( '.bulk-edit-on .rtmedia-list :checkbox:checked' ).each( function () {
                media_id[ media_id.length ] = this.value;
            } );
            var new_privacy = jQuery( '.rtmedia-bulk-privacy-container select.privacy' ).val();
            var nonce_field = jQuery( '.bulk-edit-form #rtmedia_media_nonce' ).val();
            var data = {
                action: 'rtmedia_bulk_edit',
                media_action: 'change_privacy',
                medias: media_id,
                privacy: new_privacy,
                nonce: nonce_field
            };
            jQuery.post( ajaxurl, data, function ( response ) {
                if ( response === '1' ) {
                    jQuery( '.rtmedia-bulk-action-message' ).removeClass( 'rtmedia-bulk-action-error-message' );
                    jQuery( '.rtmedia-bulk-action-message' ).addClass( 'rtmedia-bulk-action-success-message' );
                    jQuery( '.rtmedia-bulk-action-success-message' ).text( 'Privacy of the selected media changed successfully.' );
                    jQuery( '.rtmedia-bulk-action-success-message' ).css( 'display', 'block' );
                } else {
                    jQuery( '.rtmedia-bulk-action-message' ).removeClass( 'rtmedia-bulk-action-success-message' );
                    jQuery( '.rtmedia-bulk-action-message' ).addClass( 'rtmedia-bulk-action-error-message' );
                    jQuery( '.rtmedia-bulk-action-error-message' ).text( 'Some error occured. Please try again.' );
                    jQuery( '.rtmedia-bulk-action-error-message' ).css( 'display', 'block' );
                }
            } );
            //}
        } else {
            alert( rtmedia_no_media_selected );
        }
    } );

    // start -  fetch URL on activity
    jQuery.ajax = (function ( _ajax ) {
        var protocol = location.protocol,
            hostname = location.hostname,
            exRegex = RegExp( protocol + '//' + hostname ),
            YQL = 'http' + (/^https/.test( protocol ) ? 's' : '') + '://query.yahooapis.com/v1/public/yql?callback=?',
            query = 'select * from html where url="{URL}" and xpath="*"';

        function isExternal( url ) {
            return !exRegex.test( url ) && /:\/\//.test( url )
        }

        return function ( o ) {
            var url = o.url;
            if ( /get/i.test( o.type ) && !/json/i.test( o.dataType ) && isExternal( url ) ) {
                o.url = YQL;
                o.dataType = 'json';
                o.data = {
                    q: query.replace( '{URL}', url + (o.data ? (/\?/.test( url ) ? '&' : '?') + jQuery.param( o.data ) : '') ),
                    format: 'xml'
                };
                if ( !o.success && o.complete ) {
                    o.success = o.complete;
                    delete o.complete
                }
                o.success = (function ( _success ) {
                    return function ( data ) {
                        if ( _success ) {
                            _success.call( this, {
                                responseText: (data.results[0] || '').replace( /<script[^>]+?\/>|<script(.|\s)*?\/script>/gi, '' )
                            }, 'success' )
                        }
                    }
                })( o.success )
            }
            return _ajax.apply( this, arguments )
        }
    })( jQuery.ajax );

    String.prototype.startsWith = function ( str ) {
        return (this.match( "^" + str ) == str)
    };

    var rtmp_urlInUse = '';
    var rtmp_url_imgSrcArray = [];
    var rtmp_url_imgArrayCounter = 0;
    var rtmp_url_tid;
    var rtmp_url_setNewTimer = false;
    var rtmp_url_preview = true;
    var rtmp_embed_urls = ['youtube.com', 'www.youtube.com', 'vimeo.com', 'www.vimeo.com'];

    function rtmp_url_scrapeURL( urlText ) {
        var url_a = document.createElement( 'a' );
        url_a.href = urlText;
        var hostname = url_a.hostname;
        if( rtmp_embed_urls.indexOf( hostname ) != -1 ){
            jQuery( '#rtmp-url-scrapper' ).hide();
            jQuery( '.rtmp-url-scrapper-container' ).hide();
            return;
        }
        if ( urlText.indexOf( 'http://' ) >= 0 ) {
            urlString = rtmp_url_getUrl( 'http://', urlText );
            rtmp_load_url_preview( urlString );
        } else if ( urlText.indexOf( 'https://' ) >= 0 ) {
            urlString = rtmp_url_getUrl( 'https://', urlText );
            rtmp_load_url_preview( urlString );
        } else if ( urlText.indexOf( 'www.' ) >= 0 ) {
            urlString = rtmp_url_getUrl( 'www', urlText );
            rtmp_load_url_preview( urlString );
        } else {
            jQuery( '#rtmp-url-scrapper' ).hide();
            jQuery( '.rtmp-url-scrapper-container' ).hide();
        }
    }

    function rtmp_load_url_preview( urlString ) {
        if ( rtmp_is_valid_url( urlString ) ) {
            rtmp_url_getUrlData( urlString );
        }
    }

    function rtmp_url_abortTimer() {
        if ( null != rtmp_url_tid ) clearTimeout( rtmp_url_tid )
    }

    jQuery( '#whats-new' ).addClass( 'linkBox' );

    jQuery( '#whats-new-textarea' ).append( '<div class="rtmp-url-scrapper-container"><img class="rtmp-url-scrapper-loading" src="' + rMedia_loading_media + '" /><table id="rtmp-url-scrapper"><tr><td><table id="rtmp-url-scrapper-img-holder"><tr><td colspan="2" style="height:100px; overflow:hidden;"><div id="rtmp-url-scrapper-img"><img src="" /></div></td></tr><tr><td id="" style="width:50%; text-align:right;"><input type="button" id="rtmp-url-prevPicButton" value="<"></td><td id="" style="width:50%; text-align:left;"><input type="button" id="rtmp-url-nextPicButton" value=">"></td></tr><tr><td colspan="2"><div id="rtmp-url-scrapper-img-count"></div></td></tr></table></td><td><table id="rtmp-url-scrapper-text-holder"><tr><td ><div id="rtmp-url-scrapper-title"></div></td></tr><tr><td ><div id="rtmp-url-scrapper-url"></div></td></tr><tr><td ><br/><div id="rtmp-url-scrapper-description"></div></td></tr></table></td><td style="vertical-align:top;"><a title="Cancel Preview" href="#" id="rtmediacloselinksuggestion">x</a></td></tr></table></div><div id="rtmp-url-error"></div>' );

    jQuery( '#whats-new-form' ).append( '<input type="hidden" id="rtmp-url-scrapper-img-hidden" name="rtmp-url-scrapper-img-hidden" value="" /><input type="hidden" id="rtmp-url-scrapper-title-hidden"  name="rtmp-url-scrapper-title-hidden" value="" /><input type="hidden" id="rtmp-url-scrapper-url-hidden" name="rtmp-url-scrapper-url-hidden" value="" /><input type="hidden" id="rtmp-url-scrapper-description-hidden" name="rtmp-url-scrapper-description-hidden" value="" /><input type="hidden" id="rtmp-url-no-scrapper" name="rtmp-url-no-scrapper" value="1" />' );

    jQuery( '#rtmp-url-scrapper' ).hide();

    jQuery( "#rtmp-url-nextPicButton" ).click( function ( $ ) {
        rtmp_url_imgArrayCounter++;
        if ( rtmp_url_imgArrayCounter >= rtmp_url_imgSrcArray.length ) rtmp_url_imgArrayCounter = 0;
        //jQuery('#rtmp-url-scrapper_img').css('backgroundImage', 'url(' + imgSrcArray[imgArrayCounter] + ')');
        jQuery( '#rtmp-url-scrapper-img' ).find( 'img' ).attr( 'src', rtmp_url_imgSrcArray[rtmp_url_imgArrayCounter] );
        jQuery( '#rtmp-url-scrapper-img-hidden' ).val( rtmp_url_imgSrcArray[rtmp_url_imgArrayCounter] );
        jQuery( '#rtmp-url-scrapper-img-count' ).text( (rtmp_url_imgArrayCounter + 1) + ' of ' + rtmp_url_imgSrcArray.length )
    } );

    jQuery( "#rtmp-url-prevPicButton" ).click( function ( $ ) {
        rtmp_url_imgArrayCounter--;
        if ( rtmp_url_imgArrayCounter < 0 ) rtmp_url_imgArrayCounter = rtmp_url_imgSrcArray.length - 1;
        //jQuery('#rtmp-url-scrapper_img').css('backgroundImage', 'url(' + imgSrcArray[imgArrayCounter] + ')');
        jQuery( '#rtmp-url-scrapper-img' ).find( 'img' ).attr( 'src', rtmp_url_imgSrcArray[rtmp_url_imgArrayCounter] );
        jQuery( '#rtmp-url-scrapper-img-hidden' ).val( rtmp_url_imgSrcArray[rtmp_url_imgArrayCounter] );
        jQuery( '#rtmp-url-scrapper-img-count' ).text( (rtmp_url_imgArrayCounter + 1) + ' of ' + rtmp_url_imgSrcArray.length )
    } );

    jQuery( "#rtmediacloselinksuggestion" ).click( function ( e ) {
        e.preventDefault();
        jQuery( '.rtmp-url-scrapper-container' ).hide();
        jQuery( '#rtmp-url-no-scrapper' ).val( "0" );
    } );

    jQuery( ".linkBox" ).on( "keyup", function ( event ) {
        if ( jQuery( '#rtmp-url-no-scrapper' ).val() === "1" ) {
            urlText = this.value;
            rtmp_url_abortTimer();
            rtmp_url_tid = setTimeout( function () {
                rtmp_url_scrapeURL( urlText )
            }, 1000 );
            if ( event.which == 13 ) {
                this.rows++
            }
        }
    } );

    function rtmp_url_getUrlData( urlString ) {
        jQuery( '.rtmp-url-scrapper-container' ).show();
        jQuery( '.rtmp-url-scrapper-loading' ).show();
        jQuery( '#rtmp-url-scrapper' ).hide();
        jQuery( '#rtmp-url-error' ).hide();
//        if ( rtmp_urlInUse == urlString ) {
//            jQuery( '.rtmp-url-scrapper-loading' ).hide();
//            jQuery( '#rtmp-url-scrapper' ).show();
//            return;
//        }
        var ajaxdata = {
            action: 'rtm_url_parser',
            url: urlString
        }
        jQuery.ajax( {
            url: ajaxurl,
            data: ajaxdata,
            type: 'POST',
            dataType: 'json',
            success: function ( res ) {
                if ( res.title == "" ) {
                    jQuery( '.rtmp-url-scrapper-container' ).hide();
                    return;
                }
                jQuery( '.rtmp-url-scrapper-loading' ).hide();
                if( res.error == '' ){
                    jQuery( '#rtmp-url-error' ).hide();
                    jQuery( '#rtmp-url-scrapper' ).show();
                    rtmp_urlInUse = urlString;
                    var imgSrc;
                    var title = '';
                    jQuery( '#rtmp-url-scrapper-description' ).text( '' );
                    jQuery( '#rtmp-url-scrapper-description-hidden' ).val( '' );
                    jQuery( '#rtmp-url-scrapper-title' ).text( '' );
                    jQuery( '#rtmp-url-scrapper-title-hidden' ).val( '' );
                    jQuery( '#rtmp-url-scrapper-url' ).text( '' );
                    jQuery( '#rtmp-url-scrapper-url-hidden' ).val( '' );
                    jQuery( '#rtmp-url-scrapper-img' ).css( 'backgroundImage', '' );
                    rtmp_url_imgSrcArray = [];
                    rtmp_url_imgArrayCounter = 0;
                    title = res.title;
                    jQuery( '#rtmp-url-scrapper-title' ).text( title );
                    jQuery( '#rtmp-url-scrapper-title-hidden' ).val( title );
                    jQuery( '#rtmp-url-scrapper-url' ).text( urlString );
                    jQuery( '#rtmp-url-scrapper-url-hidden' ).val( urlString );
                    jQuery( '#rtmp-url-scrapper-description' ).text( res.description );
                    jQuery( '#rtmp-url-scrapper-description-hidden' ).val( res.description );
                    jQuery.each( res.images, function( index, value ){
                        rtmp_url_imgSrcArray.push( value );
                    });
                } else {
                    jQuery( '#rtmp-url-error' ).text( res.error );
                    jQuery( '.rtmp-url-scrapper-container' ).hide();
                    jQuery( '#rtmp-url-error' ).show();
                }

                //jQuery('#rtmp-url-scrapper_img').css('backgroundImage', 'url(' + imgSrcArray[imgArrayCounter] + ')');
                jQuery( '#rtmp-url-scrapper-img' ).find( 'img' ).attr( 'src', rtmp_url_imgSrcArray[rtmp_url_imgArrayCounter] );
                jQuery( '#rtmp-url-scrapper-img-hidden' ).val( rtmp_url_imgSrcArray[rtmp_url_imgArrayCounter] );
                jQuery( '#rtmp-url-scrapper-img-count' ).text( '1 of ' + rtmp_url_imgSrcArray.length );
            }
        } )
    }

    rtMediaHook.register(
        'rtmedia_js_after_activity_added',
        function () {
            jQuery( '.rtmp-url-scrapper-container' ).hide();
            jQuery( '#rtmp-url-no-scrapper' ).val( "1" ) // URL link preview
            jQuery( '#rtmp-url-scrapper-description' ).text( '' );
            jQuery( '#rtmp-url-scrapper-description-hidden' ).val( '' );
            jQuery( '#rtmp-url-scrapper-title' ).text( '' );
            jQuery( '#rtmp-url-scrapper-title-hidden' ).val( '' );
            jQuery( '#rtmp-url-scrapper-url' ).text( '' );
            jQuery( '#rtmp-url-scrapper-url-hidden' ).val( '' );
            jQuery( '#rtmp-url-scrapper-img' ).css( 'backgroundImage', '' );
        }
    );

    // end -  fetch URL on activity
    //rtmedia_lightbox_enabled from setting
    if ( typeof(rtmedia_lightbox_enabled) != 'undefined' && rtmedia_lightbox_enabled == "1" ) {
        apply_rtMagnificPopup( '.widget-item-listing' );
    }

    // media attributes in uploader
    jQuery( '#rtmedia-upload-add-attributes-button' ).on( 'click', function () {
        jQuery( '.rtmedia-editor-attributes' ).slideToggle();
        if ( jQuery( '.rtmedia-allow-upload-attribute' ).val() == "1" ) {
            jQuery( '.rtmedia-allow-upload-attribute' ).val( '0' );
        } else {
            jQuery( '.rtmedia-allow-upload-attribute' ).val( '1' );
        }
    } );

    // add media attributes after file upload
    rtMediaHook.register(
        'rtmedia_js_after_file_upload',
        function ( args ) {
            var media_obj;
            var data;
            // args = [up, file, res.response];
            if ( jQuery( '.rtmedia-allow-upload-attribute' ).length > 0 ) {
                if ( jQuery( '.rtmedia-allow-upload-attribute' ).val() == "1" ) {
                    media_obj = JSON.parse( args[2] );
                    var new_attributes;
                    new_attributes = rtmedia_get_media_attributes( '#rtmedia-upload-container' );
                    data = {
                        action: 'rtmedia_add_media_attributes_after_upload',
                        media_id: media_obj.media_id,
                        media_attributes: new_attributes,
                    };
                    jQuery.post( ajaxurl, data, function () {
                    } );
                }
            }
        }
    );

    // check for terms and condition
    rtMediaHook.register(
        'rtmedia_js_upload_file',
        function ( args ) {
            var alter_msg = ( ( typeof rtmedia_check_terms_message ) == "string" ) ? rtmedia_check_terms_message : 'Please check terms and conditions.';
            if ( args == false ) {
                return args;
            }
            if ( jQuery( '#rtmedia_upload_terms_conditions' ).length > 0 ) {
                if ( !jQuery( '#rtmedia_upload_terms_conditions' ).is( ':checked' ) ) {
                    alert( alter_msg );
                    return false;
                }
            }
            return true;
        }
    );

    function rtmp_url_show_upload( urlText ) {
        var url = '';
        var invalid_url = ( ( typeof rtmedia_invalid_url_message ) == "string" ) ? rtmedia_invalid_url_message : 'You had entered invalid URL.';
        if ( urlText.indexOf( 'http://' ) >= 0 ) {
            url = rtmp_url_getUrl( 'http://', urlText );
        } else if ( urlText.indexOf( 'https://' ) >= 0 ) {
            url = rtmp_url_getUrl( 'https://', urlText );
        } else if ( urlText.indexOf( 'www.' ) >= 0 ) {
            url = rtmp_url_getUrl( 'www', urlText );
        } else {
            jQuery( '.start-media-upload' ).hide();
            alert( invalid_url );
            rtmedia_url_media_upload = false;
            return;
        }
        if ( rtmp_is_valid_url( url ) ) {
            if ( rtmp_is_valid_file_url( url ) ) {
//                rtmp_get_url_file_size( url );
                jQuery( '.start-media-upload' ).show();
                rtmedia_url_media_upload = true;
                rtmedia_url_upload_file_name = decodeURI( url ).split( '/' ).pop().substring( 0, 40 );
                if ( jQuery( '#rtm-url-upload' ).length > 0 ) {
                    jQuery( '#rtm-url-upload .rtm-url-upload-file-name' ).html( rtmedia_url_upload_file_name );
                    jQuery( '#rtm-url-upload .rtm-url-upload-file-status' ).html( rtmedia_waiting_msg );
                    jQuery( '#rtm-url-upload .plupload_media_edit' ).html( '' );
                    jQuery( '#rtm-url-upload .plupload_delete' ).html( '' );
                    jQuery( '#rtm-url-upload' ).removeClass();
                    jQuery( '#rtm-url-upload' ).addClass( 'upload-waiting' );
                } else {
                    tdName = document.createElement( "td" );
                    tdName.className = "rtm-url-upload-file-name";
                    tdName.innerHTML = rtmedia_url_upload_file_name;
                    tdStatus = document.createElement( "td" );
                    tdStatus.className = "rtm-url-upload-file-status";
                    tdStatus.innerHTML = rtmedia_waiting_msg;
                    tdSize = document.createElement( "td" );
                    tdSize.className = "rtm-url-upload-file-size";
                    tdDelete = document.createElement( "td" );
                    tdDelete.className = "close plupload_delete";
                    tdEdit = document.createElement( "td" );
                    tdEdit.className = "plupload_media_edit";
                    tdEdit.innerHTML = "";
                    tr = document.createElement( "tr" );
                    tr.className = 'upload-waiting';
                    tr.id = 'rtm-url-upload';
                    tr.appendChild( tdName );
                    tr.appendChild( tdStatus );
                    tr.appendChild( tdSize );
                    tr.appendChild( tdEdit );
                    tr.appendChild( tdDelete );
                    jQuery( "#rtMedia-queue-list" ).append( tr );
                }
            } else {
                alert( rtmedia_file_extension_error_msg );
                rtmedia_url_media_upload = false;
            }
        } else {
            alert( invalid_url );
            rtmedia_url_media_upload = false;
        }
    }

    function rtmp_is_valid_file_url( url ) {
        if ( typeof rtMedia_plupload_config == "object" ) {
            var valid = rtmedia_is_valid_url_file( url );
            if ( valid == null ) {
                return false;
            }
            if ( typeof valid[1] == 'string' ) {
                if ( rtMedia_plupload_config.filters[0].extensions.indexOf( valid[1] ) != '-1' ) {
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    // upload media by URL
    jQuery( '#rtmedia_url_upload_input' ).on( 'keyup', function ( event ) {
//        if ( !rtmedia_url_media_upload ) {
        urlText = this.value;
        rtmp_url_abortTimer();
        rtmp_url_tid = setTimeout( function () {
            rtmp_url_show_upload( urlText );
        }, 1000 );
//        }
    } );

    rtMediaHook.register(
        'rtmedia_js_after_files_uploaded',
        function () {
            if ( rtmedia_url_media_upload && typeof rtMedia_plupload_config == "object" && jQuery( '#rtmedia_url_upload_input' ).length > 0 && jQuery( '#rtmedia_url_upload_input' ).val() != '' ) {
                allow_upload = rtmedia_pro_upload_limit_check( rtMedia_plupload_config, '' );
                if ( allow_upload ) {
                    url = rtMedia_plupload_config.url;

                    var privacy = jQuery( "#rtm-file_upload-ui select.privacy" ).val();
                    var album_id = '';
                    var up_params = new Object();
                    if ( jQuery( "#rt_upload_hf_redirect" ).length > 0 )
                        up_params['redirect'] = 1;
                    jQuery( "#rtmedia-uploader-form input[type=hidden]" ).each( function () {
                        up_params[$( this ).attr( "name" )] = $( this ).val();
                    } );
                    up_params['activity_id'] = activity_id;
                    if ( jQuery( '#rtmedia-uploader-form .rtmedia-user-album-list' ).length > 0 ) {
                        album_id = jQuery( '#rtmedia-uploader-form .rtmedia-user-album-list' ).find( ":selected" ).val();
                    } else if ( jQuery( '#rtmedia-uploader-form .rtmedia-current-album' ).length > 0 ) {
                        album_id = jQuery( '#rtmedia-uploader-form .rtmedia-current-album' ).val();
                    }
                    up_params['album_id'] = album_id;
                    up_params['mode'] = 'url_upload';
                    up_params['url'] = jQuery( '#rtmedia_url_upload_input' ).val();
                    jQuery( '#rtm-url-upload' ).removeClass();
                    jQuery( '#rtm-url-upload' ).addClass( 'upload-progress' );
                    jQuery( '#rtm-url-upload .rtm-url-upload-file-status' ).html( rtmedia_uploading_msg );
                    var xhr = jQuery.post( url, up_params, function ( res ) {
                        if ( res.media_id != null ) {

                            rtMediaHook.call( 'rtmedia_js_after_file_upload', [ '', '', xhr.responseText ] );

                            // set upload limit parameters
                            if( typeof( rtMedia_plupload_config.rtmedia_pro_upload_limits_current_stats ) != "undefined" ) {
                                rtMedia_plupload_config.rtmedia_pro_upload_limits_current_stats.files.daily = ( parseInt( rtMedia_plupload_config.rtmedia_pro_upload_limits_current_stats.files.daily ) + 1 ).toString();
                                rtMedia_plupload_config.rtmedia_pro_upload_limits_current_stats.files.monthly = ( parseInt( rtMedia_plupload_config.rtmedia_pro_upload_limits_current_stats.files.monthly ) + 1 ).toString();
                                rtMedia_plupload_config.rtmedia_pro_upload_limits_current_stats.files.lifetime = ( parseInt( rtMedia_plupload_config.rtmedia_pro_upload_limits_current_stats.files.lifetime ) + 1 ).toString();
                            }
                            jQuery( '#rtm-url-upload' ).removeClass();
                            jQuery( '#rtm-url-upload' ).addClass( 'upload-success' );
                            jQuery( '#rtm-url-upload .rtm-url-upload-file-status' ).html( rtmedia_uploaded_msg );

                            if ( res.permalink != '' ) {
                                jQuery( '#rtm-url-upload .rtm-url-upload-file-name' ).html( "<a href='" + res.permalink + "' target='_blank' title='" + res.permalink + "'>" + rtmedia_url_upload_file_name + "</a>" );
                                jQuery( '#rtm-url-upload .plupload_media_edit' ).html( "<a href='" + res.permalink + "edit' target='_blank'><span title='" + rtmedia_edit_media + "'><i class='rtmicon-edit'></i> " + rtmedia_edit + "</span></a>" );
                                jQuery( '#rtm-url-upload .plupload_delete' ).html( "<span id='" + res.media_id + "' class='rtmedia-delete-uploaded-media' title='" + rtmedia_delete + "'>&times;</span>" );
                            }
                        } else {
                            alert( 'Fail to upload file from URL.' );
                            jQuery( '#rtm-url-upload' ).removeClass();
                            jQuery( '#rtm-url-upload' ).addClass( 'upload-error' );
                            jQuery( '#rtm-url-upload .rtm-url-upload-file-status' ).html( rtmedia_upload_failed_msg );
                        }
                        jQuery( '#rtmedia_url_upload_input' ).val( '' );
                        rtmedia_url_media_upload = false;
                    } );
                }
            }
            return true;
        }
    );

    //get the "add-to-favlist" form
    $( document ).on( "click", '.rtmedia-add-to-favlist', function ( e ) {

        e.preventDefault();
        var that = this;
        $( this ).attr( 'disabled', 'disabled' );
        var url = $( this ).parent().attr( "action" );
        $( that ).prepend( "<img src='" + rMedia_loading_file + "' class='rtm-favlist-loading' />" );
        $.ajax( {
            url: url,
            type: 'post',
            data: "action=get_form",
            success: function ( data ) {

                $( '.rtm-favlist-loading' ).remove();
                $( that ).removeAttr( 'disabled' );
                $( "#rtmp-favlist-form" ).html( data );
            }
        } );

    } );

    $( document ).on( "change", '#rtm-favlist-list', function ( e ) {

        var favlist = $.trim( $( '#rtm-favlist-list' ).val() );
        if ( favlist === '-1' ) {
            $( '#rtm-new-favlist-container' ).css( 'display', 'inline' );
            $( '#rtm-favlist-cancel' ).css( 'display', 'inline' );
            $( '#rtm-favlist-select' ).css( 'display', 'none' );

        }
        else {
            $( '#rtm-new-favlist-container' ).css( 'display', 'none' );
            $( '#rtm-favlist-cancel' ).css( 'display', 'none' );
        }
    } );

    $( document ).on( "click", '#rtm-favlist-cancel', function ( e ) {
        e.preventDefault();
        $( '#rtm-favlist-select' ).css( 'display', 'inline-block' );
        $( '#rtm-favlist-list' ).val( '0' );
        $( '#rtm-new-favlist-container' ).css( 'display', 'none' );

        $( this ).css( 'display', 'none' );
    } );


    $( document ).on( "click", '.add-to-rtmp-favlist', function ( e ) {

        e.preventDefault();
        var that = this;
        var favlistId = $( '#rtm-favlist-list' ).val();
        var data = { json: true, action: 'add', favlist_id: favlistId };

        if ( favlistId === "" || favlistId === "0" ) {
            alert( rtmedia_select_favlist_msg );
            return false;
        }

        if ( favlistId === '-1' ) {
            var favlist_name = $.trim( $( '#rtm_favlist_name' ).val() );
            var privacy = $.trim( $( '#rtSelectPrivacy' ).val() );

            if ( favlist_name == "" ) {
                alert( rtmedia_empty_favlist_name_msg );
                return false;
            }
            data['favlist_name'] = favlist_name;
            data['privacy'] = privacy;
        }
        // console.log(data);
        $( this ).attr( 'disabled', 'disabled' );
        var url = $( this ).parent().attr( "action" );
        $( that ).prepend( "<img src='" + rMedia_loading_file + "' />" );
        $.ajax( {
            url: url,
            type: 'post',
            data: data,
            success: function ( data ) {
                try {
                    data = JSON.parse( data );
                    if ( data.next == "success" ) {
                        if( typeof favlist_name == "undefined") {
                            favlist_name = $('#rtm-favlist-list').find(":selected").text();
                        }
                        $( '#rtmp-favlist-form' ).after( "<div class='clear rtmedia-add-to-favlist-alert'><span class='rtmedia-success'>" + rtmedia_favlist_media_added_msg + " - " + favlist_name + " </span></div>" );
                        setTimeout( function () {
                            jQuery( ".rtmedia-add-to-favlist-alert" ).remove()
                        }, 3000 );
                    }
                } catch ( e ) {

                }
                $( "#rtmp-favlist-form" ).html( '' );
            }
        } );

    } );

    jQuery( '#new-favlist-modal' ).on( 'click', '#rtmedia_create_new_favlist', function ( e ) {
        $favlistname = jQuery.trim( jQuery( '#rtmedia_favlist_name' ).val() );
        $privacy = jQuery.trim( jQuery( '#new-favlist-modal .privacy' ).val() );
        if ( !$favlistname == "" ) {
            var data = {
                action: 'rtmedia_create_favlist',
                name: $favlistname,
                privacy: $privacy
            };
            jQuery( "#rtmedia_create_new_favlist" ).attr( 'disabled', 'disabled' );
            var old_val = jQuery( "#rtmedia_create_new_favlist" ).html();
            jQuery( "#rtmedia_create_new_favlist" ).prepend( "<img src='" + rMedia_loading_file + "'/>" );
            jQuery.post( rtmedia_ajax_url, data, function ( response ) {
                response = response.trim();
                if ( response ) {
                    jQuery( "#rtmedia_create_new_favlist" ).html( old_val );
                    jQuery( '#rtmedia-create-new-favlist-container' ).hide();
                    jQuery( '#rtmedia_favlist_name' ).val( "" );
                    jQuery( "#rtmedia_create_new_favlist" ).removeAttr( 'disabled' );
                    jQuery( "#rtmedia_create_new_favlist" ).after( "<span class='rtmedia-success rtmedia-create-favlist-alert'><b>" + $favlistname + "</b> " + rtmedia_favlist_created_msg + "</span>" );
                    galleryObj.reloadView();
                    setTimeout( function () {
                        jQuery( ".rtmedia-create-favlist-alert" ).remove()
                    }, 4000 );
                } else {
                    alert( rtmedia_favlist_creation_error_msg );
                }
            } );

        } else {
            alert( rtmedia_empty_playlist_name_msg );
        }
    } );

    $( document ).on( "click", '.rtmedia-remove-media-from-favlist', function ( e ) {

        e.preventDefault();
        if ( confirm( rtmedia_favlist_delete_confirmation ) ) {
            var that = this;
            var param = {
                action: 'rtmedia_remove_media_from_favlist',
                media_id: this.id,
                favlist_id: $( '.rtmedia-favlist-media-list' ).attr( 'id' )
            };
            $.ajax( {
                url: rtmedia_ajax_url,
                type: 'post',
                data: param,
                success: function ( data ) {
                    if ( $.trim( data ) === "true" ) {
                        $( that ).closest( 'tr' ).hide();
                        $( that ).closest( 'tr' ).remove();
                    }
                }
            } );
        }
    } );
} );

var rtmedia_url_media_upload = false;
var rtmedia_url_upload_file_name = '';

function rtmp_is_valid_url( url ) {
    var regexp = /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
    return regexp.test( url );
}

function rtmp_url_getUrl( prefix, urlText ) {
    var urlString = '';
    var startIndex = urlText.indexOf( prefix );
    for ( var i = startIndex; i < urlText.length; i++ ) {
        if ( urlText[i] == ' ' || urlText[i] == '\n' ) break;
        else urlString += urlText[i]
    }
    if ( prefix === 'www' ) {
        prefix = 'http://';
        urlString = prefix + urlString;
    }
    return urlString
}

function rtmp_get_url_file_size( url ) {
    var xhr = jQuery.ajax( {
        type: "HEAD",
        url: url,
        crossDomain: true,
        success: function ( data ) {
//            console.log( xhr.getResponseHeader( "Content-Length" ) );
        }
    } );
}

function rtmedia_is_valid_url_file( url ) {
    var regex = /.+\.([^?]+)(\?|$)/;
    return url.match( regex );
}


// sorting
function rtmedia_sort_gallery( el, type, order ) {
    if ( rtm_is_element_exist( '#rt_upload_hf_sort_by' ) && rtm_is_element_exist( '#rt_upload_hf_sort_order' ) ) {
        jQuery( '#rt_upload_hf_sort_by' ).val( type );
        jQuery( '#rt_upload_hf_sort_order' ).val( order );
    }
    if ( typeof galleryObj == "object" ) {
        if( typeof(rtmedia_document_other_table_view) !== "undefined" && typeof(rtmedia_sort_media_type) !== "undefined" && rtmedia_sort_media_type == "document" && rtmedia_document_other_table_view == "1" ) {
            rtmedia_sort_document_other_table( type, order );
        } else {
            galleryObj.reloadView();
        }
    } else {
        alert( 'There seem\'s to be some problem. Please try again later. ' );
    }
}

// Function for sorting documents and other types of media in a tabular format
function rtmedia_sort_document_other_table( type, order ) {
    var sortAsc = ( order == 'asc' ) ? true : false;
    var $table  = jQuery( '.rtmedia-container .rtmedia-list-media' );
    var $rows   = jQuery( 'tbody > tr', $table );
    
    $rows.sort( function( a, b ) {
        var keyA = '';
        var keyB = '';
        
        if( type == 'date' ) {
            keyA = jQuery( 'td:eq(1)', a ).data( 'value' );
            keyB = jQuery( 'td:eq(1)', b ).data( 'value' );
        } else if( type == 'size' ) {
            keyA = jQuery( 'td:eq(2)', a ).data( 'value' );
            keyB = jQuery( 'td:eq(2)', b ).data( 'value' );
        } else if( type == 'title' ) {
            keyA = jQuery( 'td:eq(0)', a ).data( 'value' );
            keyB = jQuery( 'td:eq(0)', b ).data( 'value' );
        }

        if ( sortAsc ) {
            return ( keyA > keyB ) ? 1 : 0;     // A bigger than B, sorting ascending
        } else {
            return ( keyA < keyB ) ? 1 : 0;     // B bigger than A, sorting descending
        }
    });
    
    $rows.each( function( index, row ){
        $table.append( row );                    // append rows after sort
    });
}
