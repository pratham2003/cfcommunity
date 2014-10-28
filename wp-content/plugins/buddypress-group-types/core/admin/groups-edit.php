<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * All custom BPGT metaboxes
 */
function bpgt_register_groups_admin_meta_boxes() {
    add_meta_box(
        'bpgt_select_type',
        __( 'Group Type' ),
        'bpgt_group_admin_meta_box_select_type',
        is_multisite() ? 'toplevel_page_bp-groups-network' : 'toplevel_page_bp-groups',
        'side'
    );
}
add_action('admin_init', 'bpgt_register_groups_admin_meta_boxes');

/**
 * Select group type in admin area
 *
 * @param object $group
 */
function bpgt_group_admin_meta_box_select_type($group){
    $type_id = groups_get_groupmeta($group->id, 'bpgt_group_type', true);
    $types   = BPGT_Types::get();

    if ( empty($type_id) || empty($types->posts) ) {
        $type_id = '';
    }
    ?>

    <ul>
        <li>
            <label>
                <input type="radio" name="bpgt_group_type" value="" <?php checked($type_id, ''); ?> />&nbsp;
                <?php _e('Default Group', 'bpgt'); ?>
            </label>
        </li>
        <?php
        if ( $types->found_posts > 0 ) {
            foreach($types->posts as $type){ ?>
                <li>
                    <label>
                        <input type="radio" name="bpgt_group_type" value="<?php echo $type->ID; ?>" <?php checked($type_id, $type->ID); ?> />&nbsp;
                        <?php echo stripslashes($type->post_title); ?>
                    </label>
                </li>
            <?php
            }
        }

        ?>
    </ul>
<?php
}

/**
 * Save the group type in admin area
 *
 * @param int $group_id
 */
function bpgt_group_admin_meta_box_select_type_save($group_id){
    if ( isset($_POST['bpgt_group_type']) ) {
        $type_id = wp_strip_all_tags($_POST['bpgt_group_type']);

        /** @var $wpdb WPDB */
        global $wpdb;

        // get old type_id if any
        $old_type_id = groups_get_groupmeta($group_id, 'bpgt_group_type', true);

        if ( empty($type_id) || !is_numeric($type_id)) {
            if ( groups_update_groupmeta($group_id, 'bpgt_group_type', '0') ) {
                // deduct from old type
                if ( !empty($old_type_id) ) {
                    $old_type_comment_count = $wpdb->get_var( "SELECT comment_count FROM {$wpdb->posts} WHERE ID = {$old_type_id}" );
                    $old_type_comment_count = $old_type_comment_count > 0 ? $old_type_comment_count - 1 : 0;
                    $wpdb->update( $wpdb->posts, array( 'comment_count' => $old_type_comment_count ), array( 'ID' => $old_type_id ) );
                }
            }
        } else {
            if ( groups_update_groupmeta($group_id, 'bpgt_group_type', $type_id) ) {
                // deduct from old type
                if ( !empty($old_type_id) ) {
                    $old_type_comment_count = $wpdb->get_var( "SELECT comment_count FROM {$wpdb->posts} WHERE ID = {$old_type_id}" );
                    $old_type_comment_count = $old_type_comment_count > 0 ? $old_type_comment_count - 1 : 0;
                    $wpdb->update( $wpdb->posts, array( 'comment_count' => $old_type_comment_count ), array( 'ID' => $old_type_id ) );
                }

                // add to new type
                $new_type_comment_count = (int) $wpdb->get_var( "SELECT comment_count FROM {$wpdb->posts} WHERE ID = {$type_id}" );
                $wpdb->update( $wpdb->posts, array('comment_count' => $new_type_comment_count + 1), array('ID' => $type_id) );
            }
        }

        do_action('bpgt_change_group_type', $group_id, $type_id, $old_type_id);
    }
}
add_action('bp_group_admin_edit_after', 'bpgt_group_admin_meta_box_select_type_save');
