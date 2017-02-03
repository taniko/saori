<?php
namespace Hrgruri\Saori;

use cebe\markdown\GithubMarkdown;

class Article
{
    private $id;
    private $cache;
    private $timestamp;
    private $title;
    private $link;
    private $newer_link;
    private $older_link;
    private $tags;
    private $allow_properties = [
        'id', 'timestamp', 'title', 'link', 'newer_link', 'older_link', 'tags'
    ];

    public function __construct(int $id, \stdClass $config, array $paths)
    {
        $this->id           = $id;
        $this->title        = $config->title;
        $this->tags         = $config->tag ?? [];
        $this->timestamp    = $config->timestamp;
        $this->cache        = $paths['cache'];
        $this->link         = $paths['link'];
        $this->newer_link   = $paths['newer'];
        $this->older_link   = $paths['older'];
    }

    public function __get($name)
    {
        if (in_array($name, $this->allow_properties)) {
            return $this->{$name};
        } elseif ($name === 'html') {
            return $this->html();
        } else {
            return null;
        }
    }

    public function __isset($name)
    {
        if (in_array($name, $this->allow_properties) || $name === 'html') {
            return true;
        } else {
            return null;
        }
    }

    /**
     * get article id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param  int $length
     * @return string
     */
    public function striptags(int $length = null)
    {
        if (is_int($length) && $length > 0) {
            $result = mb_substr(strip_tags($this->html()), 0, $length);
        } else {
            $result = strip_tags($this->html());
        }
        return $result;
    }

    public function getDate(string $format = 'F j, Y')
    {
        return date($format, $this->timestamp);
    }

    /**
     * get html
     * @return string
     */
    public function html() : string
    {
        return file_get_contents("{$this->cache}/article.html");
    }
}
