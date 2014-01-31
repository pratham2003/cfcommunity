<?php  if ( function_exists( 'bp_is_member' ) ):  ?>
<ul id="bp-user-navigation" class="nav navbar-nav pull-right"> 
<?php if ( is_user_logged_in() ): ?>

	<li class="header-avatar"><a title="View your Public Profile" href="<?php echo bp_get_loggedin_user_link();?>"><?php echo bp_loggedin_user_avatar( 'type=thumb&width=80&height=80' ); ?></a></li>
	<li class="notifications"><?php bp_adminbar_notifications_menu(); ?></li>

	<li class="dropdown menu-links">
		<a class="dropdown-toggle" href="#"><span class="glyphicon glyphicon-search"></span>My Profile</a>

	  <?php
		bp_nav_menu(array('menu_class' => 'nav navbar-nav', 'container' => false, 'menu_class' => 'dropdown-menu' ));
	  ?>	
	</li>
	     
<?php else: ?>
	
	<li class="dropdown menu-links">
 		  <!-- Button trigger modal -->
  		<a data-toggle="modal" class="bp-login-button" href="#myModal">Launch demo modal</a>
  		<a class="bp-login-button-no-js" href="<?php echo wp_login_url( $redirect ); ?> "><?php _e( 'Log In', 'buddypress' ); ?></a>
	</li>
		 
<?php endif; ?>
</ul>
<?php endif; ?>
