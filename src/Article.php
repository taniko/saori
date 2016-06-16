<?php
namespace hrgruri\saori;

use cebe\markdown\GithubMarkdown;

class Article
{
    private $id;
    private $cache;
    private $timestamp;
    public $title;
    public $link;
    public $newer_link;
    public $older_link;
    public $tags;

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
    public function striptags(int $length = null){
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
     * @return array
     */
    public function getTags()
    {
        ksort($this->tags);
        return $this->tags;
    }

    public function html()
    {
        return  (new GithubMarkdown)->parse(file_get_contents("{$this->cache}/article.md"));
    }
}
