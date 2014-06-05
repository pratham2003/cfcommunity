<?php

/**
 * Class Sample_Product
 *
 * Our sample product class
 */
class Yoast_Product_WPSEO_Video extends Yoast_Product {

	public function __construct() {
		parent::__construct(
				'https://yoast.com',
				'Video SEO for WordPress',
				plugin_basename( wpseo_Video_Sitemap::get_plugin_file() ),
				WPSEO_VIDEO_VERSION,
				'https://yoast.com/wordpress/plugins/video-seo/',
				'admin.php?page=wpseo_licenses#top#licenses',
				'yoast-video-seo',
				'Yoast'
		);
	}

}