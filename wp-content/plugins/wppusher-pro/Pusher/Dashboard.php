<?php

namespace Pusher;

use Exception;
use InvalidArgumentException;
use Pusher\Commands\EditPlugin;
use Pusher\Commands\EditTheme;
use Pusher\Commands\InstallPlugin;
use Pusher\Commands\InstallTheme;
use Pusher\Commands\UpdatePlugin;
use Pusher\Commands\UpdateTheme;
use Pusher\Pusher;
use WP_Error;

class Dashboard
{
    public $messages = array();

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    public function getIndex()
    {
        return $this->render('index');
    }

    public function getPlugins()
    {
        $data['plugins'] = $this->pusher->plugins->allPusherPlugins();

        return $this->render('plugins/index', $data);
    }

    public function postEditPlugin($request)
    {
        $command = new EditPlugin($request);
        $this->execute($command);
    }

    public function postUpdatePlugin($request)
    {
        $command = new UpdatePlugin($request);
        $this->execute($command);
    }

    public function getPluginsCreate()
    {
        return $this->render('plugins/create');
    }

    public function postInstallPlugin($request)
    {
        $command = new InstallPlugin($request);
        $this->execute($command);
    }

    public function getThemes()
    {
        $data['themes'] = $this->pusher->themes->allPusherThemes();

        return $this->render('themes/index', $data);
    }

    public function postEditTheme($request)
    {
        $command = new EditTheme($request);
        $this->execute($command);
    }

    public function postUpdateTheme($request)
    {
        $command = new UpdateTheme($request);
        $this->execute($command);
    }

    public function getThemesCreate()
    {
        return $this->render('themes/create');
    }

    public function postInstallTheme($request)
    {
        $command = new InstallTheme($request);
        $this->execute($command);
    }

    public function addMessage($message)
    {
        $this->messages[] = $message;
    }

    public function execute($command)
    {
        $handlerClass = str_replace('Commands', 'Handlers', get_class($command));

        if ( ! class_exists($handlerClass)) {
            throw new InvalidArgumentException("Handler {$handlerClass} doesn't exist.");
        }

        $handler = new $handlerClass($this->pusher);

        try {
            $handler->handle($command);
        } catch (Exception $e) {
            $this->messages[] = new WP_Error('wppusher_error', $e->getMessage());
        }
    }

    protected function render($view, $data = array())
    {
        if ( ! current_user_can('update_plugins') || ! current_user_can('update_themes') ) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $data['messages'] = $this->messages;
        $data['hasPro'] = $this->pusher->isPro();
        $data['name'] = $this->pusher->getName();

        return include __DIR__.'/../views/base.php';
    }
}
