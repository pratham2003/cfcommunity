<?php

namespace Pusher\Handlers;

use Exception;
use Pusher\Commands\InstallPlugin as InstallPluginCommand;
use Pusher\Git\BitbucketRepository;
use Pusher\Git\GitHubRepository;

class InstallPlugin extends BaseHandler
{
    public function handle(InstallPluginCommand $command)
    {
        $repository = $this->pusher->repositoryFactory->build(
            $command->type,
            $command->repository
        );

        if ($command->private) $repository->makePrivate();

        if ($this->pusher->isPro())
            $repository->setBranch($command->branch);

        $plugin = $this->pusher->plugins->fromSlug($repository->getSlug());
        $plugin->setRepository($repository);

        $upgrader = $this->pusher->pluginUpgrader;

        $result = ($command->dryRun)
            ? true
            : $upgrader->installPlugin($plugin);

        if ($result !== true) return;

        $plugin = $this->pusher->plugins->fromSlug($repository->getSlug());
        $plugin->setRepository($repository);

        $this->pusher->plugins->store($plugin);

        $baseAdminUrl = (is_multisite()) ? network_admin_url() : get_admin_url();
        $activationLink = $baseAdminUrl
            . "plugins.php?action=activate&plugin="
            . urlencode($plugin->file)
            . "&_wpnonce="
            . wp_create_nonce('activate-plugin_' . $plugin->file);

        do_action('wppusher_plugin_was_installed', $plugin->file);

        $this->pusher->dashboard->addMessage("Plugin was successfully installed. Go ahead and <a href=\"{$activationLink}\">activate</a> it.");
    }
}
