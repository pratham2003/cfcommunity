<div class="modal fade" id="AddProduct" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close fui-cross" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Purchased a new product? Fill in your product code below!</h4>
      </div>
      <div class="modal-body">
          <form name="login-form" id="sidebar-login-form" class="standard-form" role="form" action="<?php echo site_url( 'wp-login.php', 'login_post' ); ?>" method="post">
           <div class="form-group">
              <label>Add Your Product Code</label>
              <input type="text" name="log" placeholder="Your Product Code" id="sidebar-user-login" class="input form-control" value="<?php if ( isset( $user_login) ) echo esc_attr(stripslashes($user_login)); ?>" tabindex="97" />
          </div>
            </form>
      </div>

      <div class="modal-footer">
          <input type="submit" name="wp-submit" id="sidebar-wp-submit" value="<?php _e( 'Log In', 'buddypress' ); ?>" tabindex="100" />

          <?php do_action( 'bp_sidebar_login_form' ); ?>

          <input type="hidden" name="testcookie" value="1" />
      </div>
      </div>
  </div>        
</div>