<?php

namespace Pusher\WordPress;

use Theme_Upgrader;
use Pusher\Git\Repository;
use Pusher\Theme;
use Pusher\Pusher;
use Pusher\WordPress\ThemeUpgraderSkin;

class ThemeUpgrader extends Theme_Upgrader
{
    public function __construct(Pusher $pusher, ThemeUpgraderSkin $skin)
    {
        $this->pusher = $pusher;
        parent::__construct($skin);
    }

    public function installTheme(Theme $theme)
    {
        add_filter('upgrader_source_selection', array($this, 'upgraderSourceSelectionFilter'), 10, 3);

        $this->theme = $theme;
        return parent::install($this->theme->repository->getZipUrl());
    }

    public function upgradeTheme(Theme $theme)
    {
        add_filter("pre_site_transient_update_themes", array($this, 'preSiteTransientUpdateThemesFilter'), 10, 3);
        add_filter('upgrader_source_selection', array($this, 'upgraderSourceSelectionFilter'), 10, 3);

        $this->theme = $theme;
        return parent::upgrade($this->theme->stylesheet);
    }

    public function upgraderSourceSelectionFilter($source, $remote_source, $upgrader)
    {
        $newSource = trailingslashit($remote_source) . trailingslashit($upgrader->theme->repository->getSlug());

        global $wp_filesystem;

        if ( ! $wp_filesystem->move($source, $newSource, true))
            return new WP_Error();

        return $newSource;
    }

    public function preSiteTransientUpdateThemesFilter($transient)
    {
        $options = array('package' => $this->theme->repository->getZipUrl());
        $transient->response[$this->theme->stylesheet] = $options;

        return $transient;
    }
}
