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
$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'width=1200&height=250&crop=true' )
?>	
<!-- show the post thumb? -->
<div class="featured-page-image" style="background: url(<?php echo $src[0]; ?> )">

</div>
