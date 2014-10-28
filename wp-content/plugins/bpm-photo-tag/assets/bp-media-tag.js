jQuery(window).load(function(){
	$currently_is_tagging = false;
	$media_id = 0;
	$context = jQuery('.tagcontainer').find('img');
	jQuery('.tagcontainer').css(
		{
			'height': $context.css('height'),
			'width': $context.css('width')
		});
	jQuery('body').on('click','button.bp-media-tag-button',function(e){
		$tagdiv = jQuery(this).closest('.rtmedia-single-container').find('.rtmedia-single-media').first();
		$tagdiv.addClass('taggable');
		$image = $tagdiv.find('#rtmedia-tag-form');
		jQuery('.bp-media-content-wrap .modal-ctrl').css('display','none');
		jQuery(this).children('span').text(rtm_photo_tag_str.tag_done_txt);
		jQuery(this).removeClass('bp-media-tag-button').addClass('bp-media-tag-done-button');
		$currently_is_tagging = true;
		jQuery('.tagcontainer').css('cursor','crosshair');

	});

	jQuery('body').on('click','button.bp-media-tag-done-button',function(e){
		$tagdiv = jQuery(this).closest('.rtmedia-single-container').find('.rtmedia-single-media').first();
		$tagdiv.removeClass('taggable');
		$image = $tagdiv.find('#rtmedia-tag-form');
		$image.find('.tag-ui').remove();
		jQuery('.bp-media-content-wrap .modal-ctrl').css('display','block');
		jQuery('.bp-media-tag').removeClass('highlight');
		jQuery(this).children('span').text(rtm_photo_tag_str.tag_txt);
		jQuery(this).removeClass('bp-media-tag-done-button').addClass('bp-media-tag-button');
		$currently_is_tagging = false;
		jQuery('.tagcontainer').css('cursor','default');
	});

	jQuery('body').on('click','.tagbox a.close',function(e){
		e.preventDefault();
		$media_id = jQuery(this).closest('.rtmedia-media').attr('id').replace('rtmedia-media-','');
		$box = jQuery(this).closest('.tagbox');
		data = $box.find('input').serialize();
		data += '&action=rtmedia_delete_tag&media_id='+$media_id+'&tag_nonce='+rtm_photo_tag_str.tag_nonce;
		jQuery.get(
					ajaxurl,
					data,
				function( tags ) {
					if(tags>0){
						$box.closest('.bp-media-tag').remove();
					}
				});

	});



	jQuery('body').on('click','.tagcontainer',function(e){

		if($currently_is_tagging!=false){

			$context = jQuery(this).find('img');


			jQuery(this).find('.tag-ui').remove();
			$tagbox = jQuery('<div class="tag-ui"></div>');
			coffset =$context.offset();

			ex = Math.round(((e.pageX - coffset.left)/parseInt($context.css('width')))*1000)/10;
			ey = Math.round(((e.pageY - coffset.top )/parseInt($context.css('height')))*1000)/10;
			$tagbox.css({
				'top':ey+'%',
				'left':ex+'%'
			});
			jQuery(this).find('form#rtmedia-tag-form').append($tagbox);
			$tagbox.append('<div class="tagbox">\n\
								<input type="hidden" class="bp-media-tagatr" name="bp-media-tagger[]" />\n\
								<input type="hidden" class="bp-media-tagatr" name="bp-media-tag-top[]" value="'+ey+'" />\n\
								<input type="hidden" class="bp-media-tagatr" name="bp-media-tag-left[]" value="'+ex+'"  />\n\
								<input type="text" id="bp-media-tag-input" />\n\
							</div>');
			$tagbox.append('<div class="tagged-user"><i class="bp-media-notch"></i></div>');
			jQuery("#bp-media-tag-input").focus();
		}
	});
	jQuery('body').on('mouseover','.bp-media-tag',function(e){
		jQuery(this).addClass('highlight');
	});
	jQuery('body').on('mouseout','.bp-media-tag',function(e){
		jQuery(this).removeClass('highlight');
	});

	jQuery('body').on('click','.tag-ui,.bp-media-tag',function(e){
		e.stopPropagation();

	});
	jQuery('body').on('focus','#bp-media-tag-input',function(e){
		var cache = {};
		$taginput = jQuery(this);
		$media_id = jQuery(this).closest('.rtmedia-media').attr('id').replace('rtmedia-media-','');
		$taginput.autocomplete({
			minLength: 2,
			source: function( request, response ) {
				var term = request.term;
				if ( term in cache ) {
					response( cache[ term ] );
					return;
				}

				jQuery.getJSON(
					ajaxurl,
					bpmdata = {
					'q':term,
					'limit': 10,
					'action':'rtmedia_get_taggable',
					'media_id': $media_id
				},
				function( data, status, xhr ) {
					cache[ term ] = data;
					response( data );
				});
			},
			appendTo: $taginput.closest('.tagbox'),
			select: function( event, ui ) {
				event.stopPropagation();
				$tagreplace = $taginput.closest('.tag-ui');
				$tagged_user = $tagreplace.find('.tagged-user');
				$tagreplace.addClass('bp-media-tag');
				jQuery('input.bp-media-tagatr[name^="bp-media-tagger"]').val(ui.item.tagger);
				$tagreplace.find('input.bp-media-tagatr').each(function(){
					$name = jQuery(this).attr('name');
					$newname = $name.replace('[]','['+ui.item.tagged+']');
					$tagreplace.find('.tagbox').attr('id','bpm-tag-'+ui.item.tagged).append('<a class="close" href="#"></a>');
					jQuery(this).attr('name', $newname);
				});
				$tagged_user.append( ui.item.tagged_url );
				$taginput.remove();
				$tagreplace.removeClass('tag-ui');
				data = jQuery('#rtmedia-tag-form').serialize();
				data += '&action=rtmedia_save_tags&media_id='+$media_id;
				jQuery.get(
					ajaxurl,
					data,
				function( tags ) {
					//nothing!
				});
				return false;
			}
		})
		.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			return jQuery( '<li class="bp-media-tag-list-item"></li>' )
				.data("item.autocomplete",item)
				.append( '<a>'+item.label+'</a>' )
				.appendTo( ul );
		};
	});

});