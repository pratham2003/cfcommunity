<?php
/**
 * Plugin Name: BuddyPress Featured Groups
 * Plugin URI:  https://github.com/BoweFrankema/buddypress-featured-groups
 * Description: Give extra power to selected groups
 * Version:     1.0
 * Author:      slaFFik
 * Author URI:  http://ovirium.com
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BPFG_VERSION',   '1.0' );
define( 'BPFG_PATH',      dirname(__FILE__) . '/core'); // without /
define( 'BPFG_URL',       plugins_url('_inc', __FILE__ )); // link to all assets, without /


/**
 * What to do on activation
 */
register_activation_hook( __FILE__, 'bpfg_activation');
function bpfg_activation() {
    // some defaults
    $bpfg = array(
        'users_pin' => 'yes',
        'strings'   => array(
            'filter'                        => __('Pinned Groups', 'bpfg'),
            'groups_loop_action_do'         => __('Pin Group', 'bpfg'),
            'groups_loop_action_do_title'   => __('Group will be displayed as a separate tab in Activity Directory', 'bpfg'),
            'groups_loop_action_undo'       => __('Unpin Group', 'bpfg'),
            'groups_loop_action_undo_title' => __('Group will not be displayed as a separate tab in Activity Directory any more', 'bpfg'),
        )
    );

    bp_add_option('bpfg', $bpfg);
}

/**
 * In case somebody will want to translate the plugin
 */
function bpfg_load_textdomain() {
    load_plugin_textdomain( 'bpfg', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}
add_action( 'plugins_loaded', 'bpfg_load_textdomain' );

/**
 * Admin, helpers and ajax
 */
function bpfg_init() {
    if ( !bp_is_active('groups') ) {
        return false;
    }

    // used globally
    include_once( BPFG_PATH . '/helpers.php' );

    if ( is_admin() ) {
        include_once( BPFG_PATH . '/admin.php' );
    }
}
add_action('init', 'bpfg_init');

/**
 * Load different parts of a plugin in proper places
 */
function bpfg_load_extensions(){
    if ( !bp_is_active('groups') ) {
        return false;
    }

    $bpfg = bp_get_option('bpfg');

    if ( $bpfg['users_pin'] == 'yes' ) {
        include_once(BPFG_PATH . '/ext/users_pin.php');
    }
}
add_action('bp_loaded', 'bpfg_load_extensions');

/**
 * Add tabs to Activity Directory
 */
function bpfg_activity_type_tabs(){
    if ( !bp_is_active('groups') ) {
        return false;
    }

    $groups = bpfg_get_all_featured_groups();

    // display them as an activity tab
    if ( $groups['total'] > 0 ) {
        $ordered = array();

        $i = 1000;
        foreach($groups['groups'] as $group) {
            $order = groups_get_groupmeta( $group->id, 'bpfg_activity_tab_order' );

            if ( empty($order) && $order != '0' ) {
                $order = $i;
            }
            if ( !isset($ordered[ $order ]) ) {
                $ordered[ $order ] = $group;
            } else {
                $order++;
                $ordered[ $order ] = $group;
            }
            $i++;
        }

        ksort($ordered);

        foreach($ordered as $group) {
            $label = groups_get_groupmeta( $group->id, 'bpfg_activity_tab_label' );
            $class = groups_get_groupmeta( $group->id, 'bpfg_activity_tab_class' );
            if ( empty($label) ) {
                $label = $group->name;
            }
            ?>
            <li id="activity-favgroup-<?php echo $group->id; ?>" class="activity-favorite-group <?php echo $class; ?>">
                <a href="<?php bp_group_permalink($group); ?>">
                    <?php echo $label; ?>
                </a>
            </li>
        <?php
        }
    }
}
add_action('bp_activity_type_tabs', 'bpfg_activity_type_tabs');

/**
 * Alter the activity stream to display group only activities on its tab
 *
 * @param $bp_ajax_querystring string
 * @param $object string
 * @return string
 */
function bpfg_filter_actvity_feed($bp_ajax_querystring, $object){
    if ( !bp_is_active('groups') ) {
        return false;
    }

    if( bp_is_activity_directory() && $object == 'activity' ) {
        $args = wp_parse_args( $bp_ajax_querystring, array() );

        if ( !isset($args['scope']) || empty($args['scope'])){
            $args['scope'] = isset($_POST['scope']) ? $_POST['scope'] : isset($_COOKIE['bp-activity-scope']) ? $_COOKIE['bp-activity-scope'] : '';
        }

        if ( !strpos($args['scope'], 'avgroup-') ) {
            return $bp_ajax_querystring;
        }

        $data = explode('-', $args['scope']);

        $args[ 'show_hidden' ] = true;
        $args[ 'object' ]      = 'groups';
        $args[ 'primary_id' ]  = $data[ 1 ];

        return $args;
    }

    return $bp_ajax_querystring;
}
add_filter( 'bp_ajax_querystring', 'bpfg_filter_actvity_feed', 99, 2 );