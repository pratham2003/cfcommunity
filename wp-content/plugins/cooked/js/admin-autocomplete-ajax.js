jQuery(document).ready(function($) {

	cp_autocomplete();

});

function cp_autocomplete() {
	var $ = jQuery;
	var search_string = '';

	$('.autocomplete-field').autocomplete({
		delay: 500,
		source: function(request, response) {
			search_string = request.term;
			$.ajax({
				type: 'POST',
				url: ajax_params.ajax_url,
				data: {
					action: 'cp_handleautocomplete',
					query_string: search_string
				},
				success: function(data) {
					response($.parseJSON(data));
				}
			});
		}
	});
}