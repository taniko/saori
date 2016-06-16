<?php
namespace hrgruri\saori;

use hrgruri\saori\Maker;
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
    private static $articles;
    private static $tag_list;
    private $twig;

    public function __construct(array $path, \stdClass $config, \stdClass $tc, \stdClass $ut)
    {
        $this->path         =   $path;
        $this->config       =   $config;
        $this->theme_config =   $tc;
        $this->ut_config    =   $ut;
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
        if (!isset(self::$articles)) {
            ArticleGenerator::cacheArticle($this->path);
            self::$articles = ArticleGenerator::getArticles($this->path);
            self::$tag_list = $this->getTagList(self::$articles);
            $flag = true;
        }
        $env = new \hrgruri\saori\generator\Environment(
            $this->path,
            $this->getMaker(),
            $this->twig,
            $this->url,
            self::$articles
        );
        $env->theme_config  = $this->theme_config;
        $env->tag_list      = self::$tag_list;
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
            self::$articles,
            $this->path['contents'],
            $this->theme_config,
            $this->ut_config,
            self::$tag_list
        );
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

    private function getTagList(array $articles) : array
    {
        $tags = [];
        foreach ($articles as $article) {
            foreach ($article->tags as $tag) {
                $tags[$tag][] = $article->getId();
            }
        }
        ksort($tags, SORT_NATURAL);
        return $tags;
    }
}
