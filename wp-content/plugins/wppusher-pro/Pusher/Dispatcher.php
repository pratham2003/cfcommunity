<?php

namespace Pusher;

class Dispatcher
{
    protected $pusher;

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    public function dispatchPostRequests()
    {
        if (isset($_POST['wppusher'])) {

            if ( ! current_user_can('update_plugins') || ! current_user_can('update_themes') ) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            $request = $_POST['wppusher'];

            switch ($request['action']) {
                case 'install-plugin':
                    $this->pusher->dashboard->postInstallPlugin($request);
                    break;

                case 'install-theme':
                    $this->pusher->dashboard->postInstallTheme($request);
                    break;

                case 'edit-plugin':
                    $this->pusher->dashboard->postEditPlugin($request);
                    break;

                case 'edit-theme':
                    $this->pusher->dashboard->postEditTheme($request);
                    break;

                case 'update-plugin':
                    $this->pusher->dashboard->postUpdatePlugin($request);
                    break;

                case 'update-theme':
                    $this->pusher->dashboard->postUpdateTheme($request);
                    break;

                default:
                    break;
            }
        }
    }
}
