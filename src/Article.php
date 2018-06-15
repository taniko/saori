<?php
namespace Taniko\Saori;

use cebe\markdown\GithubMarkdown;
use Symfony\Component\Yaml\Yaml;

class Article
{
    private $source;
    private $path;
    private $url;
    private $id;
    private $cache;
    private $timestamp;
    private $title;
    private $newer_article;
    private $older_article;
    private $tags;
    private $allow_properties = [
        'id', 'timestamp', 'title', 'newer_article', 'older_article', 'tags', 'url', 'path'
    ];

    private function __construct(string $source, string $path, array $config)
    {
        $this->source       = $source;
        $this->path         = $path;
        $this->timestamp    = $config['timestamp'];
        $this->title        = $config['title'];
        $this->tags         = $config['tag'] ?? [];
    }

    /**
     * @param string $source
     * @return Article
     */
    public static function create(string $source): Article
    {
        $config_file = "{$source}/config.yml";
        preg_match('/(.*)\/([0-9]{4}\/[0-9]{2}\/\w+)$/', $source, $m);
        $config = Yaml::parse(file_get_contents($config_file));
        $instance = new Article($source, $m[2], $config);
        return $instance;
    }

    /**
     * if id is undefined, set id
     * @param int $id
     */
    public function setId(int $id)
    {
        if ($this->id === null) {
            $this->id = $id;
        }
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

    public function setUrl(string $url)
    {
        $this->url = "{$url}/article/{$this->path}";
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
     * @param string $dist
     * @return string
     * @throws \Exception
     */
    public function createCache(string $dist): string
    {
        $this->cache = "{$dist}/{$this->path}";
        Util::putContents(
            "{$this->cache}/article.html",
            (new GithubMarkdown)->parse(Util::rewriteImagePath("{$this->source}/article.md", "/article/{$this->path}"))
        );
        return $this->cache;
    }

    /**
     * @param string|null $host
     * @return string
     */
    public function url(string $host = null): string
    {
        return isset($host) ? "{$host}/article/{$this->path}" : "/article/{$this->path}";
    }
}
