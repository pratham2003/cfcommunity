(function() {
    tinymce.create('tinymce.plugins.rtmedia_pro_short_code', {
        init : function(ed, url) {
            ed.addButton('rtmedia_pro_short_code', {
                title : 'rtMedia pro short code',
                cmd : 'mcertMediaProShortCode',
                image: url.replace('/js', '')+'/img/bp-media-icon.png'
            });
            ed.addCommand('mcertMediaProShortCode', function() {
                        ed.windowManager.open({
                            file: rtmedia_pro_ajax_url  + '?action=rtmedia_shortcode_editor',
                            width:parseInt(300),
                            height:parseInt(400),
                            inline:1
                        });
                });
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname : 'rtMediaproshortcode',
                author : 'rtCamp'
            };
        }
    });
    tinymce.PluginManager.add( 'rtmedia_pro_short_code', tinymce.plugins.rtmedia_pro_short_code );
})();
