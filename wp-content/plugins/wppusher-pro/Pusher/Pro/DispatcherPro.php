<?php

namespace Pusher\Pro;

use Pusher\Dispatcher;

class DispatcherPro extends Dispatcher
{
    public function dispatchWebhookRequest()
    {
        if ( ! isset($_GET['wppusher-hook']))
            return;

        if ( ! isset($_GET['token']) || $_GET['token'] !== get_option('wppusher_token')) {
            status_header(400);
            die();
        }

        if (isset($_POST['payload']))
            $payload = json_decode(stripslashes($_POST['payload']), true);
        else
            $payload = json_decode(file_get_contents('php://input'), true);

        if ( ! $payload) {
            status_header(400);
            die();
        }

        $this->pusher->dashboard->postWebhookPayload($payload);
    }
}