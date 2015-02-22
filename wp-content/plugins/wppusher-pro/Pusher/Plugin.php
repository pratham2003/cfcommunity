<?php

namespace Pusher;

use Pusher\Git\Repository;

class Plugin
{
    protected $file;
    protected $name;
    protected $pluginURI;
    protected $version;
    protected $description;
    protected $author;
    protected $authorURI;
    protected $textDomain;
    protected $domainPath;
    protected $network;
    protected $title;
    protected $authorName;
    protected $repository;
    protected $pusherStatus;
    protected $pushToDeploy;

    public static function fromWpArray($file, array $array)
    {
        $plugin = new static();

        $plugin->file = $file;
        $plugin->name = $array['Name'];
        $plugin->pluginURI = $array['PluginURI'];
        $plugin->version = $array['Version'];
        $plugin->description = $array['Description'];
        $plugin->author = $array['Author'];
        $plugin->authorURI = $array['AuthorURI'];
        $plugin->textDomain = $array['TextDomain'];
        $plugin->domainPath = $array['DomainPath'];
        $plugin->network = $array['Network'];
        $plugin->title = $array['Title'];
        $plugin->authorName = $array['AuthorName'];

        return $plugin;
    }

    public function setPusherStatus($pusherStatus)
    {
        $this->pusherStatus = $pusherStatus;
    }

    public function setPushToDeploy($pushToDeploy)
    {
        $this->pushToDeploy = $pushToDeploy;
    }

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function __get($name)
    {
        $method = "get" . ucfirst($name);

        if (method_exists($this, $method))
        {
            return $this->$method();
        }

        if (isset($this->$name))
        {
            return $this->$name;
        }
    }

    public function __toString()
    {
        return $this->file;
    }
}
