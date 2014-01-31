<?php
/**
 * The Template for displaying all single campaigns.
 *
 * @package Fundify
 * @since Fundify 1.0
 */

global $campaign;

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); $campaign = atcf_get_campaign( $post->ID ); ?>

		<?php locate_template( array( 'campaign/title.php' ), true ); ?>
		
		<div id="content" class="post-details">
			<div class="container">		

		<div class="call-out">
			<h2>Help ons bij het opzetten van een online community voor mensen met Cystic Fibrosis. Lees ons verhaal en doneer direct via iDeal! Elke donatie, hoe klein ook, helpt! <a class="contribute" href="http://support.cfcommunity.net/campaigns/cfcommunity-net/#contribute-now">Doneer nu!</a></h2>
		</div>
				
				<?php do_action( 'atcf_campaign_before', $campaign ); ?>
				
				<?php //locate_template( array( 'searchform-campaign.php' ), true ); ?>

				<?php locate_template( array( 'campaign/project-details.php' ), true ); ?>

				<?php locate_template( array( 'campaign/campaign-sort-tabs.php' ), true ); ?>

				<aside id="sidebar">
					<?php // locate_template( array( 'campaign/author-info.php' ), true ); ?>

					<div id="contribute-now" class="single-reward-levels">
						<?php 
							if ( $campaign->is_active() ) :
								echo edd_get_purchase_link( array( 
									'download_id' => $post->ID,
									'class'       => '',
									'price'       => false,
									'text'        => __( 'Doneer Nu!', 'fundify' )
								) ); 
							else : // Inactive, just show options with no button
								atcf_campaign_contribute_options( edd_get_variable_prices( $post->ID ), 'checkbox', $post->ID );
							endif;
						?>						
					</div>
					<div class="fb-like-box" data-href="https://www.facebook.com/pages/CFCommunitynet-Where-people-with-Cystic-Fibrosis-meet/176854133478" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false"></div>
						
<h4>International Campaign: We're also running a big campaign on IndieGogo to raise funds for CFCommunity. Please share that campaign with your international friends & family!</h4>
					<iframe src="http://www.indiegogo.com/project/259614/widget/765375" width="224px" height="486px" frameborder="0" scrolling="no"></iframe>
				</aside>

				<div id="main-content">
					<?php locate_template( array( 'campaign/meta.php' ), true ); ?>
					
					<strong>Deel onze campagne!</strong>
					<p>
<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style addthis_32x32_style" addthis:url="http://support.cfcommunity.net"><a class="addthis_button_facebook"></a><a class="addthis_button_twitter"></a><a class="addthis_button_pinterest_share"></a><a class="addthis_button_gmail"></a><a class="addthis_button_linkedin"></a><a class="addthis_button_google_plusone_share"></a><a class="addthis_button_compact"></a><a class="addthis_counter addthis_bubble_style"></a>
</div>
<script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4e933d57217f2a00"></script>
</p>
<br>
<br>
<!-- AddThis Button END -->

					<div class="entry-content inner campaign-tabs">
						<div id="description">
							<?php the_content(); ?>
							<a href="#contribute-now" class="btn-green contribute">Steun ons nu!</a>
						</div>

						<?php locate_template( array( 'campaign/updates.php' ), true ); ?>

						<?php comments_template(); ?>

						<?php locate_template( array( 'campaign/backers.php' ), true ); ?>
					</div>
				</div>

			</div>
		</div>

	<?php endwhile; ?>

<?php get_footer(); ?>