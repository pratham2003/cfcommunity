<?php

function bpfg_pin_assets(){
    wp_enqueue_script('bpfg-users-pin', BPFG_URL . '/users_pin.js', array('jquery'), BPFG_VERSION);
}
add_action('wp_enqueue_scripts', 'bpfg_pin_assets');

function bpfg_activity_filter_options(){
    if ( bp_is_active( 'groups' ) ) {
        echo '<option value="pinned_groups">'. __( 'Pinned Groups', 'bprf' ) . '</option>';
    }
}
add_action('bp_activity_filter_options', 'bpfg_activity_filter_options');
add_action('bp_member_activity_filter_options', 'bpfg_activity_filter_options');

function bpfg_groups_directory_group_filter(){
    $bpfg   = bp_get_option('bpfg');
    $pinned = bpfg_get_all_featured_groups('users'); ?>

    <?php if ( is_user_logged_in() && $pinned['total'] > 0 ) : ?>
        <li id="groups-pinned">
            <a href="<?php echo bp_loggedin_user_domain() . bp_get_groups_slug() . '/pinned/'; ?>">
                <?php echo $bpfg['strings']['filter']; ?>&nbsp;
                <span><?php echo $pinned['total']; ?></span>
            </a>
        </li>
    <?php endif; ?>

    <?php
}
add_action('bp_groups_directory_group_filter', 'bpfg_groups_directory_group_filter');

/**
 * Alter activity stream to display items from pinned groups only
 *
 * @param $bp_ajax_querystring string
 * @param $object string
 * @return string
 */
function bpfg_filter_pinned_output($bp_ajax_querystring, $object){
    $query = wp_parse_args($bp_ajax_querystring, array());

    if ( $object == buddypress()->activity->id && isset($query['action']) && $query['action'] == 'pinned_groups' ){
        $query['object']     = 'groups';
        $query['primary_id'] = array();
        unset($query['type'], $query['action']);

        $groups = bpfg_get_all_featured_groups();
        foreach($groups['groups'] as $group){
            $query['primary_id'][] = $group->id;
        }

        if ( bp_is_user() ) {
            $query['user_id'] = bp_displayed_user_id();
        }

        return $query;
    }

    if ( $object == buddypress()->groups->id && isset($query['scope']) && $query['scope'] == 'pinned' ){
        $query['include'] = array();

        $groups = bpfg_get_all_featured_groups('users');
        foreach($groups['groups'] as $group){
            $query['include'][] = $group->id;
        }

        return $query;
    }

    return $bp_ajax_querystring;
}
add_filter( 'bp_ajax_querystring', 'bpfg_filter_pinned_output', 999, 2 );

/**
 * Add (Un)Pin button in groups-loop actions area
 *
 * @param bool $group
 */
function bpfg_groups_action_pin($group = false){
    global $groups_template;
    $bpfg = bp_get_option('bpfg');

    /** @var $group BP_Groups_Group */
    if ( empty( $group ) ) {
        $group =& $groups_template->group;
    }

    // get user pinned groups
    $pinned_groups = (array) bp_get_user_meta( bp_loggedin_user_id(), 'bpfg_pinned', true );

    if ( empty($pinned_groups) || !in_array($group->id, $pinned_groups) ) {
        $link_class = 'pin-group';
        $link_text  = $bpfg['strings']['groups_loop_action_do'];
        $link_href  = wp_nonce_url( bp_get_group_permalink( $group ) . 'pin', 'groups_pin_group' );
        $link_title = $bpfg['strings']['groups_loop_action_do_title'];
    } else {
        $link_class = 'unpin-group';
        $link_text  =$bpfg['strings']['groups_loop_action_undo'];
        $link_href  = wp_nonce_url( bp_get_group_permalink( $group ) . 'unpin', 'groups_unpin_group' );
        $link_title = $bpfg['strings']['groups_loop_action_undo_title'];
    }

    echo apply_filters( 'bpfg_groups_action_pin', bp_get_button( array(
            'id'                => 'pin_group',
            'component'         => 'groups',
            'must_be_logged_in' => true,
            'block_self'        => false,
            'wrapper_class'     => 'featured-group-button',
            'wrapper_id'        => 'bpfg-' . $group->id,
            'link_href'         => $link_href,
            'link_text'         => $link_text,
            'link_title'        => $link_title,
            'link_class'        => $link_class,
        ) ), $group, $pinned_groups
    );
}
add_action('bp_directory_groups_actions', 'bpfg_groups_action_pin', 90);

function bpfg_users_pin_ajax(){
    $bpfg   = bp_get_option('bpfg');
    $method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

    $result = array(
        'status'  => 'error',
        'class'   => '',
        'message' => ''
    );

    switch($method){
        case 'pin_group':
            if ( bpfg_do_pin_group(trim(wp_strip_all_tags($_POST['gid']))) ) {
                $result = array(
                    'status'  => 'success',
                    'message' => $bpfg['strings']['groups_loop_action_undo'],
                    'class'   => 'unpin-group'
                );
            }
            break;

        case 'unpin_group':
            if ( bpfg_do_unpin_group(trim(wp_strip_all_tags($_POST['gid']))) ) {
                $result = array(
                    'status'  => 'success',
                    'message' => $bpfg['strings']['groups_loop_action_do'],
                    'class'   => 'pin-group'
                );
            }
            break;
    }

    die(json_encode($result));
}
add_action('wp_ajax_bpfg_users_pin_ajax', 'bpfg_users_pin_ajax');

function bpfg_do_pin_group($group_id){
    $user_id = bp_loggedin_user_id();
    $pinned  = (array) bp_get_user_meta($user_id, 'bpfg_pinned', true);

    $group_id = (int) $group_id;

    if ( !in_array($group_id, $pinned) ) {
        $pinned[ ] = $group_id;
    }

    return bp_update_user_meta($user_id, 'bpfg_pinned', $pinned);
}

function bpfg_do_unpin_group($group_id){
    $user_id = bp_loggedin_user_id();
    $pinned  = bp_get_user_meta($user_id, 'bpfg_pinned', true);
    $new     = array();

    foreach($pinned as $pin){
        if ( $pin != $group_id ) {
            $new[] = $pin;
        }
    }

    return bp_update_user_meta($user_id, 'bpfg_pinned', $new);
}