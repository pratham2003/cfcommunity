<?php 
	
/* ADDS A VISIBLE FLAG TO YOUR STAGING/DEV ENVIRONMENTS
 *
 * Drop me in mu-plugins
 */

/**
 * Adds 'local environment' tab
 */
function bbg_env_type_flag() {
	if ( defined( 'ENV_TYPE' ) && 'production' !== ENV_TYPE ) {
		?>

 <style type="text/css">
            #env-type-flag {
                background: none repeat scroll 0 0 #CB0808;
                border: 1px solid #820C0C;
                border-radius: 6px 6px 6px 6px;
                bottom: 50px;
                color: #FFFFFF;
                font-size: 1.2em;
                line-height: 1.8em;
                opacity: 0.75;
                padding: 8px;
                position: fixed;
                right: 0;
                text-align: center;
                text-shadow: 0 1px 0 #000000;
                width: 226px;
                z-index: 1000;
            }
        </style>
		<div id="env-type-flag">
			<?php echo strtoupper( ENV_TYPE ) ?> ENVIRONMENT
		</div>

		<?php
	}
}
add_action( 'wp_footer', 'bbg_env_type_flag' );
add_action( 'admin_footer', 'bbg_env_type_flag' );
	
?>
