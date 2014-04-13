(function($) {
  "use strict";
	jQuery(document).ready(function(){
		$(".switch-button").click(function(){
			
		    $('html, body').animate({
		        scrollTop: $("#navigation-wrapper").offset().top
		    }, 1000);			
			
			$("#lightoff").fadeToggle();
		});	
		
		$('#lightoff').click(function(){
			$('#lightoff').hide();
		});			
		$('.social-share-buttons').css('display','none');
		$('a.share-button').on( "click", function() {
			var id = $(this).attr('id');
			if( id == 'off' ){
				$('.social-share-buttons').slideDown(200);
				$(this).attr('id','on');
			}
			else{
				$('.social-share-buttons').slideUp(200);
				$(this).attr('id','off');
			}
		});
		$('table#wp-calendar').addClass('table');
		$('form#loginform > p > input.input').addClass('form-control');
		$(".comments-scrolling").click(function() {
		    $('html, body').animate({
		        scrollTop: $("div.comments").offset().top
		    }, 1000);
		});	
		$('form#mars-submit-video-form').submit(function(){
			var data_form = $(this).serialize();
			jQuery.ajax({
				type:'POST',
				data:data_form,
				url:mars_ajax_url,
				beforeSend:function(){
					$('form#mars-submit-video-form > div > span.help-block').text('');
					$("form#mars-submit-video-form > div.has-error").removeClass("has-error");
					$('form#mars-submit-video-form button[type="submit"]').text('Sending');
				},
				success:function(data){
					var data = $.parseJSON(data);
					if( data.resp == 'error' ){
						if(typeof data.element_id != 'undefined'){
							$('div.'+data.element_id).addClass('has-error');
							$('input#'+data.element_id).focus();
							$('div.'+data.element_id+' > span.help-block').text('*'+data.message);
						}
					}
					else if( data.resp == 'publish' ){
						window.location.href = data.redirect_to;
					}
					else if ( data.resp == 'success' ){
						if (typeof data.redirect_to !== 'undefined'){
							window.location.href = data.redirect_to;
						}
						else{
							$('form#mars-submit-video-form').remove();
							$('form#mars-submit-video-form').slideUp("slow", function() { $('form#mars-submit-video-form').remove();});
							$('div.post-entry').append('<div class="alert alert-success">'+data.message+'</div>');						
						}
					}
					$('form#mars-submit-video-form button[type="submit"]').text('Submit');
				}
			});
			return false;		
		});
		
		$('a.likes-dislikes').click(function(){
			var act = $(this).attr('action');
			var id = $(this).attr('id');
			var me = $(this);
			jQuery.ajax({
				type:'POST',
				data:'id='+id+'&action=actionlikedislikes&act='+act,
				url:mars_ajax_url,
				beforeSend:function(){
					$('div.alert').remove();
					$('a.likes-dislikes i').removeClass('fa-thumbs-up');
					$('a.likes-dislikes i').addClass('fa-spinner');
				},
				success:function(data){
					var data = $.parseJSON(data);
					if( data.resp =='error' ){
						$('div.video-options').before('<div class="alert alert-success alert-info">'+data.message+'</div>');
					}
					if( data.resp =='success' ){
						if (typeof(data.like) != "undefined"){
							$('label.likevideo'+id).text(data.like);
						}
					}
					$('a.likes-dislikes i').removeClass('fa-spinner');
					$('a.likes-dislikes i').addClass('fa-thumbs-up');
				}
			});
			return false;		
		});
		$('form#mars-subscribe-form').submit(function(){
			var name = $('form#mars-subscribe-form input#name').val();
			var email = $('form#mars-subscribe-form input#email').val();
			var referer = $('form#mars-subscribe-form input[name="referer"]').val();
			var agree = $('form#mars-subscribe-form input#agree').is(':checked');
			jQuery.ajax({
				type:'POST',
				data:'action=mars_subscrib_act&name='+name+'&email='+email+'&agree='+agree+'&referer='+referer,
				url:mars_ajax_url,
				beforeSend:function(){
					$('form#mars-subscribe-form button[type="submit"]').text('...');
					$('div.alert').remove();
				},
				success:function(data){
					var data = $.parseJSON(data);
					if( data.resp == 'error' ){
						$('form#mars-subscribe-form div.name').before('<div class="alert alert-warning">'+data.message+'</div>');
						$('form#mars-subscribe-form input#'+data.id).focus();
					}
					else{
						$('form#mars-subscribe-form div.name').before('<div class="alert alert-success">'+data.message+'</div>');
						window.location.href = data.redirect_to;
					}
					$('form#mars-subscribe-form button[type="submit"]').text( $('form#mars-subscribe-form input[name="submit-label"]').val());
				}				
			});
			return false;
		});
		$('form#loginform').submit(function(){
			var data_form = $(this).serialize();
			jQuery.ajax({
				type:'POST',
				data: data_form,
				url: mars_ajax_url,
				beforeSend:function(){
					$('.alert-danger').slideUp('slow');
					$('form#loginform input[type="submit"]').val('...');
				},				
				success: function(data){
					var data = $.parseJSON(data);
					if( data.resp == 'error' ){
						$('.alert-danger').html(data.message);
						$('.alert-danger').slideDown('slow');
					}
					else if( data.resp =='success' ){
						window.location.href = data.redirect_to;
					}
					$('form#loginform input[type="submit"]').val( $('input[name="button_label"]').val() );
				}
			});
			return false;
		});
		$('form#registerform').submit(function(){
			var data_form = $(this).serialize();
			jQuery.ajax({
				type:'POST',
				data: data_form,
				url: mars_ajax_url,
				beforeSend:function(){
					$('.alert-danger').slideUp('slow');
					$('form#registerform input[type="submit"]').val('...');
				},				
				success: function(data){
					var data = $.parseJSON(data);
					if( data.resp == 'error' ){
						$('.alert-danger').html(data.message);
						$('.alert-danger').slideDown('slow');
					}
					else if( data.resp =='success' ){
						window.location.href = data.redirect_to;
					}
					$('form#registerform input[type="submit"]').val( $('form#registerform input[name="button_label"]').val() );
				}
			});
			return false;
		});		
		$('form#lostpasswordform').submit(function(){
			var data_form = $(this).serialize();
			jQuery.ajax({
				type:'POST',
				data: data_form,
				url: mars_ajax_url,
				beforeSend:function(){
					$('.alert-danger').slideUp('slow');
					$('form#lostpasswordform button[type="submit"]').text('...');
				},				
				success: function(data){
					var data = $.parseJSON(data);
					if( data.resp == 'error' ){
						$('.alert-danger').html(data.message);
						$('.alert-danger').slideDown('slow');
					}
					else if( data.resp =='success' ){
						window.location.href = data.redirect_to;
					}
					$('form#lostpasswordform button[type="submit"]').text( $('form#lostpasswordform input[name="button_label"]').val() );
				}
			});
			return false;			
		});
		
	});
})(jQuery);









