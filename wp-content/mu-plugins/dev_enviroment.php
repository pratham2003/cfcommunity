<?php
/**
 * ADDS A VISIBLE FLAG TO YOUR STAGING/DEV ENVIRONMENTS
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
        position: fixed;
        right: 0;
        bottom: 35px;
        width: auto;
        padding: 10px 15px;
        text-align: center;
        background: #444;
        color: #fff;
        font-size: 14px;
        line-height: 1.8em;
        border: 1px solid #454545;
        z-index: 1000;
        font-weight: 800;
      }
      #env-type-flag i {
        font-size: 20px;
        color: #f9a516;
        position: relative;
        top: 3px;
        left: -2px;
      }
    </style>

    <div id="env-type-flag">
      <i class="fa fa-coffee"></i> NO SWEAT, WE LOCAL
    </div>

    <?php
  }
}
add_action( 'wp_footer', 'bbg_env_type_flag' );
add_action( 'admin_footer', 'bbg_env_type_flag' );
?>