<?php
/*
Template Name: Supporter Template
*/
?>

<div class="page-header">
  <h1>
    Our Supporters  </h1>
</div>
	
<div class="intro"><?php _e('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo 
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 	', 'cfcommunity'); ?>
</div>


<div class="supporter-loop">
	<?php if ( bp_has_members( 'type=random&max=50&per_page=50&exclude=1' ) ) : ?>
	<div id="members-list" class="avatar-block" role="main">

			<ul id="members-list" class="item-list">
				<?php while ( bp_members() ) : bp_the_member(); ?>
					<li class="vcard">
						<div class="item-avatar">
							<a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_avatar() ?></a>
						</div>

						<div class="item">
							<div class="item-title fn"><a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_name() ?></a>
							</div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
	</div>
<?php endif; ?>
</div>