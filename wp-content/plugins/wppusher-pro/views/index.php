<h2><?php echo $data['name']; ?> Dashboard</h2>

<?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') { ?>
    <div class="updated"><p>Settings updated.</p></div>
<?php } ?>

<hr>

<div class="theme-browser">
    <div class="themes">
        <div class="theme" style="border: none; cursor: auto;">
            <h3>Get started</h3>
            <p>Documentation is available on <a href="https://github.com/petersuhm/wppusher-documentation" target="_blank">GitHub</a>.</p>
        </div>
        <?php if ($data['hasPro']) { ?>

        <div class="theme" style="border: none; cursor: auto;">
            <h3>Need help?</h3>

            <p>Support is offered via e-mail. Send an e-mail to <a href="mailto:support@wppusher.com">support@wppusher.com</a> and we will get back to you as soon as we can. Please identify yourself if you use another e-mail address than the one you gave us when you bought WP Pusher.</p>
        </div>

        <?php } else { ?>
        <div class="theme" style="border: none; cursor: auto;">
            <h3>WP Pusher Pro</h3>

            <ul style="list-style-type: disc;">
                <li>
                    <strong>Push-to-deploy</strong> can automatically trigger updates whenever you push to GitHub or Bitbucket
                </li>
                <li>
                    E-mail <strong>support</strong>
                </li>
            </ul>

            <p><strong><a target="_blank" href="http://wppusher.com/pro">Upgrade to PRO now</a>!</strong></p>

        </div>
        <div class="theme" style="border: none; cursor: auto;">
            <h3>Need help?</h3>

            <p>Support is only available to <strong>PRO users</strong>. However, if you experience an issue, you're can submit it <a href="https://github.com/petersuhm/wppusher-documentation/issues" target="_blank">here</a>.</p>

            <p><strong><a target="_blank" href="http://wppusher.com/pro">Upgrade to PRO now</a>!</strong></p>
        </div>
        <?php } ?>
    </div>
</div>

<div class="clear"></div>

<h2>Settings</h2>

<hr>

<?php if ($data['hasPro']) include 'pro/token.php'; ?>

<h3>GitHub</h3>

<form method="post" action="<?php echo admin_url(); ?>options.php">
<?php settings_fields('pusher-gh-settings'); ?>
<?php do_settings_sections('pusher-gh-settings'); ?>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label>GitHub token</label>
            </th>
            <td>
                <input name="gh_token" type="text" id="gh_token"  placeholder="<?php echo (get_option('gh_token')) ? '********' : null; ?>" class="regular-text">
                <p class="description">You only need a token if your repositories are private.</p>
                <p class="description">Learn more about GitHub tokens <a target="_blank" href="https://help.github.com/articles/creating-an-access-token-for-command-line-use/">here</a>.</p>
            </td>
        </tr>
    </tbody>
</table>
<?php submit_button('Save GitHub token'); ?>
</form>

<hr>

<h3>Bitbucket</h3>

<form method="post" action="<?php echo admin_url(); ?>options.php">
<?php settings_fields('pusher-bb-settings'); ?>
<?php do_settings_sections('pusher-bb-settings'); ?>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <label>Bitbucket username</label>
            </th>
            <td>
                <input name="bb_user" type="text" id="bb_user" value="<?php echo esc_attr(get_option('bb_user')); ?>" class="regular-text">
                <p class="description">Only neccessary if you have private repositories.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label>Bitbucket password</label>
            </th>
            <td>
                <input name="bb_pass" type="password" id="bb_pass" class="regular-text" placeholder="<?php echo (get_option('bb_pass')) ? '********' : null; ?>">
                <p class="description">It is highly recommended that you create a seperate <strong>read only</strong> user for WP Pusher to use.</p>
            </td>
        </tr>
    </tbody>
</table>
<?php submit_button('Save Bitbucket credentials'); ?>
</form>

<hr>

<h3>GitLab</h3>

<form method="post" action="<?php echo admin_url(); ?>options.php">
    <?php settings_fields('pusher-gl-settings'); ?>
    <?php do_settings_sections('pusher-gl-settings'); ?>
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row">
                <label>GitLab base url</label>
            </th>
            <td>
                <input name="gl_base_url" type="text" id="gl_base_url" value="<?php echo esc_attr(get_option('gl_base_url')); ?>" class="regular-text">
                <p class="description">Defaults to 'https://gitlab.com'.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label>GitLab private token</label>
            </th>
            <td>
                <input name="gl_private_token" type="text" id="gl_private_token" class="regular-text" placeholder="<?php echo (get_option('gl_private_token')) ? '********' : null; ?>">
                <p class="description">Only neccessary if you have private repositories.</p>
                <p class="description">Find private token in <strong>Settings > Account</strong>.</p>
            </td>
        </tr>
        </tbody>
    </table>
    <?php submit_button('Save GitLab settings'); ?>
</form>
