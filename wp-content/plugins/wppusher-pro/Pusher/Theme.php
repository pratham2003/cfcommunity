<?php

namespace Pusher;

use Pusher\Git\Repository;
use WP_Theme;

class Theme
{
    protected $stylesheet;
    protected $name;
    protected $themeURI;
    protected $description;
    protected $author;
    protected $authorURI;
    protected $version;
    protected $template;
    protected $status;
    protected $tags;
    protected $textDomain;
    protected $domainPath;
    protected $pusherStatus;
    protected $pushToDeploy;

    public static function fromWpThemeObject(WP_Theme $object)
    {
        $theme = new static();

        $theme->stylesheet = $object->get_stylesheet();
        $theme->name = $object['Name'];
        $theme->themeURI = $object['PluginURI'];
        $theme->description = $object['Description'];
        $theme->author = $object['Author'];
        $theme->authorURI = $object['AuthorURI'];
        $theme->version = $object['Version'];
        $theme->template = $object['Template'];
        $theme->status = $object['status'];
        $theme->tags = $object['tags'];
        $theme->textDomain = $object['TextDomain'];
        $theme->domainPath = $object['DomainPath'];

        return $theme;
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
        return $this->stylesheet;
    }
}
