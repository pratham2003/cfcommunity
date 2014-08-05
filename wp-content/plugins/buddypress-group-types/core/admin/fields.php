<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function bpgt_admin_page_fields(){
    if ( isset($_GET['mode']) ) {
        if ( $_GET['mode'] == 'add_field') {
            bpgt_groups_admin_fields_add();
        } elseif( $_GET['mode'] == 'edit_field') {
            bpgt_groups_admin_fields_edit();
        }
    } else {
        bpgt_groups_admin_fields_list();
    }
}

function bpgt_groups_admin_fields_list() { ?>

    <div class="wrap">
        <?php screen_icon( 'buddypress-groups' ); ?>
        <h2>
            <?php _e( 'Groups Fields', 'bpgt' ); ?>
            <a id="add_group_field" class="add-new-h2" href="?page=bp-groups-fields&amp;mode=add_field">
                <?php _e('Add New', 'bpgt'); ?>
            </a>
        </h2>

    </div>
<?php
}

function bpgt_groups_admin_fields_add(){ ?>

    <div class="wrap">
        <?php screen_icon( 'buddypress-groups' ); ?>
        <h2>
            <?php _e( 'Add New Group Field', 'bpgt' ); ?>
        </h2>

    </div>
<?php
}

function bpgt_groups_admin_fields_edit(){ ?>

    <div class="wrap">
        <?php screen_icon( 'buddypress-groups' ); ?>
        <h2>
            <?php _e( 'Edit Group Field', 'bpgt' ); ?>
            <a id="add_group_field" class="add-new-h2" href="?page=bp-groups-fields&amp;mode=add_field">
                <?php _e('Add New', 'bpgt'); ?>
            </a>
        </h2>

    </div>
<?php
}