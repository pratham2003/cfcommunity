<form method="post" action="<?php echo admin_url(); ?>options.php">
<?php settings_fields('pusher-settings'); ?>
<?php do_settings_sections('pusher-settings'); ?>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label>Push to deploy URL (Pro)</label>
            </th>
            <td>
                <input name="wppusher_token" type="text" id="wppusher_token" value="<?php echo esc_attr(get_site_url() . '?wppusher-hook&token=' . get_option('wppusher_token')); ?>" class="regular-text" disabled>
                &nbsp; <input type="submit" name="submit" id="submit" class="button" value="Refresh token">
                <p class="description"><strong>Note</strong> that refreshing the token, means re-pasting the URL to the respective GitHub and Bitbucket repositories, where push to deploy is set up.</p>
            </td>
        </tr>
    </tbody>
</table>
</form>

<hr>
