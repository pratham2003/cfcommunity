<?php

namespace Pusher\Handlers;

use Pusher\Commands\InstallTheme as InstallThemeCommand;
use Pusher\Git\BitbucketRepository;
use Pusher\Git\GitHubRepository;

class InstallTheme extends BaseHandler
{
    public function handle(InstallThemeCommand $command)
    {
        $repository = $this->pusher->repositoryFactory->build(
            $command->type,
            $command->repository
        );

        if ($command->private) $repository->makePrivate();

        if ($this->pusher->isPro())
            $repository->setBranch($command->branch);

        $theme = $this->pusher->themes->fromSlug($repository->getSlug());
        $theme->setRepository($repository);

        $upgrader = $this->pusher->themeUpgrader;

        $result = ($command->dryRun)
            ? true
            : $upgrader->installTheme($theme);

        if ($result !== true) return;

        $theme = $this->pusher->themes->fromSlug($repository->getSlug());
        $theme->setRepository($repository);

        $this->pusher->themes->store($theme);

        if (is_multisite()) {
            $activationLink = network_admin_url()
                . "themes.php?action=enable&theme="
                . urlencode($theme->stylesheet)
                . "&_wpnonce="
                . wp_create_nonce('enable-theme_' . $theme->stylesheet);
        } else {
            $activationLink = get_admin_url()
                . "themes.php?action=activate&stylesheet="
                . urlencode($theme->stylesheet)
                . "&_wpnonce="
                . wp_create_nonce('switch-theme_' . $theme->stylesheet);
        }

        do_action('wppusher_theme_was_installed', $theme->stylesheet);

        $this->pusher->dashboard->addMessage("Theme was successfully installed. Go ahead and <a href=\"{$activationLink}\">activate</a> it.");
    }
}
