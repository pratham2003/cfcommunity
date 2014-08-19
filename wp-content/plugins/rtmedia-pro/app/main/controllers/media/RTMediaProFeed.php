<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProFeed
 *
 * @author ritz
 */
class RTMediaProFeed {

    public $is_feed = false;
    public $title = "";
    public $link = "";
    public $ttl = "30";
    public $description = "";
    public $update_period = "hourly";
    public $update_frequency = "2";

    public function __construct() {

	//add_filter( "rtmedia_action_query_modifier_type", array( $this, "rtmedia_action_query_modifier_type"), 99, 2 );
	add_filter( "rtmedia_action_query_modifier_value", array( $this, "rtmedia_action_query_modifier_value"), 99, 2 );
	add_action( "rtmedia_pre_template", array($this,"rtmedia_pre_template" ), 99 );
	add_filter( "rtmedia_per_page_media", array( $this, "rtmedia_per_page_media" ), 99, 1);
	add_action( "init", array($this, "add_feed_option"), 999);
	add_action( 'wp_head',array($this,'rtmedia_feed') );
	add_action( 'rtmedia_buddypress_setting_content',array($this,'rtmedia_feed_content'), 20 );
//	add_filter("rtmedia_pro_settings_tabs_content", array($this,"rtmedia_pro_add_feed_tab"), 40, 1);
    }

    function rtmedia_pro_add_feed_tab($sub_tabs) {
	$sub_tabs[ ] = array(
                'href' => '#rtmedia-feed',
                'icon' => 'rtmicon-rss',
                'title' => __ ( 'rtMedia Feed and Podcast', 'rtmedia' ),
                'name' => __ ( 'Feed', 'rtmedia' ),
                'callback' => array( 'RTMediaProFeed', 'rtmedia_feed_content' )
            );
        return $sub_tabs;
    }

    static function rtmedia_feed_content() {
	global $rtmedia;
	$options = $rtmedia->options;
	$render_options = array();
	$render_options['rtmedia_enable_feed'] = array(
                'title' => __('Enable podcasting' ,'rtmedia'),
                'callback' => array('RTMediaFormHandler', 'checkbox'),
                'args' => array(
                        'key' => 'rtmedia_enable_feed',
                        'value' => $options['rtmedia_enable_feed'],
                        'desc' => __('This will add podcasting/RSS feed link to each and every BuddyPress profiles and groups. Link can be added to apple\'s iTune or any software that support podcasting.','rtmedia')
                )
        );
	$render_options['rtmedia_media_per_feed'] = array(
                'title' => __('Limit number of media items in feed' ,'rtmedia'),
                'callback' => array('RTMediaFormHandler', 'number'),
                'args' => array(
                        'key' => 'rtmedia_media_per_feed',
                        'value' => $options['rtmedia_media_per_feed'],
                        'desc' => __('You may need to set this higher considering bulk uploads.','rtmedia'),
			'class'=> array('rtmedia-setting-text-box')
                )
        );
	?>
	<div class="postbox metabox-holder">
	    <h3 class="hndle"><span>Podcasting and RSS Feed</span></h3>
	<?php
	foreach ($render_options as $key => $option) { ?>
		<div class="row section">
			<div class="columns large-9">
			    <?php echo $option['title']; ?>
			</div>
			<div class="columns large-3">
			    <?php call_user_func($option['callback'], $option['args']); ?>
			    <span data-tooltip class="has-tip" title="<?php echo (isset($option['args']['desc'])) ? $option['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
			</div>
		</div>
		<div class="clearfix"></div>
	<?php }
	?>
	</div>
	<?php
    }

    function rtmedia_feed() {
	global $wp_query,$rtmedia_query,$rtmedia;
	$options = $rtmedia->options;
	if( !defined( RTMEDIA_MEDIA_SLUG ) ) {
	    $media_slug = "media";
	} else {
	    $media_slug = RTMEDIA_MEDIA_SLUG;
	}
	if( ( isset( $rtmedia_query->media_query ) && sizeof( $rtmedia_query->media_query ) > 0 ) && ( isset( $wp_query->query_vars[ $media_slug ] ) ) && ( isset( $options['rtmedia_enable_feed'] ) && $options['rtmedia_enable_feed'] != "0" ) ) {
    ?>
	<link rel="alternate" type="application/rss+xml" title="<?php echo get_wp_title_rss()?>" href="?feed=rss" />
    <?php
	}
    }

    function add_feed_option() {
	global $rtmedia;
	$options = $rtmedia->options;
	if( isset( $options['rtmedia_enable_feed'] ) && $options['rtmedia_enable_feed'] != "0" ) {
	    add_filter('rtmedia_gallery_actions',array($this,'rtmedia_add_rss_feed'), 20 ,1);
	}
    }

    function rtmedia_add_rss_feed($options) {
	$options[] = '<a id="rtmedia-nav-item-rss" target="_blank" href="?feed=rss"><i class="rtmicon-rss"></i>'. __('RSS','rtmedia').'</a>';
	return $options;
    }

    function rtmedia_action_query_modifier_value( $modifier_value, $raw_query ) {
	if( $modifier_value == "feed" ) {
	    return "";
	}
	return $modifier_value;
    }

    function rtmedia_per_page_media( $per_page_media ) {
	global $wp_query,$rtmedia;
	$options = $rtmedia->options;
	if( !defined( RTMEDIA_MEDIA_SLUG ) ) {
	    $media_slug = "media";
	} else {
	    $media_slug = RTMEDIA_MEDIA_SLUG;
	}
	if( ( isset( $wp_query->query_vars[$media_slug] ) && ( strpos( $wp_query->query_vars[$media_slug],"feed" ) ) !== false ) || ( isset( $wp_query->query_vars['feed'] ) && $wp_query->query_vars['feed'] == "rss" ) ) {
	    if( isset( $options['rtmedia_media_per_feed'] ) ) {
		return $options['rtmedia_media_per_feed'];
	    }
	    return 30;
	}
	return $per_page_media;
    }

//    function rtmedia_action_query_modifier_type( $modifier_type, $raw_query ) {
//	if( is_array( $raw_query ) && array_search( "feed", $raw_query ) ) {
//	    $this->is_feed = true;
//	} else if( $modifier_type == "feed" ) {
//	    $this->is_feed = true;
//	    return "";
//	}
//	return $modifier_type;
//    }

    function rtmedia_pre_template() {
	global $rtmedia;
	$options = $rtmedia->options;
	if( ! ( isset( $options['rtmedia_enable_feed'] ) ) || ( isset( $options['rtmedia_enable_feed'] ) && $options['rtmedia_enable_feed'] == "0" ) ) {
	    return;
	}
	if( !defined( RTMEDIA_MEDIA_SLUG ) ) {
	    $media_slug = "media";
	} else {
	    $media_slug = RTMEDIA_MEDIA_SLUG;
	}
	global $wp_query,$rtmedia_query;
	if( ( isset( $rtmedia_query->media_query ) && sizeof( $rtmedia_query->media_query ) > 0 ) && ( ( isset( $wp_query->query_vars[$media_slug] ) && ( strpos( $wp_query->query_vars[$media_slug],"feed" ) ) !== false ) || isset( $wp_query->query_vars['feed'] ) && $wp_query->query_vars['feed'] == "rss" ) ) {
	    if( isset( $rtmedia_query->media[0]->post_date ) ) {
		$last_build_date = $rtmedia_query->media[0]->post_date;
	    } else {
		$last_build_date = "";
	    }
	    $this->link = get_site_url();
	    $this->title = get_wp_title_rss();
	    $this->description = "rtMedia media feeds";
	    header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
	    echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?'.'>';
	?>
	    <rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
		xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
		<?php do_action( 'rtmedia_pro_feed_rss_attributes' ); ?>
	>
	    <channel>
		<title><?php echo $this->title; ?></title>
		<link><?php echo $this->link; ?></link>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
		<description><?php echo $this->description ?></description>
		<lastBuildDate><?php echo  ($last_build_date != "") ? mysql2date( 'D, d M Y H:i:s O', $last_build_date, false ) : "NA" ; ?></lastBuildDate>
		<generator>http://rtcamp.com/rtmedia</generator>
		<language><?php bloginfo_rss( 'language' ); ?></language>
		<ttl><?php echo $this->ttl; ?></ttl>
		<sy:updatePeriod><?php echo $this->update_period; ?></sy:updatePeriod>
		<sy:updateFrequency><?php echo $this->update_frequency; ?></sy:updateFrequency>
		<?php do_action( 'rtmedia_pro_feed_channel_elements' ); ?>
		    <?php if ( have_rtmedia () ) { ?>
			<?php while ( have_rtmedia () ) : rtmedia (); ?>
			<?php
			    global $rtmedia_media;
			    if( isset( $rtmedia_media->post_date ) ) {
				$pubdate = $rtmedia_media->post_date;
			    } else {
				$post = get_post($rtmedia_media->media_id);
				$pubdate = $post->post_date;
			    }
			?>
				<item>
				    <guid isPermaLink="false"><?php rtmedia_permalink(); ?></guid>
				    <title><?php echo $text=preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $rtmedia_media->media_title); ?></title>
				    <link><?php rtmedia_permalink(); ?></link>
				    <pubDate><?php echo mysql2date( 'D, d M Y H:i:s O', $pubdate, false ); ?></pubDate>
				    <?php if( isset( $rtmedia_media->post_mime_type ) ) { ?>
					<enclosure url="<?php echo wp_get_attachment_url ( $rtmedia_media->media_id ) ?>" type="<?php echo $rtmedia_media->post_mime_type ?>" length="<?php echo filesize( get_attached_file( $rtmedia_media->media_id ) ) ?>"></enclosure>
				    <?php } ?>
				    <content:encoded><![CDATA[<img src="<?php rtmedia_image ( 'rt_media_thumbnail' ); ?>" >]]></content:encoded>
				    <?php do_action( 'rtmedia_pro_feed_item_elements' ); ?>
				</item>
			<?php endwhile; ?>
		    <?php } ?>
	    </channel>
	</rss>
	<?php
	    die();
	}
    }
}
