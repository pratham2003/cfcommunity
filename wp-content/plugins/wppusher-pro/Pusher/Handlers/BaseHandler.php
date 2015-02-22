<?php

namespace Pusher\Handlers;

use Pusher\Pusher;

class BaseHandler
{
    protected $pusher;

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }
}
