<?php
/*
* Template for profile piture editing
*/
?>
<div class="profile" role="main">
                
    <h4><?php _e( 'Change Avatar', 'buddypress' ); ?></h4>

    <?php do_action( 'bp_before_profile_avatar_upload_content' ); ?>

    <?php if ( !(int)bp_get_option( 'bp-disable-avatar-uploads' ) ) : ?>

        <p><?php _e( 'Your avatar will be used on your profile and throughout the site. If there is a <a href="http://gravatar.com">Gravatar</a> associated with your account email we will use that, or you can upload an image from your computer.', 'buddypress'); ?></p>

        <form action="" method="post" id="avatar-upload-form" class="standard-form" enctype="multipart/form-data">

        <?php if ( 'crop-image' == bp_get_avatar_admin_step() ) : ?>

            <h5><?php _e( 'Crop Your New Avatar', 'buddypress' ); ?></h5>

            <img src="<?php bp_avatar_to_crop(); ?>" id="avatar-to-crop" class="avatar" alt="<?php _e( 'Avatar to crop', 'buddypress' ); ?>" />

            <div id="avatar-crop-pane">
                    <img src="<?php bp_avatar_to_crop(); ?>" id="avatar-crop-preview" class="avatar" alt="<?php _e( 'Avatar preview', 'buddypress' ); ?>" />
            </div>

            <input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php _e( 'Crop Image', 'buddypress' ); ?>" />

            <input type="hidden" name="image_src" id="image_src" value="<?php bp_avatar_to_crop_src(); ?>" />
            <input type="hidden" id="x" name="x" />
            <input type="hidden" id="y" name="y" />
            <input type="hidden" id="w" name="w" />
            <input type="hidden" id="h" name="h" />
            <input type="hidden" id="rtmp-profile-picture" name="rtmp-profile-picture" value="" />
            
            <?php wp_nonce_field( 'bp_avatar_cropstore' ); ?>

        <?php endif;  ?>

       </form>
     <?php else : ?>

            <p><?php _e( 'Your avatar will be used on your profile and throughout the site. To change your avatar, please create an account with <a href="http://gravatar.com">Gravatar</a> using the same email address as you used to register with this site.', 'buddypress' ); ?></p>

    <?php endif; ?>

</div>
