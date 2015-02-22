<?php

namespace Pusher\WordPress;

use Plugin_Upgrader;
use Pusher\Git\Repository;
use Pusher\Plugin;
use Pusher\Pusher;
use Pusher\WordPress\PluginUpgraderSkin;

class PluginUpgrader extends Plugin_Upgrader
{
    public function __construct(Pusher $pusher, PluginUpgraderSkin $skin)
    {
        $this->pusher = $pusher;
        parent::__construct($skin);
    }

    public function installPlugin(Plugin $plugin)
    {
        add_filter('upgrader_source_selection', array($this, 'upgraderSourceSelectionFilter'), 10, 3);

        $this->plugin = $plugin;
        return parent::install($this->plugin->repository->getZipUrl());
    }

    public function upgradePlugin(Plugin $plugin)
    {
        $reActivatePlugin = is_plugin_active((string) $plugin);

        add_filter("pre_site_transient_update_plugins", array($this, 'preSiteTransientUpdatePluginsFilter'), 10, 3);
        add_filter('upgrader_source_selection', array($this, 'upgraderSourceSelectionFilter'), 10, 3);

        $this->plugin = $plugin;
        $result = parent::upgrade($this->plugin->file);

        if ($reActivatePlugin) {
            if ( ! is_plugin_active((string) $plugin))
                activate_plugin($plugin, null, $network_wide = is_multisite(), $silent = true);
        }

        return $result;
    }

    public function upgraderSourceSelectionFilter($source, $remote_source, $upgrader)
    {
        $newSource = trailingslashit($remote_source) . trailingslashit($upgrader->plugin->repository->getSlug());

        global $wp_filesystem;

        if ( ! $wp_filesystem->move($source, $newSource, true))
            return new WP_Error();

        return $newSource;
    }

    public function preSiteTransientUpdatePluginsFilter($transient)
    {
        $options = array('package' => $this->plugin->repository->getZipUrl());
        $transient->response[$this->plugin->file] = (object) $options;

        return $transient;
    }
}
