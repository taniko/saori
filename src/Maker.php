<?php
namespace Hrgruri\Saori;

use Hrgruri\Saori\Article;
use Hrgruri\Saori\Exception\GeneratorException;
use cebe\markdown\GithubMarkdown;

class Maker
{
    private $article_list;
    private $config;
    private $contents_path;
    private $ut_config;
    private $public;
    private $url;
    public  $theme_config;
    public  $noapp;
    public  $tag_list;

    public function __construct($config, $article_list, $path, $tc, $ut, $tag_list, $public, string $url)
    {
        $this->config           = $config;
        $this->article_list     = $article_list;
        $this->contents_path    = $path;
        $this->theme_config     = $tc;
        $this->ut_config        = $ut;
        $this->noapp            = $this->theme_config->noapp ?? 10;
        $this->tag_list         = $tag_list;
        $this->public           = $public;
        $this->url              = $url;
    }

    public function __get($name)
    {
        if ($name === 'articles') {
            return $this->article_list;
        } elseif (isset($this->$name)) {
            return $this->$name;
        } else {
            throw new \LogicException();
        }
    }

    public function getArticles()
    {
        return $this->article_list;
    }

    /**
     * get newest articles
     * @param  integer $num
     * @return array
     */
    public function getNewestArticle(int $num = 1) : array
    {
        $articles = [];
        $num    = $num > 0 ? $num : 1;
        $limit  = (count($this->article_list) < $num) ? count($this->article_list) : $num;
        $from   = strlen($this->contents_path);
        for ($i = 0; $i < $limit ; $i++) {
            $articles[] = $this->article_list[$i];
        }
        return $articles;
    }

    /**
     * get oldest article
     * @param  integer $num
     * @return array
     */
    public function getOldestArticle(int $num = 1) : array
    {
        $articles   = [];
        $num        = ($num > 0) ? $num : 1;
        $key        = count($this->article_list) -1;
        for ($i = 0; $i < $num && $key >= 0; $i++, $key--) {
            $articles[] = $this->article_list[$key];
        }
        return $articles;
    }

    /**
     * get newer Article
     * @param  Article $article
     * @param  integer $num
     * @return array
     */
    public function getNewerArticle(Article $article, int $num = 1) : array
    {
        $articles   =   [];
        $key        =   $article->getId() - 1;
        $num        =   ($num > 0) ? $num : 1;
        for ($i = 0; $i < $num && $key >= 0; $i++, $key--) {
            $articles[] = $this->article_list[$key];
        }
        return $articles;
    }

    /**
     * get older article
     * @param  Article $article
     * @param  integer $num
     * @return array
     */
    public function getOlderArticle(Article $article, int $num = 1) : array
    {
        $articles   =   [];
        $key        =   $article->getId() +1;
        $num        =   ($num > 0) ? $num : 1;
        $limit      =   count($this->article_list);
        for ($i = 0; $i < $num && $key < $limit; $i++, $key++) {
            $articles[] = $this->article_list[$key];
        }
        return $articles;
    }

    /**
     * @param   string  $path   path of markdown file
     * @param   bool    $flag   throw flag
     * @return  string HTML
     */
    private function getHtml(string $path, bool $flag = true)
    {
        if (file_exists($path)) {
            $result = (new GithubMarkdown)->parse(file_get_contents($path));
        } elseif ($flag) {
            throw new GeneratorException("not exists {$path}");
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * @param  int    $page  corrent page number
     * @param  int    $num number of articles par page
     * @return bool
     */
    public function existsNextArticlePage(int $page, int $num)
    {
        if ($page < 0 || $num <= 0) {
            return false;
        }
        return (count($this->article_list) - $page * $num) > 0;
    }

    /**
     * get tag list
     * @return array
     */
    public function getTagList() : array
    {
        return array_keys($this->tag_list);
    }


    /**
     * @param  string  $tag    tag name
     * @param  integer $num number of articles
     * @return array
     */
    public function getArticleByTag(string $tag, int $num = 1)
    {
        $num        = $num > 0 ? $num : 1;
        $articles   = [];
        if (isset($this->tag_list[$tag])) {
            $num = count($this->tag_list[$tag]) < $num ? count($this->tag_list[$tag]) : $num;
            for ($i = 0; $i < $num; $i++) {
                $articles[] = $this->article_list[$this->tag_list[$tag][$i]];
            }
        }
        return $articles;
    }

    /**
     * @param  string $filename
     * @param  bool   $flag     throw flag
     * @return string | null
     */
    public function requireHtml(string $filename, bool $flag = true)
    {
        return $this->getHtml("{$this->contents_path}/markdown/{$filename}", $flag);
    }

    /**
     * @param  string $filename
     * @param  bool   $flag     throw flag
     * @return string | null
     */
    public function require(string $filename, bool $flag = true)
    {
        return $this->requireHtml($filename, $flag);
    }

    /**
     * get blog config
     * @param  string $key
     * @return mixed
     */
    public function config(string $key)
    {
        return $this->config->{$key} ?? null;
    }

    /**
     * get user's theme config or theme config
     * @param  string $key
     * @return mixed
     */
    public function theme(string $key)
    {
        return $this->ut_config->{$this->config->theme}->{$key}
            ?? $this->theme_config->{$key}
            ?? null;
    }

    /**
     * get user's theme color or theme color
     * @param  string $key
     * @return mixed
     */
    public function color(string $key)
    {
        return $this->ut_config->{$this->config->theme}->color->{$key}
            ?? $this->theme_config->color->{$key}
            ?? null;
    }

    /**
     * @return boolean
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @return boolean
     */
    public function isLocal()
    {
        return !$this->isPublic();
    }
}
