<?php  if ( function_exists( 'bp_is_member' ) ):  ?>
<ul id="bp-user-navigation" class="nav navbar-nav navbar-right"> 

<?php if ( is_user_logged_in() ): ?>

	<?php cfc_notifications_buddybar_menu(); ?>

	<li id="bp-profile-menu" class="dropdown menu-groups">
		<a href="<?php echo bp_get_loggedin_user_link(); ?>" data-target="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo bp_loggedin_user_avatar( 'type=thumb&width=70&height=70' ); ?><span class="visible-xs"><?php echo bp_core_get_user_displayname( bp_loggedin_user_id() );?></span></a>

			<?php cfc_adminbar_account_menu(); ?>
	</li>

<?php else: ?>
	<li class="menu-register">
		<a href="<?php echo bp_get_signup_page()?>"><i class="fa fa-user"></i> <?php _e('Register', 'cfctranslation'); ?>	</a>
	</li>
<li class="dropdown menu-groups">
		<a href="/menu/" data-target="#" data-toggle="dropdown" class="dropdown-toggle"><i class="fa fa-sign-in"></i> <?php _e('Log In', 'cfctranslation'); ?>	</a>
		<ul class="dropdown-menu">
		<li>
			<?php wp_login_form();?>

			<div id="facebook-login">
			<?php if ( function_exists( 'jfb_output_facebook_btn' ) ) {
				jfb_output_facebook_btn();
			} ?>
			</div>	

		</li>
		</ul>
</li>
		 
<?php endif; ?>
</ul>
<?php endif; ?>

<?php if ( is_user_logged_in() ): ?>
	<ul class="nav navbar-nav navbar-right search-bar"> 
	    <li class="search nav">
	    <form role="search" method="get" action="<?php echo home_url('/'); ?>">
	        <input type="search" value="<?php if (is_search()) { echo get_search_query(); } ?>" name="s" class="search-field form-control" placeholder="<?php _e('Search for anything on CFCommunity', 'cfctranslation'); ?>">
	      <button type="submit" class="btn"><i class="fa fa-search"></i></button>
	     </form>
	    </li>
	</ul>
<?php endif; ?>


