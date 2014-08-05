<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function bpfg_get_all_featured_groups($type = 'all'){
    $bpfg    = bp_get_option('bpfg');
    $include = array();

    if ( $bpfg['users_pin'] == 'yes' ) {
        $include = bp_get_user_meta(bp_loggedin_user_id(), 'bpfg_pinned', true);
    }

    if ( !empty($include)) {
        // get user-pinned groups
        $pinned = BP_Groups_Group::get( array(
                                            'order'   => 'ASC',
                                            'orderby' => 'name',
                                            'user_id' => bp_loggedin_user_id(),
                                            'include' => $include
                                        )
        );

        if ( $type == 'users' ) {
            return $pinned;
        }
    }
    else {
        $pinned = array(
            'total' => 0,
            'groups' => array()
        );
    }

    // get global featured groups for loggedin user
    $featured = BP_Groups_Group::get(array(
                                         'order'      => 'ASC',
                                         'orderby'    => 'name',
                                         'user_id'    => bp_loggedin_user_id(),
                                         'meta_query' => array(array(
                                                                   'key'    => 'bpfg_is_featured',
                                                                   'value'  => 'yes'
                                                               ))
                                     ));

    $all_total = $pinned['total'] + $featured['total'];
    $all_groups = array();

    foreach($pinned['groups'] as $group) {
        $all_groups[$group->id] = $group;
    }
    foreach($featured['groups'] as $group) {
        if ( !isset($all_groups[$group->id]) ) {
            $all_groups[ ] = $group;
        }
    }

    return array(
        'total'  => $all_total,
        'groups' => $all_groups
    );
}