<?php
namespace hrgruri\saori;

use hrgruri\saori\{ArticleInfo, Maker};
use hrgruri\saori\exception\GeneratorException;
use hrgruri\saori\generator\{
    IndexGenerator,
    UserPageGenerator,
    ArticleGenerator,
    TagPageGenerator,
    FeedGenerator
};
use cebe\markdown\GithubMarkdown;

class SiteGenerator
{
    private $root;
    private $url;
    private $path;
    private $config;
    private $theme_config;
    private $ut_config;
    private $article_list;
    private $twig;

    public function __construct(array $path, \stdClass $config, \stdClass $tc, \stdClass $ut)
    {
        $this->path         =   $path;
        $this->config       =   $config;
        $this->theme_config =   $tc;
        $this->ut_config    =   $ut;
        $this->article_list =   $this->getArticleList();
        $this->cacheArticle();
        $this->twig         = new \Twig_Environment(
            new \Twig_Loader_Filesystem("{$this->path['theme']}/twig")
        );
    }

    public function generate(string $url, string $to)
    {
        $this->url  = rtrim($url, '/');
        $this->root = rtrim($to, '/');
        $this->path['root'] = $this->root;
        $this->copyTheme();
        $env = new \hrgruri\saori\generator\Environment(
            $this->path,
            $this->getMaker(),
            $this->twig,
            $this->url,
            $this->article_list
        );
        $env->theme_config  = $this->theme_config;
        $env->tag_list      = $this->tag_list;
        IndexGenerator::generate($env, $this->config);
        ArticleGenerator::generate($env, $this->config);
        TagPageGenerator::generate($env, $this->config);
        FeedGenerator::generate($env, $this->config);
        UserPageGenerator::generate($env, $this->config);
    }

    /**
     * @return \hrgruri\saori\Maker
     */
    private function getMaker()
    {
        return new Maker(
            $this->config,
            $this->article_list,
            $this->path['contents'],
            $this->theme_config,
            $this->ut_config,
            $this->tag_list
        );
    }

    private function getArticleList()
    {
        $tags       = [];
        $articles   = [];
        $path = $this->path['article'];
        if (is_dir($path)) {
            foreach ($this->getSubDirectory($path) as $year) {
                foreach ($this->getSubDirectory("{$path}/{$year}") as $month) {
                    foreach ($this->getSubDirectory("{$path}/{$year}/{$month}") as $dir) {
                        $config = $this->loadArticleConfig("{$path}/{$year}/{$month}/{$dir}");
                        if (!is_null($config)) {
                            $articles[]   = new ArticleInfo(
                                $config->timestamp,
                                "{$path}/{$year}/{$month}/{$dir}",
                                $config->title,
                                $config->tag,
                                "/article/{$year}/{$month}/{$dir}"
                            );
                        }
                    }
                }
            }
        }
        usort($articles, function ($a, $b) {
            return $b->timestamp <=> $a->timestamp
                ?: strnatcmp($a->title, $b->title);
        });
        $i = 0;
        foreach ($articles as $article) {
            $article->newer_link = isset($articles[$i - 1]) ? $articles[$i - 1]->link : null;
            $article->older_link = isset($articles[$i + 1]) ? $articles[$i + 1]->link : null;
            $article->id = $i++;
            sort($article->tag, SORT_NATURAL);
            foreach($article->tag as $tag) {
                $tags[$tag][] = $article->id;
            }
        }
        ksort($tags, SORT_NATURAL);
        $this->tag_list = $tags;
        return $articles;
    }

    private function getSubDirectory(string $path)
    {
        $dirs = [];
        if ( is_dir($path) && ($dh = opendir($path)) ) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$path}/{$file}")) {
                    $dirs[] = $file;
                }
            }
            closedir($dh);
        }
        return $dirs;
    }

    /**
     * @param  string $dir article directory
     * @return null | \stdClass
     */
    private function loadArticleConfig(string $dir)
    {
        if (file_exists("{$dir}/article.md") && file_exists("{$dir}/config.json")) {
            $config = json_decode(file_get_contents("{$dir}/config.json"));
            if (!isset($config->title) || !is_string($config->title)) {
                $config = null;
            } elseif (!isset($config->timestamp) || !is_int($config->timestamp)) {
                $config = null;
            }
        } else {
            $config = null;
        }
        return $config;
    }

    /**
     * generate article cache
     * @return null
     */
    private function cacheArticle()
    {
        if (!is_dir($this->path['cache'])) {
            mkdir($this->path['cache'], 0700);
        }
        foreach ($this->article_list as $article) {
            if (!is_dir("{$this->path['cache']}{$article->link}")) {
                mkdir("{$this->path['cache']}{$article->link}", 0700, true);
            }
            file_put_contents(
                "{$this->path['cache']}{$article->link}/article.md",
                $this->rewriteImagePath("{$article->path}/article.md", $article->link)
            );
        }
    }

    /**
     * @param  string $file
     * @param  string $path
     * @return string
     */
    private function rewriteImagePath(string $file, string $path)
    {
        return preg_replace(
            '/\!\[.*\]\(([a-zA-Z0-9\-_\/]+\.[a-zA-Z]+)(\s+\"\w*\"|)\)/',
            '![]('. $path .'/${1}${2})',
            file_get_contents($file)
        );
    }

    private function copyTheme()
    {
        if (is_dir("{$this->path['theme']}/css")) {
            $this->copyDirectory("{$this->path['theme']}/css", "{$this->root}/css");
        }
        if (is_dir("{$this->path['theme']}/js")) {
            $this->copyDirectory("{$this->path['theme']}/js", "{$this->root}/js");
        }
        if (is_dir("{$this->path['theme']}/img")) {
            $this->copyDirectory("{$this->path['theme']}/img", "{$this->root}/img");
        }
    }

    private function copyDirectory(string $from, string $to)
    {
        if (!is_dir($to)) {
            mkdir($to, 0700, true);
        }
        if (is_dir($from) && ($dh = opendir($from))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$from}/{$file}")) {
                    $this->copyDirectory("{$from}/{$file}", "{$to}/{$file}");
                } else {
                    copy("{$from}/{$file}", "{$to}/{$file}");
                }
            }
            closedir($dh);
        }
    }
}
