jQuery(document).ready( function($) {
	function cp_open_pointer(i) {
		pointer = cpPointer.pointers[i];
		$(pointer.target).pointer( pointer.options ).pointer('open');
	}

	$('.cp-helper').on('click', function(e) {
		e.preventDefault();

		var $me = $(this);
		var pointer_id = $me.attr('id');
		$.each(cpPointer.pointers, function(key, value) {
			if(value.target === '#' + pointer_id) {
				cp_open_pointer(key);
			}
		});
	});
});