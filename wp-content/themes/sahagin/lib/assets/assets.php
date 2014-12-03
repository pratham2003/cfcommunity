<?php

/**
 * Class Sahagin_Assets
 */
final class Sahagin_Assets
{
    /**
     * The Constructor
     */
    private function __construct()
    {
        //* Enqueue and dequeue fonts/styles
        add_action( 'wp_enqueue_scripts', array( $this, 'fonts' ) );

        //* Style Trump
        add_action( 'wp_enqueue_scripts', array( $this, 'style_trump' ), 99 );

        //* Register default background/headers
        $this->default_headers();
        add_filter( 'hybrid_default_backgrounds', array( $this, 'default_backgrounds' ), 11 );
    }

    /**
     * Get the Singleton instance
     */
    static function get_instance()
    {
        static $instance;
        if ( !isset( $instance ) ) {
            $instance = new Sahagin_Assets();
        }

        return $instance;
    }

    /**
     * Enqueue Fonts
     */
    function fonts()
    {
        wp_dequeue_style( 'saga-fonts' );
    }

    /**
     * Style Trump
     */
    function style_trump()
    {
        wp_dequeue_style( 'style' );
        wp_enqueue_style( 'style' );
    }

    /**
     * Register Default Headers
     */
    function default_headers()
    {
        register_default_headers( array(
            'header-01' => array(
                'url'           => '%2$s/lib/assets/images/headers/header-01.jpg',
                'thumbnail_url' => '%2$s/lib/assets/images/headers/header-01-thumb.jpg',
                'description'   => __( 'Header One', 'sahagin' )
            ),
            'header-02' => array(
                'url'           => '%2$s/lib/assets/images/headers/header-02.jpg',
                'thumbnail_url' => '%2$s/lib/assets/images/headers/header-02-thumb.jpg',
                'description'   => __( 'Header Two', 'sahagin' )
            )
        ) );
    }

    /**
     * Register Default Backgrounds
     */
    function default_backgrounds( $backgrounds )
    {
        $_backgrounds = array(
            'crossword' => array(
                'url'           => '%2$s/lib/assets/images/backgrounds/crossword.png',
                'thumbnail_url' => '%2$s/lib/assets/images/backgrounds/crossword-thumb.png',
            ),
            'greyzz'    => array(
                'url'           => '%2$s/lib/assets/images/backgrounds/greyzz.png',
                'thumbnail_url' => '%2$s/lib/assets/images/backgrounds/greyzz-thumb.png',
            )
        );

        return array_merge( $backgrounds, $_backgrounds );
    }

}
