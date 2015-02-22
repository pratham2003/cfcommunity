<h2>Manage WP Pusher Plugins</h2>

<?php if ( ! count($data['plugins']) > 0) { ?>

    <div class="updated">
        <p>
            No plugins yet. Install a new one <a href="?page=wppusher-plugins-create">here</a>.
        </p>
    </div>

<?php } ?>

<table class="form-table">
    <tbody>
        <tr>
        <?php foreach ($data['plugins'] as $plugin) { ?>
            <th scope="row">
                <?php echo $plugin->name; ?>
            </th>
            <form action="" method="POST">
                <input type="hidden" name="wppusher[action]" value="edit-plugin">
                <input type="hidden" name="wppusher[file]" value="<?php echo $plugin->file; ?>">
                <td>
                    <input class="regular-text" type="text" name="wppusher[repository]" value="<?php echo $plugin->repository; ?>">
                </td>
                <td>
                    <input placeholder="master" type="text" name="wppusher[branch]" value="<?php echo $plugin->repository->getBranch(); ?>" <?php echo ($data['hasPro']) ? null : 'disabled'; ?>>
                    <?php if ( ! $data['hasPro']) { ?>
                        <p class="description"><a href="http://wppusher.com/pro">Upgrade to PRO</a> to get this feature</a></p>
                    <?php } ?>
                </td>
                <td>
                    <label><input type="checkbox" name="wppusher[ptd]" <?php echo ($plugin->pushToDeploy) ? 'checked' : null; ?> <?php echo ($data['hasPro']) ? null : 'disabled'; ?>> Push to deploy</label>
                    <?php if ( ! $data['hasPro']) { ?>
                        <p class="description"><a href="http://wppusher.com/pro">Upgrade to PRO</a> to get this feature</a></p>
                    <?php } ?>
                </td>
                <td>
                    <input value="Save changes" type="submit" class="button button-primary">
                </td>
            </form>
            <td>
                <form action="" method="POST">
                    <input type="hidden" name="wppusher[action]" value="update-plugin">
                    <input type="hidden" name="wppusher[repository]" value="<?php echo $plugin->repository; ?>">
                    <input type="hidden" name="wppusher[file]" value="<?php echo $plugin->file; ?>">
                    <input type="submit" class="button" value="Update plugin">
                </form>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
