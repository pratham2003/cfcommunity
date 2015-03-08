<?php

namespace Pusher\Pro\Handlers;

use Pusher\Commands\UpdatePlugin;
use Pusher\Commands\UpdateTheme;
use Pusher\Pro\Commands\UpdatePackageFromPayload as UpdatePackageFromPayloadCommand;
use Pusher\Pro\Git\BitbucketWebhook;
use Pusher\Pro\Git\GitHubWebhook;
use Pusher\Handlers\BaseHandler;
use Pusher\Pro\Git\GitLabWebhook;

class UpdatePackageFromPayload extends BaseHandler
{
    public function handle(UpdatePackageFromPayloadCommand $command)
    {
        // GitHub, Bitbucket or GitLab?
        if (isset($command->payload['repository']['html_url']) && strstr($command->payload['repository']['html_url'], 'github.com')) {
            $hook = new GitHubWebhook($command->payload);
        } else if (isset($command->payload['canon_url']) && strstr($command->payload['canon_url'], 'bitbucket.org')) {
            $hook = new BitbucketWebhook($command->payload);
        } else if (isset($command->payload['total_commits_count'])) {
            // It's probably GitLab then
            $hook = new GitLabWebhook($command->payload);
        } else {
            return;
        }

        $repository = $hook->getRepository();

        // Plugin or theme command?
        if ( ! is_null($package = $this->pusher->plugins->pusherPluginFromRepository($repository))) {
            $command = new UpdatePlugin(array(
                'file' => $package->file,
                'repository' => (string) $repository
            ));
        } else if ( ! is_null($package = $this->pusher->themes->pusherThemeFromRepository($repository))) {
            $command = new UpdateTheme(array(
                'stylesheet' => $package->stylesheet,
                'repository' => (string) $repository
            ));
        } else {
            return;
        }

        // Check if push to deploy is enabled before executing
        if ( ! $package->pushToDeploy)
            return;

        $this->pusher->dashboard->execute($command);
    }
}
