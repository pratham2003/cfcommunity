<h2>Install New Theme</h2>

<form action="" method="POST">
    <input type="hidden" name="wppusher[action]" value="install-theme">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label>Theme repository</label>
                </th>
                <td>
                    <input name="wppusher[repository]" type="text" class="regular-text" value="<?php echo (isset($_POST['wppusher']['repository'])) ? $_POST['wppusher']['repository'] : ''; ?>">
                    <p class="description">Example: petersuhm/twentyfourteen</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>Repository branch</label>
                </th>
                <td>
                    <input name="wppusher[branch]" type="text" class="regular-text" placeholder="master" value="<?php echo (isset($_POST['wppusher']['branch'])) ? $_POST['wppusher']['branch'] : ''; ?>" <?php echo ($data['hasPro']) ? null : 'disabled'; ?>>
                    <p class="description">Defaults to <strong>master</strong> if left blank</p>
                    <?php if ( ! $data['hasPro']) { ?>
                        <p class="description"><a href="http://wppusher.com/pro">Upgrade to PRO</a> to get this feature</a></p>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>Repository host</label>
                </th>
                <td>
                    <select name="wppusher[type]">
                        <option value="gh" <?php if (isset($_POST['wppusher']['type']) && $_POST['wppusher']['type'] === 'gh') echo 'selected="selected" '; ?>>GitHub</option>
                        <option value="bb" <?php if (isset($_POST['wppusher']['type']) && $_POST['wppusher']['type'] === 'bb') echo 'selected="selected" '; ?>>Bitbucket</option>
                        <option value="gl" <?php if (isset($_POST['wppusher']['type']) && $_POST['wppusher']['type'] === 'gl') echo 'selected="selected" '; ?>>GitLab</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"></th>
                <td>
                    <label><input type="checkbox" name="wppusher[private]" <?php if (isset($_POST['wppusher']['private'])) echo 'checked'; ?>> Repository is private</label>
                </td>
            </tr>
            <tr>
                <th scope="row"></th>
                <td>
                    <label><input type="checkbox" name="wppusher[dry-run]" <?php if (isset($_POST['wppusher']['dry-run'])) echo 'checked'; ?>> Dry run</label>
                    <p class="description">For already installed themes</p>
                    <p class="description">Folder name <strong>must</strong> have the same name as repository</p>
                </td>
            </tr>
        </tbody>
    </table>
    <?php submit_button('Install theme'); ?>
</form>
