<?php 
/**
 * Infinity Theme: Post Thumbnail
 *
 * The Post Thumbnail Template part
 * 
 * @author Bowe Frankema <bowe@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Bowe Frankema
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package Infinity
 * @subpackage templates
 * @since 1.0
 */

global $post;
$author_id=$post->post_author;

$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'width=1170&height=410&crop=true' )
?>	
<!-- show the post thumb? -->
<div class="featured-image" style="background: url(<?php echo $src[0]; ?> )">

<div class="col-sm-12 col-xs-15 featured-text">
	 <div class="intro">
	 		<?php
				print get_avatar( get_the_author_meta( 'user_email' ), '50' );
			?>
	    <header>
	      <h1 class="entry-title"><?php the_title(); ?></h1>
	    </header> 
	    <div class="post-meta">
			<i class="fa fa-clock-o"></i> 3 Jan , 2014
			<i class="fa fa-video-camera"></i> Video
		</div> 
	    <?php echo get_the_excerpt(); ?> 
	</div>
</div>


</div>


