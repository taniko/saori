<?php
namespace Taniko\Saori;

use cebe\markdown\GithubMarkdown;
use Illuminate\Support\Collection;

class Maker
{
    private $config;
    private $articles;
    private $tag_list;
    private $public;
    private $url;
    private $theme_setting; // theme setting

    private $denies = ['config'];

    public function __construct(
        Config $config,
        Collection $articles,
        Collection $tag_list,
        bool $public,
        string $url
    ) {
        $this->config   = $config;
        $this->articles = $articles;
        $this->tag_list = $tag_list;
        $this->public   = $public;
        $this->url      = $url;
        $this->theme_setting = Util::getYamlContents($config->themes[$config->env['theme']].'/setting.yml');
    }

    public function __get($name)
    {
        if (in_array($name, $this->denies)) {
            throw new \InvalidArgumentException("{$name} is access deny");
        } elseif (isset($this->$name)) {
            return $this->$name;
        } else {
            throw new \InvalidArgumentException("{$name} is not exists");
        }
        return $this->{$name};
    }

    /**
     * check build type is public
     * @return bool if build type is public, return true
     */
    public function isPublic() : bool
    {
        return $this->public;
    }

    /**
     * check build type is local
     * @return bool if build type is local, return true
     */
    public function isLocal() : bool
    {
        return !$this->isPublic();
    }

    /**
     * site title
     * @return string
     */
    public function title()
    {
        return $this->env('title');
    }

    /**
     * get blog config
     * @param  string $key
     * @return mixed
     */
    public function env(string $key)
    {
        $result = null;
        $break  = false;
        $keys   = explode('.', $key);
        $env    = $this->config->env;
        foreach ($keys as $key) {
            if (isset($env[$key])) {
                $env = $env[$key];
            } else {
                $break = true;
                break;
            }
        }
        if (!$break) {
            $result = $env;
        }
        return $result;
    }

    /**
     * get user's theme config or theme config
     * @param  string $key
     * @param  boolean $override
     * @return mixed
     */
    public function theme(string $key, bool $override = true)
    {
        $result = null;
        $keys   = explode('.', $key);
        $theme  = $this->theme_setting;
        if ($override) {
            $break = false;
            $theme = $this->config->theme[$this->env('theme')];
            foreach ($keys as $key) {
                if (isset($theme[$key])) {
                    $theme = $theme[$key];
                } else {
                    $break = true;
                    break;
                }
            }
            if (!$break) {
                $result = $theme;
            }
        }
        if (!isset($result)) {
            $break = false;
            $theme  = $this->theme_setting;
            foreach ($keys as $key) {
                if (isset($theme[$key])) {
                    $theme = $theme[$key];
                } else {
                    $break = true;
                    break;
                }
            }
            if (!$break) {
                $result = $theme;
            }
        }
        return $result;
    }

    /**
     * get user's theme color or theme color
     * @param  string $key
     * @param  boolean $override
     * @return mixed
     */
    public function color(string $key, bool $override = true)
    {
        return $this->theme("color.{$key}", $override);
    }

    /**
     * get articles order by timestamp asc
     * @param  integer    $size number of articles
     * @return Collection      article collection
     */
    public function getNewestArticles(int $size = null) : Collection
    {
        $size = $size ?? $this->articles->count();
        return $this->articles->reverse()->values()->take($size);
    }

    /**
     * get articles order by timestamp asc
     * @param  integer    $size number of articles
     * @return Collection      article collection
     */
    public function getOldestArticles(int $size = null) : Collection
    {
        $size = $size ?? $this->articles->count();
        return $this->articles->values()->take($size);
    }

    /**
     * get newer articles
     * @param  Article    $article target article
     * @param  integer    $size    take size
     * @return Collection          article Collection
     */
    public function getNewerArticles(Article $article, int $size = null) : Collection
    {
        $size = $size ?? $this->articles->count();
        $timestamp = $article->timestamp;
        return $this->articles->filter(function ($article) use ($timestamp) {
            return $timestamp < $article->timestamp;
        })->values()->take($size);
    }

    /**
     * get older articles
     * @param  Article    $article target article
     * @param  integer    $size    take size
     * @return Collection          article Collection
     */
    public function getOlderArticles(Article $article, int $size = null) : Collection
    {
        $size = $size ?? $this->articles->count();
        $timestamp = $article->timestamp;
        return $this->articles->reverse()->filter(function ($article) use ($timestamp) {
            return $timestamp > $article->timestamp;
        })->values()->take($size);
    }

    /**
     * get tag list
     * @return Collection
     */
    public function getTagList() : Collection
    {
        return $this->tag_list->keys();
    }

    /**
     * get articles by tag. alias of getArticlesByTagDesc
     * @param  string     $tag  tag name
     * @param  integer    $size articles size
     * @return Collection       article collection
     */
    public function getArticlesByTag(string $tag, int $size = null) : Collection
    {
        return $this->getArticlesByTagDesc($tag, $size);
    }

    /**
     * get articles by tag
     * @param  string     $tag  tag name
     * @param  integer    $size articles size
     * @return Collection       article collection
     */
    public function getArticlesByTagDesc(string $tag, int $size = null) : Collection
    {
        $size = $size ?? $this->articles->count();
        $articles = $this->articles;
        return $this->tag_list->get($tag)->reverse()->map(function ($item) use ($articles) {
            return $articles->get($item);
        })->values()->take($size);
    }

    /**
     * get articles by tag asc
     * @param  string     $tag  tag name
     * @param  integer    $size articles size
     * @return Collection       article collection
     */
    public function getArticlesByTagAsc(string $tag, int $size = null) : Collection
    {
        $size = $size ?? $this->articles->count();
        $articles = $this->articles;
        return $this->tag_list->get($tag)->map(function ($item) use ($articles) {
            return $articles->get($item);
        })->values()->take($size);
    }

    /**
     * @param  string $filename markdown file name
     * @param  bool   $flag     throw flag
     * @throws \Exception
     * @return string|null
     */
    public function markdown(string $filename, bool $flag = true)
    {
        return $this->getHtml("{$this->config->paths['markdown']}/{$filename}", $flag);
    }

    /**
     * @param   string  $path   path of markdown file
     * @param   bool    $flag   throw flag
     * @throws  \Exception
     * @return  string HTML
     */
    private function getHtml(string $path, bool $flag = true)
    {
        if (file_exists($path)) {
            $result = (new GithubMarkdown)->parse(file_get_contents($path));
        } elseif ($flag) {
            throw new \Exception("not exists {$path}");
        } else {
            $result = null;
        }
        return $result;
    }

    public function existsNewerArticlePage(int $page)
    {
        return ($this->articles->count() - $page * $this->theme('size')) > 0;
    }

    public function getTagListLength(string $name) : int
    {
        return isset($this->tag_list[$name])
            ? $this->tag_list[$name]->count()
            : 0;
    }
}
