<h2>Manage WP Pusher Themes</h2>

<?php if ( ! count($data['themes']) > 0) { ?>

    <div class="updated">
        <p>
            No themes yet. Install a new one <a href="?page=wppusher-themes-create">here</a>.
        </p>
    </div>

<?php } ?>

<table class="form-table">
    <tbody>
        <tr>
        <?php foreach ($data['themes'] as $theme) { ?>
            <th scope="row">
                <?php echo $theme->name; ?>
            </th>
            <form action="" method="POST">
                <input type="hidden" name="wppusher[action]" value="edit-theme">
                <input type="hidden" name="wppusher[stylesheet]" value="<?php echo $theme->stylesheet; ?>">
                <td>
                    <input class="regular-text" type="text" name="wppusher[repository]" value="<?php echo $theme->repository; ?>">
                </td>
                <td>
                    <input placeholder="master" type="text" name="wppusher[branch]" value="<?php echo $theme->repository->getBranch(); ?>" <?php echo ($data['hasPro']) ? null : 'disabled'; ?>>
                    <?php if ( ! $data['hasPro']) { ?>
                        <p class="description"><a href="http://wppusher.com/pro">Upgrade to PRO</a> to get this feature</a></p>
                    <?php } ?>
                </td>
                <td>
                    <label><input type="checkbox" name="wppusher[ptd]" <?php echo ($theme->pushToDeploy) ? 'checked' : null; ?> <?php echo ($data['hasPro']) ? null : 'disabled'; ?>> Push to deploy</label>
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
                    <input type="hidden" name="wppusher[action]" value="update-theme">
                    <input type="hidden" name="wppusher[repository]" value="<?php echo $theme->repository; ?>">
                    <input type="hidden" name="wppusher[stylesheet]" value="<?php echo $theme->stylesheet; ?>">
                    <input type="submit" class="button" value="Update theme">
                </form>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
