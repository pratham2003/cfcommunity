;(function($, window, document, undefined) {
	var $win = $(window);
	var $doc = $(document);

	$doc.ready(function() {
	
		if ($('.cp-color-field').length){
			$('.cp-color-field').wpColorPicker();
		}
		
		// Upload Image Button
		var _custom_media = true,
		_orig_send_attachment = wp.media.editor.send.attachment;
	 
		$('#cooked_email_logo_button').click(function(e) {
			var send_attachment_bkp = wp.media.editor.send.attachment;
			var button = $(this);
			var id = button.attr('id').replace('_button', '');
			_custom_media = true;
			wp.media.editor.send.attachment = function(props, attachment){
				if ( _custom_media ) {
					$("#"+id).val(attachment.url);
					$("#"+id+"-img").attr('src',attachment.url);
				} else {
					return _orig_send_attachment.apply( this, [props, attachment] );
				};
			}
	 
			wp.media.editor.open(button);
			return false;
		});
		// END Upload Image Button

		var checkedClass = 'custom-input-checked';
		var disabledClass = 'custom-input-disabled';
		var inputSelector = '#cooked-admin-panel-container .custom-checkbox input, #cooked-admin-panel-container .custom-radio input';
		var ajaxRequests = [];

		// Checkbox and Radio
		$(inputSelector)

			// Add classes to all checked checkboxes
			.each(function() {
				var input = this;

				$(input)
					.parent() // go up to the input holder element
					.toggleClass(checkedClass, input.checked);
			})

			// Handle the change event
			.on('change', function() {
				var input = this;

				// detect if the input is radio
				if(input.type === 'radio') {
					var name = input.name;

					// find all the radios with that name, in the same document
					$(input.ownerDocument)
						.find('[name=' + name + ']')
						.each(function() {

							var radioInput = this;

							$(radioInput)
								.parent() // go up to the input holder element
								.toggleClass(checkedClass, radioInput.checked);

						});
				} else {

					$(input)
						.parent() // go up to the input holder element
						.toggleClass(checkedClass, input.checked);
				}
			})
			.on('disable', function() {
				var input = this;

				input.disabled = true;

				$(input)
					.parent() // go up to the input holder element
					.addClass(disabledClass);
			})
			.on('enable', function() {
				var input = this;

				input.disabled = false;

				$(input)
					.parent() // go up to the input holder element
					.removeClass(disabledClass);
			});

		// Select
		init_custom_select();

		if($('#cooked-admin-panel-container .slider.time').length) {
			
			// Difficulty Level Slider
			$('#cooked-admin-panel-container .slider.difficulty').each(function(){
				var slider = $(this),
					maxval = slider.attr('data-maxval'),
					amount = slider.find('.amount .slider-difficulty'),
					$value_input = slider.find('.amount .real-value');
					real_time = $value_input.val();

				if(real_time.length) {
					var slider_value = real_time;
				} else {
					var slider_value = 0;
				}

				amount.val(dformat(slider_value));

				slider.slider({
					value: slider_value,
					min: 0,
					max: maxval,
					step: 1,
					slide: function( event, ui ) {

						var raw = ui.value;
						amount.val( dformat(raw) );
						$value_input.val(raw);
					}
				});
			});
			
			// Time Sliders
			$('#cooked-admin-panel-container .slider.time').each(function(){
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

				amount.val(sformat(slider_value));
				
				if (maxval > 17999){ timer_step = 300; } else { timer_step = 60; }

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

		if($('.cp-import-form').length) {
			$('#upload-field').on('change', function() {
				$('#upload-field').parents('form').trigger('submit');
			});
		}

		if($('.slider-labels').length) {
			var rsp_labels_default_text = [];
			$('.slider-labels').each(function() {
				rsp_labels_default_text.push($(this).text());
			});

			var sliders_min = 300,
				sliders_max = 1200;

			$('#cooked-admin-panel-container .rsp-slider').slider({
				slide: function(event, ui){
					var slide = $( ".rsp-slider a.ui-state-focus" ),
						slide_index = slide.data('slide'),
						value = ui.value;

					$('.rsp-slider .slide-data[data-slide="' + slide_index + '"]').css('left', slide.position().left).text(value +'px');
					adjust_slider_labels();
				},
				change: function(event, ui){
					var slide = $( ".rsp-slider a.ui-state-focus" ),
						slide_index = slide.data('slide');
					$('.rsp-slider .slide-data[data-slide="' + slide_index + '"]').css('left', slide.position().left).text(ui.value +'px');
					adjust_slider_labels();
				},
				create: function(event, ui){
					$('.rsp-slider a').each(function(i) {
						var left = $(this).position().left;
						$(this).attr('data-slide', i);
						$('.rsp-slider').append('<div class="slide-data" data-slide="' + i + '"></div>');
					});

					$('.rsp-slider .slide-data').each(function(i){
						$(this).text( rsp_slider_values[i] +'px' ).css('left', $('.rsp-slider a[data-slide="' + i + '"]').position().left );
						adjust_slider_labels();
					});
				},
				min: 300,
				max: 1200,
				values: rsp_slider_values
			});
		}
		
		if($('.prep-slider').length) {
		
			var prep_slider_min = 1,
				prep_slider_max = 12;

			$('#cooked-admin-panel-container .prp-slider').slider({
				slide: function(event, ui){
					var slide = $( ".prp-slider span.ui-state-focus" ),
						slide_index = slide.data('slide'),
						value = ui.value;
					$('.prp-slider .slide-data[data-slide="' + slide_index + '"]').text(ui.value +'hr(s)');
					$('input[name=cp_prep_time_max_hrs').val(ui.value);
				},
				change: function(event, ui){
					var slide = $( ".prp-slider span.ui-state-focus" ),
						slide_index = slide.data('slide');
					$('.prp-slider .slide-data[data-slide="' + slide_index + '"]').text(ui.value +'hr(s)');
					$('input[name=cp_prep_time_max_hrs').val(ui.value);
				},
				create: function(event, ui){
					$('.prp-slider span').each(function(i) {
						$(this).attr('data-slide', i);
						$('.prp-slider').append('<div class="slide-data" data-slide="' + i + '"></div>');
					});

					$('.prp-slider .slide-data').each(function(i){
						$(this).text( prep_slider_value[i] +'hr(s)' );
						$('input[name=cp_prep_time_max_hrs').val(prep_slider_value[i]);
					});
					
									},
				min: prep_slider_min,
				max: prep_slider_max,
				values: prep_slider_value
			});
		}
		
		if($('.cook-slider').length) {
		
			var cook_slider_min = 1,
				cook_slider_max = 12;

			$('#cooked-admin-panel-container .ck-slider').slider({
				slide: function(event, ui){
					var slide = $( ".ck-slider span.ui-state-focus" ),
						slide_index = slide.data('slide'),
						value = ui.value;
					$('.ck-slider .slide-data[data-slide="' + slide_index + '"]').text(ui.value +'hr(s)');
					$('input[name=cp_cook_time_max_hrs').val(ui.value);
				},
				change: function(event, ui){
					var slide = $( ".ck-slider span.ui-state-focus" ),
						slide_index = slide.data('slide');
					$('.ck-slider .slide-data[data-slide="' + slide_index + '"]').text(ui.value +'hr(s)');
					$('input[name=cp_cook_time_max_hrs').val(ui.value);
				},
				create: function(event, ui){
					$('.ck-slider span').each(function(i) {
						$(this).attr('data-slide', i);
						$('.ck-slider').append('<div class="slide-data" data-slide="' + i + '"></div>');
					});

					$('.ck-slider .slide-data').each(function(i){
						$(this).text( cook_slider_value[i] +'hr(s)' );
						$('input[name=cp_cook_time_max_hrs').val(cook_slider_value[i]);
					});
					
				},
				min: cook_slider_min,
				max: cook_slider_max,
				values: cook_slider_value
			});
		}

		fields_controller('cp_controller');
		fields_controller('cp_sub_controller');
		fields_controller('cp_fes_controller');
		
		if ($('.view-instructions').length){
			$('.view-instructions').on('click', function(e) {
				e.preventDefault();
				$('ol.fb-instructions').slideToggle('fast');
			});
		}
		
		if ($('.view-import-export').length){
			$('.view-import-export').on('click', function(e) {
				e.preventDefault();
				$('.import-export-row').slideToggle('fast');
			});
		}
		
		if ($('.view-uninstall').length){
			$('.view-uninstall').on('click', function(e) {
				e.preventDefault();
				$('.uninstall-row').slideToggle('fast');
			});
		}

		if($('.cp_controller').length) {
			$('.cp_controller[checked="checked"]').each(function(){
				fields_update_controller('cp_controller', $(this));
			});
		}

		if($('.cp_sub_controller').length) {
			$('.cp_sub_controller[checked="checked"]').each(function(){
				fields_update_controller('cp_sub_controller', $(this));
			});
		}
		
		if($('.cp_fes_controller').length) {
			$('.cp_fes_controller').each(function(){
				fields_update_controller('cp_fes_controller', $(this));
			});
		}

		function adjust_slider_labels() {
			var total_sliders = $('.rsp-slider .slide-data').length;
			var desktop_label = $('.responsive-sliders .desktop-label');
			$('.rsp-slider .slide-data').each(function(i){
				if(i === 1 || i === 2) {
					var text = rsp_labels_default_text[i] + ' ' + (rsp_slider_values[i - 1] + 1) + 'px to ' + (rsp_slider_values[i]) + 'px';
				} else {
					var text = rsp_labels_default_text[i] + ' ' + rsp_slider_values[i] + 'px';
				}
				$('.responsive-sliders .slider-labels:eq(' + i + ')').text(text);
				$('.responsive-sliders .slider-inputs:eq(' + i + ')').val(rsp_slider_values[i]);
				if(total_sliders === i + 1) {
					desktop_label.text('Desktop: above ' + (rsp_slider_values[i] + 1) + 'px');
				}
			});
		}

		function fields_controller(controller) {
			$('.' + controller).on('change', function() {
				var $self = $(this);
				fields_update_controller(controller, $self);
			});
		}

		function fields_update_controller(controller, $self) {
		
			if($self.length) {
				var selected_value = $self.val();
				var is_checkbox = $self.attr('type') === 'checkbox';
				var checked = $self.attr('checked') === 'checked';
				$('[data-controller="' + controller + '"]').each(function() {
					var controlled_by = $(this).attr('data-controlled_by');
					var can_be_controlled = controlled_by.indexOf(selected_value) !== -1;
					if (is_checkbox && !checked && controller == 'cp_sub_controller' && can_be_controlled || is_checkbox && checked && controller == 'cp_fes_controller' && can_be_controlled || !is_checkbox && checked && can_be_controlled) {
						$(this).fadeIn();
					} else {
						$(this).fadeOut();
					}
				});
			} else {
				$('[data-controller="' + controller + '"]').slideDown();
			}
		}

		// Fields
		fix_form_fields();

		$('#cooked-admin-panel-container .field, #cooked-admin-panel-container input:text, #cooked-admin-panel-container textarea')
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

		$('.sortable-table').sortable({
			items: 'tr.sortable-row',
			handle: '.button-re-order'
		});
		
		$('.section-repeater-actions button').on('click', function(e) {
			e.preventDefault();

			var $this = $(this);
			var $fields_parent_container = $this.parents('.fields-container');
			var $fields_template_container = $fields_parent_container.find('.fields-templates');
			var $fields_live_container = $fields_parent_container.find('.fields-live');
			var to_clone = $this.attr('data-duplicate');
			
			var $new_row = $fields_template_container.find('.' + to_clone).clone();
			
			$fields_live_container.append($new_row);
			init_duplicated_row_functionality($new_row, action);

		});
		
		$('.fields-live').on('click', '.action-button', function(e) {
			e.preventDefault();

			$("#cooked-admin-panel-container select:not(.inactive) option").each(function() {
				if (!$(this).is(':selected')) {
					$(this).removeAttr('selected');
				} else {
					$(this).attr('selected', 'selected');
				}
			});

			var $this = $(this);
			var action = $this.attr('data-action');
			var $row = $this.parents('.field-row');
			var $fields_live_container = $this.parents('.fields-live');

			if(action === 'remove') {
				$row.remove();
			} else if(action === 'duplicate') {
				var $new_row = $row.clone();
				$new_row.insertAfter($row);
				init_duplicated_row_functionality($new_row, action);
			}
		});
		
		// Delete Recipe from Pending List
		$('.cooked-pending-recipe-list').on('click', '.pending-recipe .delete', function(e) {
		
			e.preventDefault();
			
			var $button 			= $(this),
				$thisParent			= $button.parents('.pending-recipe'),
				recipe_id			= $thisParent.attr('data-recipe-id'),
				cooked_ajaxURL		= $('#data-ajax-url').html();
				pending_menu_item	= $('li#menu-posts-cp_recipe').find('li.current');
			
			confirm_recipe_delete = confirm(i18n_confirm_recipe_delete);
			if (confirm_recipe_delete == true){
	  		
	  			var currentPendingCount = parseInt(pending_menu_item.find('span.update-count').html());
				currentPendingCount = parseInt(currentPendingCount - 1);
				if (currentPendingCount < 1){
					pending_menu_item.find('span.update-plugins').remove();
					$('.no-pending-message').slideDown('fast');
				} else {
					pending_menu_item.find('span.update-count').html(currentPendingCount);
				}
	  		
	  			$thisParent.slideUp('fast',function(){
					$(this).remove();
				});
				
	  			savingState(true);
	  							
				ajaxRequests.push = $.ajaxQueue({
					'url' : cooked_ajaxURL,
					'data': {
						'action'     	: 'delete_recipe',
						'recipe_id'     : recipe_id
					},
					success: function(data) {
						savingState(false);
					}
				});
			
			}
			
			return false;
			
		});
		
		// Approve Appointment from Pending List
		$('.cooked-pending-recipe-list').on('click', '.pending-recipe .approve', function(e) {
		
			e.preventDefault();
			
			var $button 			= $(this),
				$thisParent			= $button.parents('.pending-recipe'),
				recipe_id			= $thisParent.attr('data-recipe-id'),
				cooked_ajaxURL		= $('#data-ajax-url').html();
				pending_menu_item	= $('li#menu-posts-cp_recipe').find('li.current');
			
			confirm_recipe_approve = confirm(i18n_confirm_recipe_approve);
			if (confirm_recipe_approve == true){
				
				var currentPendingCount = parseInt(pending_menu_item.find('span.update-count').html());
				currentPendingCount = parseInt(currentPendingCount - 1);
				if (currentPendingCount < 1){
					pending_menu_item.find('span.update-plugins').remove();
					$('.no-pending-message').slideDown('fast');
				} else {
					pending_menu_item.find('span.update-count').html(currentPendingCount);
				}
				
				$thisParent.slideUp('fast',function(){
					$(this).remove();
				});
				
	  			savingState(true);
	  	
		  		ajaxRequests.push = $.ajaxQueue({
					'url' : cooked_ajaxURL,
					'data': {
						'action'     	: 'approve_recipe',
						'recipe_id'     	: recipe_id
					},
					success: function(data) {
						savingState(false);
					}
				});
			
			}
			
			return false;
			
		});

	});

	function init_duplicated_row_functionality($row, action) {
		var row_index = parseInt($row.parents('.fields-live').attr('data-field-index'));
		var row_data_name = $row.parents('.fields-live').attr('data-name');

		$row.find('input[type="text"], select, input[type="hidden"], textarea').each(function() {
			var $this = $(this);
			var partial_data_name = $this.attr('data-partial-name');

			var new_name_attr = row_data_name + '[' + row_index + ']' + partial_data_name;

			$this.attr('name', new_name_attr);

		});

		$row.find('.inactive').removeClass('inactive');

		$row.find('select').removeClass('chzn-done').removeAttr('id').css('display', 'block').next().remove();

		init_custom_select();

		$row.parents('.fields-live').attr('data-field-index', row_index + 1);
		cp_autocomplete();
	}

	$win.on('load', function() {

		$('.button-unn').on('click', function(e) {
			e.preventDefault();
			$(this).parents('form').trigger('submit');
		});
		
		if ($('.cooked-admin-tabs').length){
		
			$('.cooked-admin-tabs').each(function(){
				
				var adminTabs = $(this);
				var adminTabsSection = $(this).parent();
				adminTabsSection.find('.tab-content').hide();
				var tabHash 	= window.location.hash;
				
				if (tabHash){
					var activeTab = tabHash;
					activeTab = activeTab.split('#');
					activeTab = activeTab[1];
					adminTabs.find('li').removeClass('active');
					adminTabs.find('a[href="'+tabHash+'"]').parent().addClass('active');
					adminTabsSection.find('#cooked-'+activeTab).show();
				} else {
					var activeTab = adminTabs.find('.active > a').attr('href');
					activeTab = activeTab.split('#');
					activeTab = activeTab[1];
					adminTabsSection.find('#cooked-'+activeTab).show();
				}
				
				adminTabs.find('li > a').on('click', function(e) {
				
					//e.preventDefault();
					adminTabsSection.find('.tab-content').hide();
					adminTabs.find('li').removeClass('active');
					
					$(this).parent().addClass('active');
					var activeTab = $(this).attr('href');
					activeTab = activeTab.split('#');
					activeTab = activeTab[1];
					
					if (activeTab == 'import_export_uninstall'){
						adminTabsSection.find('.submit-section').hide();
					} else {
						adminTabsSection.find('.submit-section').show();
					}
					
					adminTabsSection.find('#cooked-'+activeTab).show();
					
				});
			
			});
		
		}
		
		if ($('.cooked-admin-tabs-alt').length){
		
			// Tabs
			$('.cooked-admin-tabs-alt').each(function(){
				var adminTabs = $(this);
				var adminTabsSection = $(this).parent();
				adminTabsSection.find('.tab-content').hide();
				var activeTab = adminTabs.find('.active > a').attr('href');
				activeTab = activeTab.split('#');
				activeTab = activeTab[1];
				adminTabsSection.find('#cooked-'+activeTab).show();
				
				adminTabs.find('li > a').on('click', function(e) {
				
					e.preventDefault();
					adminTabsSection.find('.tab-content').hide();
					adminTabs.find('li').removeClass('active');
					
					$(this).parent().addClass('active');
					var activeTab = $(this).attr('href');
					activeTab = activeTab.split('#');
					activeTab = activeTab[1];
					
					if (activeTab == 'import_export_uninstall'){
						$('.submit-section').hide();
					} else {
						$('.submit-section').show();
					}
					
					adminTabsSection.find('#cooked-'+activeTab).show();
					
				});
			});
		
		}
			
		// Image Uploader
		$('.fields-live').on('click', '.cp-media-action', function(e) {
			e.preventDefault();
			var $this = $(this);
			var $row = $this.parents('.cp-media-uploader-wrapper');

			wp.media.model.settings.post.id = $('#post_ID').val();

			var input_field = $row.find('input.real-value'),
				button_label = $(this).attr('data-window-button-label'),
				window_label = $(this).attr('data-window-label'),
				value_type = 'id',
				file_type = 'image',
				image_media = wp.media({
					title: window_label,
					library: { type: file_type },
					button: { text: button_label },
					multiple: false
				});

			image_media.on('select', function() {
				var media_attachment = image_media.state().get('selection').first().toJSON();
				var media_value = media_attachment[value_type];
				var media_url = media_attachment['url'];

				input_field.val(media_value);

				$row.find('.image-preview').css('background', 'url(' + media_url + ') no-repeat center center');
				$row.find('.real-value').val(media_value);
				$row.find('.img-holder').removeClass('no-image');
				$this.hide();
			});

			image_media.open();
		});

		$('.fields-live').on('click', '.x-btn', function(e) {
			e.preventDefault();
			var $row = $(this).parents('.cp-media-uploader-wrapper');

			$row.find('.img-holder').addClass('no-image');
			$row.find('.image-preview').css('background', $row.find('.image-preview').attr('data-empty_src'));
			$row.find('.cp-media-file-url').val('');
			$row.find('.real-value').val('');
			$row.find('.cp-media-action').show();
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
			$('.clear-rating')
				.on('click',function(){
					var $me = $(this);
					var $parent = $me.parent().find('.rating-holder');
					var currentValue = $('.rating-real-value').val();
					$('.rating-real-value').val(0);
					$parent.attr('data-rated', 0);
					$parent.removeClass('rate-'+currentValue);
					return false;
				});
		}
		
	});
	
	// Saving state updater
	function savingState(show){
		var $savingStateDIV = $('.topSavingState.savingState');
		if (show){
			$savingStateDIV.fadeIn(200);
		} else {
			$savingStateDIV.hide();
		}
	}

	function init_custom_select() {
		//$("#cooked-admin-panel-container select:not(.inactive)").chosen({disable_search_threshold: 10});
	}

	function fix_form_fields() {
		$('#cooked-admin-panel-container .field,#cooked-admin-panel-container input:text,#cooked-admin-panel-container textarea').each(function() {
			if(this.value!=='') {
				$(this).parents('.field-wrap, .gfield').find('label').hide();
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
	
	function dformat(s){
		if (s == 1){
			return 'Beginner';
		} else if (s == 2){
			return 'Intermediate';
		} else if (s == 3){
			return 'Advanced';
		}
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

	// Textarea and select clone() bug workaround | Spencer Tipping
	// Licensed under the terms of the MIT source code license

	// Motivation.
	// jQuery's clone() method works in most cases, but it fails to copy the value of textareas and select elements. This patch replaces jQuery's clone() method with a wrapper that fills in the
	// values after the fact.

	// An interesting error case submitted by Piotr Przybyl: If two <select> options had the same value, the clone() method would select the wrong one in the cloned box. The fix, suggested by Piotr
	// and implemented here, is to use the selectedIndex property on the <select> box itself rather than relying on jQuery's value-based val().

	(function (original) {
		jQuery.fn.clone = function () {
			var result           = original.apply(this, arguments),
				my_textareas     = this.find('textarea').add(this.filter('textarea')),
				result_textareas = result.find('textarea').add(result.filter('textarea')),
				my_selects       = this.find('select').add(this.filter('select')),
				result_selects   = result.find('select').add(result.filter('select'));

			for (var i = 0, l = my_textareas.length; i < l; ++i) $(result_textareas[i]).val($(my_textareas[i]).val());
			for (var i = 0, l = my_selects.length; i < l; ++i) {
				for (var j = 0, m = my_selects[i].options.length; j < m; ++j) {
					if (my_selects[i].options[j].selected === true) {
					result_selects[i].options[j].selected = true;
					}
				}
			}
			return result;
		};
	}) (jQuery.fn.clone);

	// Generated by SDoc

})(jQuery, window, document);

// Ajax Queue Function
(function($) {
 
	// jQuery on an empty object, we are going to use this as our Queue
	var ajaxQueue = $({});
	 
	$.ajaxQueue = function( ajaxOpts ) {
	    var jqXHR,
	        dfd = $.Deferred(),
	        promise = dfd.promise();
	 
	    // queue our ajax request
	    ajaxQueue.queue( doRequest );
	 
	    // add the abort method
	    promise.abort = function( statusText ) {
	 
	        // proxy abort to the jqXHR if it is active
	        if ( jqXHR ) {
	            return jqXHR.abort( statusText );
	        }
	 
	        // if there wasn't already a jqXHR we need to remove from queue
	        var queue = ajaxQueue.queue(),
	            index = $.inArray( doRequest, queue );
	 
	        if ( index > -1 ) {
	            queue.splice( index, 1 );
	        }
	 
	        // and then reject the deferred
	        dfd.rejectWith( ajaxOpts.context || ajaxOpts, [ promise, statusText, "" ] );
	        return promise;
	    };
	 
	    // run the actual query
	    function doRequest( next ) {
	        jqXHR = $.ajax( ajaxOpts )
	            .done( dfd.resolve )
	            .fail( dfd.reject )
	            .then( next, next );
	    }
	 
	    return promise;
	};
	 
})(jQuery);