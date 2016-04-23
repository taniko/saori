<?php
namespace Hrgruri\Saori;

class ArticleInfo
{
    public $id;
    public $timestamp;
    public $path;
    public $title;
    public $tag;
    public $link;
    public $newer_link;
    public $older_link;

    public function __construct($timestamp, $path, $title, array $tag, $link)
    {
        $this->timestamp = $timestamp;
        $this->path      =   $path;
        $this->title     =   $title;
        $this->tag       =   $tag;
        $this->link      =   $link;
    }
}
