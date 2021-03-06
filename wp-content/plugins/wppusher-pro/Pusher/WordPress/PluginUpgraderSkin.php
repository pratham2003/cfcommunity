<?php

namespace Pusher\WordPress;

use Plugin_Upgrader_Skin;
use WP_Error;

class PluginUpgraderSkin extends Plugin_Upgrader_Skin
{
    protected $error;
    protected $feedback;
    protected $pusher;

    public function __construct($pusher)
    {
        parent::__construct();
        $this->pusher = $pusher;
    }

    public function after()
    {
        // WP doesn't sent all errors as actual error objects
        if ($this->error === 'up_to_date')
            $this->error = new WP_Error('wppusher_error', 'Package is up-to-date.');

        if ( ! is_null($this->error)) {
            $this->pusher->dashboard->addMessage($this->error);
        }
    }

    public function before()
    {
        // ...
    }

    public function error($error)
    {
        $this->error = $error;
    }

    public function header()
    {
        // ...
    }

    public function feedback($string)
    {
        $this->feedback[$string] = true;
    }

    public function footer()
    {
        // ...
    }
}
