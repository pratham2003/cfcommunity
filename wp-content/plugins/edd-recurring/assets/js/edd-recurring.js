jQuery(document).ready(function ($) {

	var EDD_Recurring = {
		init : function() {
			this.recurring_select();
		},

		recurring_select : function() {
			$( 'body' ).on('change', 'select[name$="[recurring]"], select[name$=recurring]', function() {

				var $this = $(this);
				val       = $( 'option:selected', this ).val(),
				fields    = $this.parent().parent().find('select,input[type="number"]')

				if ( val == 'no' ) {
					fields.attr( 'disabled', true );
				} else {
					fields.attr( 'disabled', false );
				}

				$this.attr( 'disabled', false );

			});

			$( 'input[name$="[times]"], input[name$=times]' ).change(function() {
				$( this ).next( '.times' ).text( $( this ).val() == 1 ? EDD_Recurring_Vars.singular : EDD_Recurring_Vars.plural );
			});
		}
	}

	EDD_Recurring.init();

});