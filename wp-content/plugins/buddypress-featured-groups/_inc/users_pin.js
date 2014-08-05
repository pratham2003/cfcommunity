jQuery(document).ready(function(){
    jQuery('#groups-list').on('click', '.featured-group-button a', function() {
        var gid   = jQuery(this).parent().attr('id').split('-')[1];
        var nonce = jQuery(this).attr('href').split('?_wpnonce=')[1].split('&')[0];
        var link  = jQuery(this);

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                action: 'bpfg_users_pin_ajax',
                method: link.attr('class').split('-')[0] + '_group',
                'cookie': bp_get_cookies(),
                'gid': gid,
                '_wpnonce': nonce
            }
        })
            .done(function(data){
                switch ( data.status ){
                    case 'success':
                        link.text(data.message);
                        link.removeClass().addClass(data.class);
                        break;

                    case 'error':
                        // do noting for now
                        break;
                }
            });

        return false;
    });
});