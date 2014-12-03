<?php
/**
 * WordPress.com-specific functions and definitions
 *
 * @package Pachyderm
 * @since Pachyderm 1.0
 */

global $themecolors;

/**
 * Set a default theme color array for WP.com.
 *
 * @global array $themecolors
 * @since Pachyderm 1.0
 */
$themecolors = array(
	'bg'     => 'ffffff',
	'border' => 'eeeeee',
	'text'   => '555555',
	'link'   => 'f15d5d',
	'url'    => 'f15d5d',
);


/*
 * De-queue Google fonts if custom fonts are being used instead
 */

function pachyderm_dequeue_fonts() {
	if ( class_exists( 'TypekitData' ) ) {
		if ( TypekitData::get( 'upgraded' ) ) {
			$customfonts = TypekitData::get( 'families' );
				if ( ! $customfonts )
					return;

				$site_title = $customfonts['site-title'];
				$headings = $customfonts['headings'];
				$body_text = $customfonts['body-text'];

				if ( $site_title['id'] && $headings['id'] && $body_text['id'] ) {
					wp_dequeue_style( 'pachyderm-berkshire-swash' );
					wp_dequeue_style( 'pachyderm-gudea' );
					wp_dequeue_style( 'pachyderm-poiret-one' );
				}
		}
	}
}

add_action( 'wp_enqueue_scripts', 'pachyderm_dequeue_fonts' );