<?php

namespace Pusher\Pro;

use Pusher\Dashboard;
use Pusher\Pro\Commands\UpdatePackageFromPayload;

class DashboardPro extends DashBoard
{
    public function postWebhookPayload($payload)
    {
        $command = new UpdatePackageFromPayload($payload);
        $this->execute($command);

        die();
    }
}
