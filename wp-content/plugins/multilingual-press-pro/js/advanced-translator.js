/**
 * Module Name:	Multilingual Press Advanced Translator JavaScript
 * Description:	This jQuery Code is used to toggle the editors
 * Author:		Inpsyde GmbH
 * Version:		0.1
 * Author URI:	http://inpsyde.com
 *
 * Changelog
 *
 * 0.1
 * - Initial Commit
 */

jQuery.noConflict();
( function( $ ) {
	/**
	 * Main Class for the advanced translator
	 *
	 * @author	th
	 * @since	0.1
	 */
	advanced_translator = {

		/**
		 * Initialation Function
		 *
		 * @author	th
		 * @since	0.1
		 * @return	void
		 */
		init : function() {
			advanced_translator.meta_box_init();
			advanced_translator.meta_box_toggle_switch();
		},

		/**
		 * Meta Box Init function which closes all boxes
		 * and reopen the active ones
		 *
		 * @author	th
		 * @since	0.1
		 * @return	void
		 */
		meta_box_init : function() {
			// Close all
			$( '.to_translate' ).css( 'display', 'none' );

			// Get active translations
			$( 'input.do_translate[checked]' ).each( function( index, value ) {
				$( '.translate_' + $( this ).attr( 'data' ) ).toggle();
				$( '#content_' + $( this ).attr( 'data' ) + '_ifr' ).height( '400px' );
			} );
		},

		/**
		 * Meta Box Toggle Switch
		 *
		 * @author	th
		 * @since	0.1
		 * @return	void
		 */
		meta_box_toggle_switch : function() {
			$( '.do_translate' ).live( 'click', function() {
				$( '.translate_' + $( this ).attr( 'data' ) ).toggle( 'slow' );
				$( '#content_' + $( this ).attr( 'data' ) + '_ifr' ).height( '400px' );
			} );
		}
	};
	// Kick-Off
	$( document ).ready( function( $ ) { advanced_translator.init(); } );
} )( jQuery );