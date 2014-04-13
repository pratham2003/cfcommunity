/**
 * @package Feature/Relationship_Control
 * @version 2014.02.14
 */

(function ($) {

	$.fn.mlp_search = function (options) {

		var settings = $.extend(
				{
					// Default values.
					remote_blog_id:   this.data('remote_blog_id'),
					remote_post_id:   this.data('remote_post_id'),
					source_blog_id:   this.data('source_blog_id'),
					source_post_id:   this.data('source_post_id'),
					// the selectors to listen on
					search_field:     'input.mlp_search_field',
					result_container: 'ul.mlp_search_results',
					action:           'mlp_search',
					nonce:            '',
					spinner:          '<span class="spinner no-float" style="display:block;"></span>'
				},
				options
			),


			original_content = $(settings.result_container).html(),
			search_field = $(settings.search_field),
			stored = [],

			insert = function (content) {
				$(settings.result_container).html(content);
			},

			fetch = function (keywords) {

				if (stored[ keywords ]) {
					insert(stored[ keywords ]);
					return;
				}

				insert(settings.spinner);

				//*
				var ajax = $.post(
					ajaxurl,
					{
						action:         settings.action,
						source_post_id: settings.source_post_id,
						source_blog_id: settings.source_blog_id,
						remote_post_id: settings.remote_post_id,
						remote_blog_id: settings.remote_blog_id,
						s:              keywords
					}
				);
				/**/

				ajax.done(function (data) {
					stored[ keywords ] = data;
					insert(data);
				});
			};


		// prevent submission by enter key
		search_field.keypress(function (event) {
			if (event.which == 13)
				return false;
		});

		search_field.on('keyup', function (event) {
			event.preventDefault();
			event.stopPropagation();

			var str = $.trim(this.value);

			if (!str || 0 == str.length)
				insert(original_content);

			if (2 < str.length) {
				fetch(str);
			}
		});
	};
})(jQuery);


//
(function ($) {
	$('.mlp_rsc_save_reload').on('click.mlp', function (event) {
		event.stopPropagation();
		event.preventDefault();

		var source_post_id = $(this).data('source_post_id'),
			source_blog_id = $(this).data('source_blog_id'),
			remote_post_id = $(this).data('remote_post_id'),
			remote_blog_id = $(this).data('remote_blog_id'),
			current_value = $('input[name="mlp_rsc_action[' + remote_blog_id + ']"]:checked').val(),
			new_post_id = 0,
			new_post_title = '',

			disconnect = function () {
				change_relationship('disconnect');
			},

			new_relation = function () {
				new_post_title = $('input[name="post_title"]').val();
				change_relationship('new_relation');
			},

			connect_existing = function () {

				new_post_id = $('input[name="mlp_add_post[' + remote_blog_id + ']"]:checked').val();

				if (!new_post_id || 0 == new_post_id)
					alert('Please select a post.');
				else
					change_relationship('connect_existing');
			},

			ajax_success = function (data, textStatus, jqXHR) {
				console.log('ajax_success', {
					as_data:       data,
					as_textStatus: textStatus,
					as_jqXHR:      jqXHR
				});
				// reload to populate the editor with the new data
				window.location.reload(true);
				//$( '#post' ) . submit();
			},

			change_relationship = function (action) {

				var data =
				{
					action:         'mlp_rsc_' + action,
					source_post_id: source_post_id,
					source_blog_id: source_blog_id,
					remote_post_id: remote_post_id,
					remote_blog_id: remote_blog_id,
					new_post_id:    new_post_id,
					new_post_title: new_post_title
				};

				$.ajax({
					type:    "POST",
					url:     ajaxurl,
					data:    data,
					success: ajax_success,
					async:   false
				});
			};

		if (!current_value || 'stay' == current_value)
			return;

		if ('disconnect' == current_value)
			disconnect();

		if ('new' == current_value)
			new_relation();

		if ('search' == current_value)
			connect_existing();
	});
})(jQuery);