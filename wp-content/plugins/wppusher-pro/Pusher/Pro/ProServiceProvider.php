<?php

namespace Pusher\Pro;

use Pusher\Pusher as Container;
use Pusher\ProviderInterface;
use Pusher\Pro\Services\TokenGenerator;

class ProServiceProvider implements ProviderInterface
{
    public function register(Container $pusher)
    {
        $pusher->tokens = function($pusher) { return new TokenGenerator; };
    }
}
