<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Global Plugin Settings Menu
 */
add_action( 'bp_init', 'bpfg_admin_init' );
function bpfg_admin_init() {
    add_action( bp_core_admin_hook(), 'bpfg_admin_page', 99 );
}

function bpfg_admin_page(){
    if ( ! is_super_admin() )
        return;

    bpfg_admin_page_save();

    add_submenu_page(
        bp_core_do_network_admin() ? 'settings.php' : 'options-general.php',
        __( 'BP Featured Groups', 'bpfg' ),
        __( 'BP Featured Groups', 'bpfg' ),
        'manage_options',
        'bpfg-admin',
        'bpfg_admin_page_content'
    );
}

function bpfg_admin_page_content(){
    $bpfg = bp_get_option( 'bpfg' ); ?>

    <div class="wrap">

        <h2><?php _e( 'BuddyPress Featured Groups', 'bpfg' ); ?> <sup>v<?php echo BPFG_VERSION ?></sup></h2>

        <?php
        if( isset($_GET['status']) && $_GET['status'] == 'saved') {
            echo '<div id="message" class="updated fade"><p>' . __('All options were successfully saved.', 'bpfg') . '</p></div>';
        }
        ?>

        <form action="" method="post" id="bpfg-admin-form">

            <p><?php _e( 'Below are several options that you can use to change the plugin behaviour.', 'bpfg' ); ?></p>

            <table class="form-table">

                <tr valign="top">
                    <th scope="row"><?php _e('Allow users to "pin" any group they want', 'bpfg'); ?></th>
                    <td>
                        <label>
                            <input name="bpfg[users_pin]" type="radio" value="yes" <?php checked('yes', $bpfg['users_pin']); ?>>&nbsp;
                            <?php _e('Enable', 'bpfg'); ?>
                        </label><br/>
                        <label>
                            <input name="bpfg[users_pin]" type="radio" value="no" <?php checked('no', $bpfg['users_pin']); ?>>&nbsp;
                            <?php _e('Disable', 'bpfg'); ?>
                        </label>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Filters / Tabs Title', 'bpfg'); ?></th>
                    <td>
                        <input name="bpfg[strings][filter]" type="text" value="<?php echo $bpfg['strings']['filter']; ?>" placeholder="<?php _e('Pinned Groups', 'bpfg'); ?>">
                        <p class="description"><?php _e('Used on Activity Directory page in Show filter, as label of a tab on Groups Directory. Default: '); ?></p>
                        <p class="description"><strong><?php _e('Default:', 'bpfg'); ?></strong> <?php _e('Pinned Groups', 'bpfg'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Groups Loop "Do" Action', 'bpfg'); ?></th>
                    <td>
                        <input name="bpfg[strings][groups_loop_action_do]" type="text" value="<?php echo $bpfg['strings']['groups_loop_action_do']; ?>" placeholder="<?php _e('Pin Group', 'bpfg'); ?>">
                        <p class="description"><?php _e('Used in groups loops on Groups Directory page and users profiles groups lists.'); ?></p>
                        <p class="description"><strong><?php _e('Default:', 'bpfg'); ?></strong> <?php _e('Pin Group', 'bpfg'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Groups Loop "Do" Action Title', 'bpfg'); ?></th>
                    <td>
                        <input name="bpfg[strings][groups_loop_action_do_title]" class="widefat" type="text" value="<?php echo $bpfg['strings']['groups_loop_action_do_title']; ?>" placeholder="<?php _e('Group will be displayed as a separate tab in Activity Directory', 'bpfg'); ?>">
                        <p class="description"><?php _e('Used in groups loops on Groups Directory page and users profiles groups lists on do action link hover.'); ?></p>
                        <p class="description"><strong><?php _e('Default:', 'bpfg'); ?></strong> <?php _e('Group will be displayed as a separate tab in Activity Directory', 'bpfg'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Groups Loop "Undo" Action', 'bpfg'); ?></th>
                    <td>
                        <input name="bpfg[strings][groups_loop_action_undo]" type="text" value="<?php echo $bpfg['strings']['groups_loop_action_undo']; ?>" placeholder="<?php _e('Unpin Group', 'bpfg'); ?>">
                        <p class="description"><?php _e('Used in groups loops on Groups Directory page and users profiles groups lists.'); ?></p>
                        <p class="description"><strong><?php _e('Default:', 'bpfg'); ?></strong> <?php _e('Unpin Group', 'bpfg'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Groups Loop "Undo" Action Title', 'bpfg'); ?></th>
                    <td>
                        <input name="bpfg[strings][groups_loop_action_undo_title]" class="widefat" type="text" value="<?php echo $bpfg['strings']['groups_loop_action_undo_title']; ?>" placeholder="<?php _e('Group will no be displayed as a separate tab in Activity Directory any more', 'bpfg'); ?>">
                        <p class="description"><?php _e('Used in groups loops on Groups Directory page and users profiles groups lists on do action link hover.'); ?></p>
                        <p class="description"><strong><?php _e('Default:', 'bpfg'); ?></strong> <?php _e('Group will no be displayed as a separate tab in Activity Directory any more', 'bpfg'); ?></p>
                    </td>
                </tr>

            </table>

            <p class="submit">
                <input class="button-primary" type="submit" name="bpfg-admin-submit" id="bpfg-admin-submit" value="<?php esc_attr_e( 'Save', 'bpfg' ); ?>" />
            </p>

            <?php wp_nonce_field( 'bpfg-admin' ); ?>

        </form><!-- #bpfg-admin-form -->
    </div><!-- .wrap -->
<?php
}

function bpfg_admin_page_save(){

    if( isset( $_POST['bpfg-admin-submit'] ) && isset( $_POST['bpfg'] ) ) {
        $bpfg = $_POST['bpfg'];

        if ( !isset($bpfg['users_pin']) ) {
            $bpfg['users_pin'] = 'yes';
        }

        $bpfg['strings']['filter'] = trim(htmlentities(wp_strip_all_tags($bpfg['strings']['filter'])));
        if ( empty($bpfg['strings']['filter']) ) {
            $bpfg['strings']['members'] = __('Pinned Groups', 'bpfg');
        }
        $bpfg['strings']['groups_loop_action_do'] = trim(htmlentities(wp_strip_all_tags($bpfg['strings']['groups_loop_action_do'])));
        if ( empty($bpfg['strings']['groups_loop_action_do']) ) {
            $bpfg['strings']['groups_loop_action_do'] = __('Pin Group', 'bpfg');
        }
        $bpfg['strings']['groups_loop_action_do_title'] = trim(htmlentities(wp_strip_all_tags($bpfg['strings']['groups_loop_action_do_title'])));
        if ( empty($bpfg['strings']['groups_loop_action_do_title']) ) {
            $bpfg['strings']['groups_loop_action_do_title'] = __('Group will be displayed as a separate tab in Activity Directory', 'bpfg');
        }
        $bpfg['strings']['groups_loop_action_undo'] = trim(htmlentities(wp_strip_all_tags($bpfg['strings']['groups_loop_action_undo'])));
        if ( empty($bpfg['strings']['groups_loop_action_undo']) ) {
            $bpfg['strings']['groups_loop_action_undo'] = __('Unpin Group', 'bpfg');
        }
        $bpfg['strings']['groups_loop_action_undo_title'] = trim(htmlentities(wp_strip_all_tags($bpfg['strings']['groups_loop_action_undo_title'])));
        if ( empty($bpfg['strings']['groups_loop_action_undo_title']) ) {
            $bpfg['strings']['groups_loop_action_undo_title'] = __('Group will not be displayed as a separate tab in Activity Directory any more', 'bpfg');
        }

        $bpfg = apply_filters('bpfg_admin_page_save', $bpfg);

        bp_update_option('bpfg', $bpfg);

        wp_redirect( add_query_arg( 'status', 'saved' ) );
    }

    return false;
}

/**
 * Group Admin area MetaBox - Register
 */
function bpfg_admin_meta_box_featured(){
    if ( !bp_is_active('activity') ) {
        return false;
    }

    add_meta_box(
        'bpfg_featured',
        __( 'Featured Group', 'bpfg' ),
        'bpfg_admin_meta_box_featured_edit',
        get_current_screen()->id,
        'side',
        'default'
    );
}
add_action( 'bp_groups_admin_meta_boxes', 'bpfg_admin_meta_box_featured', 1 );

/**
 * Group Admin area MetaBox - Content
 *
 * @param $group
 */
function bpfg_admin_meta_box_featured_edit($group){
    $is_featured = groups_get_groupmeta( $group->id, 'bpfg_is_featured' );
    $actab_label = groups_get_groupmeta( $group->id, 'bpfg_activity_tab_label' );
    $actab_class = groups_get_groupmeta( $group->id, 'bpfg_activity_tab_class' );
    $actab_order = groups_get_groupmeta( $group->id, 'bpfg_activity_tab_order' );
    ?>
    <style>
        .bpfg_activity_tab_extra_holder {
            display: none;
        }
        .bpfg_activity_tab_extra_holder > p {
            border-left: 2px solid orange;
            padding-left: 5px;
        }
    </style>
    <p>
        <label>
            <input type="checkbox" name="bpfg_is_featured" id="bpfg_is_featured" value="yes" <?php checked( 'yes', $is_featured ); ?> />
            <?php _e('This is a Featured Group', 'bpfg'); ?>
        </label>
    </p>

    <div class="bpfg_activity_tab_extra_holder hide">
        <p>
            <label>
                <?php _e('Change the activity tab label, based on group name:', 'bpfg'); ?>
                <input type="text" name="bpfg_activity_tab_label" class="widefat" value="<?php echo $actab_label; ?>" placeholder="<?php _e('Tab Label', 'bpfg'); ?>" />
            </label>
            <span class="description"><?php _e('Group name will be used if left blank', 'bpfg');?></span>
        </p>
        <p>
            <label>
                <?php _e('Add extra classes (separate by space) for the tab <code>li</code> element:', 'bpfg'); ?>
                <input type="text" name="bpfg_activity_tab_class" class="widefat" value="<?php echo $actab_class; ?>" />
            </label>
            <span class="description"><?php _e('This is useful if you want to add extra styling for these tabs, like icons etc.', 'bpfg');?></span>
        </p>
        <p>
            <label>
                <?php _e('Define the order of the tab:', 'bpfg'); ?>
                <input type="text" name="bpfg_activity_tab_order" class="widefat" value="<?php echo $actab_order; ?>" />
            </label>
            <span class="description"><?php _e('Integer numbers, starting from 0.', 'bpfg');?></span>
        </p>
    </div>
    <p class="description"><?php _e('By making this a featured group a tab/filter will be added to the activity stream of all members of this group.', 'bpfg'); ?></p>

    <script>
        bpfg_check_activity_tab();

        jQuery('#bpfg_is_featured').change(function(){
            bpfg_check_activity_tab();
        });

        function bpfg_check_activity_tab(){
            if ( jQuery('#bpfg_is_featured').is(':checked') ) {
                jQuery('.bpfg_activity_tab_extra_holder').show();
            } else {
                jQuery('.bpfg_activity_tab_extra_holder').hide();
            }
        }
    </script>
    <?php
}

/**
 * Group Admin area MetaBox - Save
 *
 * @param $group_id
 */
function bpfg_admin_meta_box_featured_save($group_id){
    if ( isset($_POST['bpfg_is_featured']) && $_POST['bpfg_is_featured'] === 'yes' ) {
        groups_update_groupmeta( $group_id, 'bpfg_is_featured', 'yes' );
        if ( isset($_POST['bpfg_activity_tab_label']) ) {
            groups_update_groupmeta( $group_id, 'bpfg_activity_tab_label', wp_strip_all_tags( $_POST[ 'bpfg_activity_tab_label' ] ) );
        }
        if ( isset($_POST['bpfg_activity_tab_class']) ) {
            groups_update_groupmeta( $group_id, 'bpfg_activity_tab_class', wp_strip_all_tags( $_POST[ 'bpfg_activity_tab_class' ] ) );
        }
        if ( isset($_POST['bpfg_activity_tab_order']) ) {
            $order = wp_strip_all_tags($_POST['bpfg_activity_tab_order']);
            if ( !is_numeric($order) ) {
                $order = '';
            }
            groups_update_groupmeta( $group_id, 'bpfg_activity_tab_order', $order );
        }
    }
}
add_action( 'bp_group_admin_edit_after', 'bpfg_admin_meta_box_featured_save', 10, 1 );