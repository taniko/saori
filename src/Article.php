<?php
namespace Taniko\Saori;

use cebe\markdown\GithubMarkdown;
use Taniko\Saori\Util;

class Article
{
    private $url;
    private $id;
    private $cache;
    private $timestamp;
    private $title;
    private $link;
    private $newer_article;
    private $older_article;
    private $tags;
    private $allow_properties = [
        'id', 'timestamp', 'title', 'link', 'newer_article', 'older_article', 'tags', 'url'
    ];

    public function __construct(int $id, array $data, $url)
    {
        $this->id           = $id;
        $this->timestamp    = $data['config']['timestamp'];
        $this->title        = $data['config']['title'];
        $this->tags         = $data['config']['tag'] ?? [];
        $this->link         = $data['link'];
        $this->url          = "{$url}/article{$this->link}";
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

    public function setOlderArticle(Article $article)
    {
        if (!isset($this->older_article)) {
            $this->older_article = $article;
        }
    }

    public function setNewerArticle(Article $article)
    {
        if (!isset($this->newer_article)) {
            $this->newer_article = $article;
        }
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

    public function date(string $format = 'F j, Y')
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

    /**
     * cacheing article html
     */
    public function cache($source, $dist)
    {
        $this->cache = "{$dist}{$this->link}";
        $file = "{$source}{$this->link}/article.md";
        $dist = "{$dist}{$this->link}/article.html";
        Util::putContents(
            $dist,
            (new GithubMarkdown)->parse(Util::rewriteImagePath($file, "/article{$this->link}"))
        );
    }
}
