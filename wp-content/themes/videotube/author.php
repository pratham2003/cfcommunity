<?php if( !defined('ABSPATH') ) exit;?>
<?php get_header();?>
	<div class="container">
		<div class="row">
			<div class="col-sm-8">
            	<div class="section-header">
                    <h3>
                    <?php 
                    global $wp_query;
                    print $wp_query->query_vars['author_name'];   
                    ?>
                    </h3>
                </div>			
				<?php 
					if( have_posts() ) : while ( have_posts() ) : the_post();
							get_template_part('loop','post');
						endwhile;
					endif;?>
                <ul class="pager">
                	<?php posts_nav_link(' ','<li class="previous">'.__('&larr; Older','mars').'</a></li>',' <li class="next">'.__('Newer &rarr;','mars').'</a></li>'); ?>
                </ul>
			</div>
			<?php get_sidebar();?>
		</div><!-- /.row -->
	</div><!-- /.container -->			
<?php get_footer();?>

