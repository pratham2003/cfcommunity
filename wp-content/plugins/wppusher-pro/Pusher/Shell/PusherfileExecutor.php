<?php

namespace Pusher\Shell;

use Exception;
use Pusher\Pusher;

class PusherfileExecutor
{
    const PUSHER_FILE_NAME = 'wppusher.php';

    protected $async = true;
    protected $commands;
    protected $pusher;
    protected $pwd;

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    public function executeInDirectory($dir)
    {
        $this->pwd = $dir;

        $file = $this->pwd . '/' . self::PUSHER_FILE_NAME;

        if ( ! file_exists($file))
            return null;

        $content = include $file;

        // File not array
        if ( ! is_array($content))
            throw new Exception('Your wppusher.php file should return an array.');

        // No commands ...
        if ( ! isset($content['commands']))
            return null;

        $this->commands = $content['commands'];

        $this->async = isset($content['async'])
            ? $content['async']
            : true; // Defaults to true

        $haltOnFail = isset($content['halt_on_fail'])
            ? $content['halt_on_fail']
            : true; // Defaults to true

        if ($haltOnFail)
            return $this->executeWithHaltOnFail();

        return $this->executeWithoutHaltOnFail();
    }

    protected function executeWithHaltOnFail()
    {
        $command = implode(' && ', $this->commands);

        return $this->executeCommand($command);
    }

    protected function executeWithoutHaltOnFail()
    {
        $command = implode('; ', $this->commands);

        return $this->executeCommand($command);
    }

    protected function executeCommand($command)
    {
        if ($this->async)
            $command = sprintf('(%s) > /dev/null 2>&1 &', $command);

        chdir($this->pwd);
        $output = shell_exec($command);

        if ($this->async)
            $this->pusher->dashboard->addMessage(self::PUSHER_FILE_NAME . ' running in asyncronous mode.');
        else
            $this->pusher->dashboard->addMessage(self::PUSHER_FILE_NAME . ' was run.');

        return $output;
    }
}
