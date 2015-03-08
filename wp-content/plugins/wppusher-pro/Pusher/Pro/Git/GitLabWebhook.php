<?php

namespace Pusher\Pro\Git;

use Exception;
use Pusher\Git\GitLabRepository;

class GitLabWebhook implements Webhook
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function getRepository()
    {
        if ( ! isset($this->payload['repository']['url']))
            throw new Exception('Repository not found.');

        $url = $this->payload['repository']['url'];
        $fractions = explode(':', $url);
        $repo = rtrim(array_pop($fractions), '.git');

        return new GitLabRepository($repo);
    }
}
