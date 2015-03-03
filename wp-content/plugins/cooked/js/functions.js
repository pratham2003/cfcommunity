;(function($, window, document, undefined) {
	var $win = $(window);
	var $doc = $(document);
	var loading;

	$doc.ready(function() {
		$('.hint-check').on('click', function(e) {
			e.preventDefault();
			$(this).toggleClass('checked');
		});
		
		if ($('#cooked-video-lb').length){
			$('.fancy-video').fancybox({
				fitToView	: true,
				width		: '90%',
				height		: 'auto',
				autoSize	: false,
				type		: 'inline'
			});
		}
		
		// Check Review Fields before Submitting
		if ($('.rev-box input[type="submit"]').length){
			$('.rev-box input[type="submit"]').on('click',function(e) {
			
				// Not logged in
				if ($('.rev-box input[name="author"]').length){
				
					if ($('.rev-box input[name="rating"]').length && $('.rev-box input[name="rating"]').val() && $('.rev-box textarea#comment').val() && $('.rev-box input[name="author"]').val() && $('.rev-box input[name="email"]').val() || !$('.rev-box input[name="rating"]').length && $('.rev-box textarea#comment').val() && $('.rev-box input[name="author"]').val() && $('.rev-box input[name="email"]').val() || cp_star_review_optional && $('.rev-box textarea#comment').val() && $('.rev-box input[name="author"]').val() && $('.rev-box input[name="email"]').val()){
						$('.rev-box .no-rating-error').hide();
					} else {
						e.preventDefault();
						$('.rev-box .no-rating-error').fadeOut(200).fadeIn(200);
					}

				// Logged in
				} else {
				
					if ($('.rev-box input[name="rating"]').length && $('.rev-box input[name="rating"]').val() && $('.rev-box textarea#comment').val() || !$('.rev-box input[name="rating"]').length && $('.rev-box textarea#comment').val() || cp_star_review_optional && $('.rev-box textarea#comment').val()){
						$('.rev-box .no-rating-error').hide();
					} else {
						e.preventDefault();
						$('.rev-box .no-rating-error').fadeOut(200).fadeIn(200);
					}
					
				}
				
			});
		}
		
		if ($('.cp-upload-wrap').length){
			
			$('.cp-upload-wrap input[type=file]').on('change',function(){
				
				var fileName = $(this).val();
				$(this).parent().find('span').html(fileName);
				$(this).parent().addClass('hasFile');
				
			});
			
		}
		
		// Check Login/Registration/Forgot Password forms before Submitting
		if ($('#loginform').length){
			$('#loginform input[type="submit"]').on('click',function(e) {
				if ($('#loginform input[name="log"]').val() && $('#loginform input[name="pwd"]').val()){
					$('#loginform .cp-custom-error').hide();
				} else {
					e.preventDefault();
					$('#loginform').parents('.cp-form-wrap').find('.cp-custom-error').fadeOut(200).fadeIn(200);
				}
			});
		}
		
		/*if ($('#profile-register').length){
			$('#profile-register input[type="submit"]').on('click',function(e) {
				if ($('#profile-register input[name="username"]').val() && $('#profile-register input[name="email"]').val() && $('#profile-register input[name="password"]').val()){
					$('#profile-register .cp-custom-error').hide();
				} else {
					e.preventDefault();
					$('#profile-register').find('.cp-custom-error').fadeOut(200).fadeIn(200);
				}
			});
		}*/
		
		if ($('#profile-forgot').length){
			$('#profile-forgot input[type="submit"]').on('click',function(e) {
				if ($('#profile-forgot input[name="user_login"]').val()){
					$('#profile-forgot .cp-custom-error').hide();
				} else {
					e.preventDefault();
					$('#profile-forgot').find('.cp-custom-error').fadeOut(200).fadeIn(200);
				}
			});
		}
		
		if ($('.directory-pane').length){
			$('.directory-pane').on('click',function(){
				thisLink = $(this).find('a').attr('href');
				window.location = thisLink;
			});
		}
		
		
		
		
		// Profile Tabs
		var profileTabs = $('.cp-tabs');
		
		if (!profileTabs.find('li.active').length){
			profileTabs.find('li:first-child').addClass("active");
		}
		
		if (profileTabs.length){
			$('.cp-tab-content').hide();
			var activeTab = profileTabs.find('.active > a').attr('href');
			activeTab = activeTab.split('#');
			activeTab = activeTab[1];
			$('#profile-'+activeTab).show();
			
			profileTabs.find('li > a').on('click', function(e) {
			
				e.preventDefault();
				$('.cp-tab-content').hide();
				profileTabs.find('li').removeClass('active');
				
				$(this).parent().addClass('active');
				var activeTab = $(this).attr('href');
				activeTab = activeTab.split('#');
				activeTab = activeTab[1];
				
				$('#profile-'+activeTab).show();
				return false;
				
			});
		}
		
		$(".tab_content_login").hide();
		$("ul.tabs_login li:first").addClass("active_login").show();
		$(".tab_content_login:first").show();
		$("ul.tabs_login li").click(function(e) {
		
			e.preventDefault();
			$("ul.tabs_login li").removeClass("active_login");
			$(this).addClass("active_login");
			$(".tab_content_login").hide();
			var activeTab = $(this).find("a").attr("href");
			if ($.browser.msie) {$(activeTab).show();}
			else {$(activeTab).show();}
			return false;
			
		});
		
		$('.btn-share').each(function() {
			var sharing_networks = $(this).attr('data-networks');
			var buttonText = $(this).find('span').html();
			if(sharing_networks !== undefined) {
				sharing_networks = sharing_networks.split(' ');
				var config = {
					ui: {
						button_text: buttonText
					},
					networks: {
						pinterest: {
							enabled: $.inArray('pinterest', sharing_networks) !== -1
						},
						facebook: {
							enabled: ($.inArray('facebook', sharing_networks) !== -1) && (cp_facebook_app_id !== ''),
							app_id: cp_facebook_app_id
						},
						twitter: {
							enabled: $.inArray('twitter', sharing_networks) !== -1
						},
						google_plus: {
							enabled: $.inArray('google_plus', sharing_networks) !== -1
						},
						email: {
							enabled: $.inArray('email', sharing_networks) !== -1
						}
					}
				};
				var share = new Share('.btn-share', config);
			}
		});

		$('.btn-share').on('click', function(e) {
			e.preventDefault();
		});

		var $parent = $('#cooked-plugin-page').parent();

		equalheight = function(container){
			var currentTallest = 0,
				currentRowStart = 0,
				rowDivs = new Array(),
				$el,
				topPosition = 0;
			 $(container).each(function() {

			   $el = $(this);
			   $($el).height('auto')
			   topPostion = $el.position().top;

			   if (currentRowStart != topPostion) {
				 for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
				   rowDivs[currentDiv].height(currentTallest);
				 }
				 rowDivs.length = 0; // empty the array
				 currentRowStart = topPostion;
				 currentTallest = $el.height();
				 rowDivs.push($el);
			   } else {
				 rowDivs.push($el);
				 currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
			  }
			   for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
				 rowDivs[currentDiv].height(currentTallest);
			   }
			 });
		}

		function boxHeights(){
			$("#cooked-plugin-page .recipe-row").each(function(){
				if ( $win.width() < media_query_1 && $('#cooked-plugin-page').hasClass('fullscreen')) {
 					$('#cooked-plugin-page.fullscreen .recipe-row').find('.cp-box').css('height', 'auto');
 				} else {
 					equalheight($(this).find('.cp-box'));
				}
			});
		}

		// Fullscreener
		$('#cooked-plugin-page .fullscreen-img').fullscreener();

		// Select
		$("#cooked-plugin-page select").chosen({disable_search_threshold: 10,width: '100%'});

		// Fields
		fix_form_fields();

		$('#cooked-plugin-page .field, #cooked-plugin-page input:text, #cooked-plugin-page textarea')
		.on('focusin', function() {
			if(this.title==this.value) {
				this.value = '';
			}
			$(this).parents('.field-wrap, .gfield').find('label').hide();
		}).on('focusout', function(){
			if(this.value==='') {
				this.value = this.title;
				$(this).parents('.field-wrap, .gfield').find('label').show();
			}
		});
		
		// Equal Height
		boxHeights();

		// Masonry
		var $container = $('#cooked-plugin-page .result-section.masonry-layout .loading-content');
		var $load_more = $('#cooked-plugin-page .load-more');
		var $load_more_button = $('#cooked-plugin-page .load-more-button');

		// initialize Isotope
		function masonry() {
			var column;
			if ( $win.width() > media_query_2 ){
				column = 3;
			} else if ( $win.width() < media_query_2 && $win.width() > media_query_1 ){
				column = 2;
			}else {
				column = 1;
			}

			$container.isotope({
			  resizable: false,
			  masonry: { columnWidth: $container.width() / column}
			});
		}

		setTimeout(function(){
			masonry();
		},100);
		
		setInterval(function(){
			masonry();
		},3000);
		
		loading = false;

		$win
		.on('load', function(){
			var scrollTop = $win.scrollTop();
			var loc = location.hash;
			if ( loc.length && loc === '#fullscreen'){
				$('#cooked-plugin-page .fs-btn').trigger('click');
			}

			if ( $load_more.length && scrollTop + $win.height()  >= $load_more.offset().top ){
				if(!loading) {
					load_more_posts( '.load-more' );
				}
			}

		})
		.on('scroll', function(){
			var scrollTop = $win.scrollTop();

			// Loading More
			if ( $('.load-more').length && scrollTop + $win.height()  > $('.load-more').offset().top ){
				if(!loading) {
					load_more_posts( '.load-more' );
				}
			}
		})
		.on('resize', function(){
			// Equal Height
			boxHeights();
			
			setTimeout(function() {
				masonry();
			}, 100);
		});

		$load_more.on('click', function(e){
			e.preventDefault();
			load_more_posts( '.load-more' );
		});

		$load_more_button.on('click', function(e) {
			e.preventDefault();
			load_more_posts( '.load-more-button' );
		});

		$(document)
		.on('click', '#cooked-plugin-page .fs-btn', function(e){

			if ( $(this).parents('.result-section').length ){
				window.location.hash
			}else{
				e.preventDefault();
			}

			if ( !$('#cooked-plugin-page').is('.fullscreen') ){
				$('#cooked-plugin-page').appendTo('body').addClass('fullscreen');

				boxHeights();
				masonry();
				$win.scrollTop(0);

				if ( $win.width() < media_query_2 ){
					$('#tab-info').prepend( $('.recipe-main-img, .cp-box-img-holder') );
				}

			} else {
				$('#cooked-plugin-page').removeClass('fullscreen');
				$('#cooked-plugin-page').appendTo($parent).removeClass('fullscreen');
				$('#cooked-plugin-page .timer-wrap').removeClass('hidden');
				boxHeights();
				masonry();
				
				if ( $win.width() < media_query_2 ){
					$('.upper_image_right_sidebar').prepend( $('.recipe-main-img, .cp-box-img-holder') );
					$('.upper_image_left_sidebar').prepend( $('.recipe-main-img, .cp-box-img-holder') );
				}
			}
		})

		.on('click', '#cooked-plugin-page .fullscreen-actions .x-fs-btn', function(e){
			e.preventDefault();

			$('#cooked-plugin-page').removeClass('fullscreen');
			$('#cooked-plugin-page').appendTo($parent).removeClass('fullscreen');
			$('#cooked-plugin-page .timer-wrap').removeClass('hidden');

			boxHeights();
			masonry();
		})

		.on('click', '#cooked-plugin-page .pp-btn', function(e){
			e.preventDefault();

			var timer_wrap = $('#cooked-plugin-page .timer-wrap'),
				timer = timer_wrap.find('.timer span'),
				time = timer_wrap.data('time'),
				duration = parseInt(time+'000'); // Turn time seconds in mileseconds and convert it to number

			if ( !timer_wrap.is('.playing') ){

				$('#cooked-plugin-page .time').countdown('resume');
				timer_wrap.addClass('playing');
				timer.animate( { width:'+=100%' }, { duration:duration, easing: "linear" } );

				timer.queue(function() {
					var that = $( this );
					that.dequeue();
				});

			}else{
				$('#cooked-plugin-page .time').countdown('pause');
				timer_wrap.removeClass('playing');
				timer.clearQueue();
				timer.stop();
			}
		})

		.on('click', '#cooked-plugin-page .timer-trigger', function(e){

			e.preventDefault();

			var time = $(this).attr('data-time-in-seconds');

			$('#cooked-plugin-page .timer-wrap').attr('data-time', time);

			if ( ! $('#cooked-plugin-page .timer-wrap').is('.show') ){

				var timer_wrap = $('#cooked-plugin-page .timer-wrap'),
					time = timer_wrap.attr('data-time'),
					timer = timer_wrap.find('.timer span'),
					time_output = timer_wrap.find('.time'),
					duration = parseInt(time + '000'); // Turn time seconds in milliseconds and convert it to number

				function formatSeconds(seconds){
				    var date = new Date(1970,0,1);
				    date.setSeconds(seconds);
				    return date.toTimeString().replace(/.*(\d{2}:\d{2}:\d{2}).*/, "$1");
				}

				function everyOne(periods) {
					var seconds = (periods[4] * 3600) + (periods[5] * 60) + periods[6];
					if ( seconds == time ){
						document.getElementById('readysound').play();
						var end_time = formatSeconds(time);
						timer_wrap.removeClass('playing');
						time_output.countdown('destroy');
						time_output.text( end_time )
					}
				}
				timer_wrap.addClass('show');
				time_output.countdown({
					since: 0,
					format: 'HMS',
					compact: 'true',
					description: '',
					onTick: everyOne,
					tickInterval: 1
				});


				timer_wrap.addClass('playing');
				timer.animate( { width:'+=100%' }, { duration:duration, easing: "linear" } );

				timer.queue(function() {
					var that = $( this );
					that.dequeue();
				});

				$('#cooked-plugin-page .timer-wrap .time').countdown('option', { since: 0, onTick: everyOne});
				$('.tab-links').addClass('with-timer');
				$('#cooked-plugin-page').addClass('with-timer');

			} else {

				$('#cooked-plugin-page .timer-wrap').removeClass('show playing');
				$('#cooked-plugin-page').removeClass('with-timer');
				$('#cooked-plugin-page .timer-wrap .timer span').clearQueue();
				$('#cooked-plugin-page .timer-wrap .timer span').stop();
				$('#cooked-plugin-page .timer-wrap .timer span').css('width', 0);
				$('#cooked-plugin-page .timer-wrap .time').countdown('destroy');
				$('.tab-links').removeClass('with-timer');

			}


		})

		.on('click', '#cooked-plugin-page .x-timer', function(e){
			e.preventDefault();
			$('#cooked-plugin-page .timer-wrap').removeClass('show playing');
			$('#cooked-plugin-page').removeClass('with-timer');
			$('#cooked-plugin-page .timer-wrap .timer span').clearQueue();
			$('#cooked-plugin-page .timer-wrap .timer span').stop();
			$('#cooked-plugin-page .timer-wrap .timer span').css('width', 0);
			$('#cooked-plugin-page .timer-wrap .time').countdown('destroy');
			$('.tab-links').removeClass('with-timer');
		});

		// Recipe like button
		$('.like-btn').each(function() {
			var $button      = $(this),
				$icon        = $button.find('> i'),
				likedRecipes = $.cookie('cpLikedRecipes'),
				recipeID     = $button.attr('data-recipe-id');
				cookied      = $button.attr('data-cookied');
				userLiked	 = $button.attr('data-userliked');

			if ( cookied == 1 && typeof likedRecipes !== 'undefined' && likedRecipes.split(',').indexOf(recipeID) > -1 || userLiked == 1 ) {
				$icon.removeClass('fa-heart-o').addClass('fa-heart');
			}

		});

		$('#cooked-plugin-page .like-btn').on('click', function() {
			var $button      = $(this),
				$icon        = $button.find('> i'),
				$count       = $button.find('.like-count'),
				count        = parseInt($count.text()),
				likedRecipes = $.cookie('cpLikedRecipes'),
				recipeID     = $button.attr('data-recipe-id'),
				cookied		 = $button.attr('data-cookied'),
				likeURL      = $button.attr('href'),
				likeAction;

			if ( $icon.hasClass('fa-heart-o') ) {
				$icon.removeClass('fa-heart-o').addClass('fa-heart');
				count++;

				if (cookied == 1){
					if ( typeof likedRecipes === 'undefined' ) {
						likedRecipes = recipeID;
					} else {
						likedRecipes = likedRecipes + ',' + recipeID;
					}
	
					$.cookie('cpLikedRecipes', likedRecipes, { expires: 365 } );
				}

				likeAction = 'like';
			} else {
				$icon.removeClass('fa-heart').addClass('fa-heart-o');
				count--;

				if (cookied == 1){
					if ( typeof likedRecipes === 'undefied' ) {
						return false;
					}
				}

				if (cookied == 1){
					var likedSplit = likedRecipes.split(','),
						recipeIdx  = likedSplit.indexOf(recipeID);
	
					if ( recipeIdx > -1 ) {
						likedSplit.splice( recipeIdx, 1 );
						likedRecipes = likedSplit.join(',');
	
						$.cookie('cpLikedRecipes', likedRecipes, { expires: 365 } );
	
						likeAction = 'dislike';
					}
				} else {
					likeAction = 'dislike';
				}

			}

			$.ajax({
				'url' : likeURL,
				'data': {
					'action'    : 'cp_like',
					'likeAction': likeAction
				},
				success: function(data) {
					$count.text(data);
				}
			});

			return false;
		});

		$('#cooked-plugin-page .tab-links a').on('click', function() {
			var tab = $(this).attr('href');

			if ( !$(this).is('.current') ){
				$(this).addClass('current').siblings('.current').removeClass('current');
				$('#cooked-plugin-page.fullscreen .tab').removeClass('current');
				$(tab).addClass('current');
				$win.scrollTop(0);
			}

			return false;
		});

		if($('.rating-holder').length) {
			$('.rating-holder .rate')
				.on('mouseenter', function() {
					var $me = $(this);
					var $parent = $me.parents('.rating-holder');
					var my_index = $me.index();
					var rated = $parent.attr('data-rated');
					$parent.removeClass(function(index, css) {
						return (css.match (/(^|\s)rate-\S+/g) || []).join(' ');
					});
					$parent.addClass('rate-' + (my_index + 1));
				})
				.on('mouseleave', function() {
					var $me = $(this);
					var $parent = $me.parents('.rating-holder');
					var my_index = $me.index();
					var rated = $parent.attr('data-rated');
					$parent.removeClass(function(index, css) {
						return (css.match (/(^|\s)rate-\S+/g) || []).join(' ');
					});
					if(rated !== undefined) {
						$parent.addClass('rate-' + rated);
					}
				})
				.on('click', function() {
					var $me = $(this);
					var $parent = $me.parents('.rating-holder');
					var my_index = $me.index();
					$('.rating-real-value').val(my_index + 1);
					$parent.attr('data-rated', my_index + 1);
					$parent.addClass('rate-' + (my_index + 1));
				});
		}
		
		// Front-end Submission Form
		$("#cooked-submit-recipe-form select").chosen({disable_search_threshold: 10});
		
		if($('#cooked-submit-recipe-form .slider').length) {
			// Sliders
			$('#cooked-submit-recipe-form .slider').each(function(){
				var slider = $(this),
					maxval = slider.attr('data-maxval'),
					amount = slider.find('.amount .slider-timer'),
					$value_input = slider.find('.amount .real-value');
					real_time = $value_input.val();

				if(real_time.length) {
					var hours = Math.floor(real_time / 60);
					var minutes = real_time - hours * 60;
					var slider_value = hours * 60 + minutes;
				} else {
					var slider_value = 0;
				}

				slider_value *= 60;
				if (maxval > 17999){ timer_step = 300; } else { timer_step = 60; }

				amount.val(sformat(slider_value));

				slider.slider({
					value: slider_value,
					min: 0,
					max: maxval,
					step: timer_step,
					slide: function( event, ui ) {

						var raw = ui.value;
						amount.val( sformat(raw) );

						if ( amount.val() == '00:00'){
							amount.parent('.amount').removeClass('active');
						} else {
							amount.parent('.amount').addClass('active');
						}

						var values = amount.val().split(':');
						var hours = parseInt(values[0]) * 60;
						var minutes = parseInt(values[1]);
						var total_time = (hours + minutes);
						$value_input.val(total_time);
					}
				});
			});
		}

		$('.section-stats').each(function() {
			var $me = $(this);
			var $textarea = $me.find('.section-stats-field');
			section_stats($me);
			$textarea
				.on('keydown', function(e) {
					section_stats($me);
				})
				.on('blur', function() {
					section_stats($me);
				});
		});
		
	});

	function load_more_posts(selector){
		var url = $(selector).attr('href');
		var data;
		loading = true;

		$.ajax({
			url: url,
			data: data,
			success: function( data ) {

				var $items = $( '.loading-content .item', data );
					$new_anchor = $( selector, data );
					$items.addClass('hidden');

				if ( $('#cooked-plugin-page .result-section.masonry-layout .loading-content').length ){
					$( '.loading-content').isotope( 'insert', $items );

				} else {
					$( '.loading-content').append($items);
					setTimeout(function() {
						$items.removeClass('hidden');
					}, 200);
				}

				if($new_anchor.length) {
					$(selector).attr('href', $new_anchor.attr('href'));
				} else {
					$(selector).remove();
				}

				loading = false;
				
				setTimeout(function() {
					masonry();
				}, 500);
				
			}
		});
	}
	
	function sformat(s) {
	  var fm = [
			Math.floor(s / 60 / 60) % 24, // HOURS
			Math.floor(s / 60) % 60, // MINUTES
	  ];
	  return $.map(fm, function(v, i) { return ((v < 10) ? '0' : '') + v; }).join(':');
	}

	function fix_form_fields() {
		$('#cooked-plugin-page .field,#cooked-plugin-page input:text,#cooked-plugin-page textarea').each(function() {
			if(this.value!=='') {
				$(this).parents('.field-wrap, .gfield').find('label').hide();
			}
		});
	}
	
	function section_stats($section_wrapper) {
		var $textarea = $section_wrapper.find('.section-stats-field');
		var $first_holder = $section_wrapper.find('.section-stats-first');
		var $second_holder = $section_wrapper.find('.section-stats-second');
		var first_counter = 0;
		var secondary_counter = 0;

		var lines = $textarea.val().split('\n');
		$.each(lines, function(key, value) {
			if(value.indexOf('--') === 0) {
				first_counter++;
			} else if(value !== '') {
				secondary_counter++;
			}
		});
		$first_holder.text(first_counter === 1 ? '1 ' + $first_holder.attr('data-single') : first_counter + ' ' + $first_holder.attr('data-plural'));
		$second_holder.text(secondary_counter === 1 ? '1 ' + $second_holder.attr('data-single') : secondary_counter + ' ' + $second_holder.attr('data-plural'));
	}

})(jQuery, window, document);