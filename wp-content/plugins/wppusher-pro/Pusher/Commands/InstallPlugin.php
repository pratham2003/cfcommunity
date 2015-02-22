<?php

namespace Pusher\Commands;

class InstallPlugin
{
    public $repository;
    public $branch;
    public $type;
    public $private;
    public $dryRun;

    public function __construct($input)
    {
        $this->repository = $input['repository'];
        $this->branch = (isset($input['branch'])) ? $input['branch'] : '';
        $this->type = $input['type'];
        $this->private = (isset($input['private'])) ? '1' : '0';
        $this->dryRun = (isset($input['dry-run'])) ? '1' : '0';
    }
}
