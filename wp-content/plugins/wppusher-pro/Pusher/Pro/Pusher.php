<?php

namespace Pusher\Pro;

use Pusher\Pusher as PusherFree;

class Pusher extends PusherFree
{
    public function init()
    {
        add_action('init', array($this->dispatcher, 'dispatchWebhookRequest'));

        parent::init();
    }

    public function activate()
    {
        parent::activate();

        $this->tokens->addTokenOption();
    }

    public function registerSettings()
    {
        register_setting('pusher-settings', 'wppusher_token');
        add_filter('pre_update_option_wppusher_token', array($this->tokens, 'refreshTokenFilter'), 10, 2);

        parent::registerSettings();
    }
}
