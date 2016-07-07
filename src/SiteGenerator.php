<?php
namespace hrgruri\saori;

use hrgruri\saori\Maker;
use hrgruri\saori\exception\{
    GeneratorException,
    JsonException
};
use hrgruri\saori\generator\{
    IndexGenerator,
    UserPageGenerator,
    ArticleGenerator,
    TagPageGenerator,
    FeedGenerator,
    ThemePageGenerator
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
        $this->addTwigFilter();
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
            self::$tag_list = TagPageGenerator::getTagList(self::$articles);
        }
        $env = $this->getEnvironment();
        IndexGenerator::generate($env, $this->config);
        ArticleGenerator::generate($env, $this->config);
        TagPageGenerator::generate($env, $this->config);
        FeedGenerator::generate($env, $this->config);
        UserPageGenerator::generate($env, $this->config);
        ThemePageGenerator::generate($env, $this->config);
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

    /**
     * get environment
     * @return \hrgruri\saori\generator\Environment
     */
    private function getEnvironment()
    {
        $env = new \hrgruri\saori\generator\Environment(
            $this->getMaker(),
            $this->twig
        );
        $env->paths         =   $this->path;
        $env->url           =   $this->url;
        $env->articles      =   self::$articles;
        $env->theme_config  =   $this->theme_config;
        $env->tag_list      =   self::$tag_list;
        return $env;
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

    /**
     * @param  string   $file filename
     * @return mixed
     */
    public static function loadJson(string $file)
    {
        if (!file_exists($file)) {
            throw new JsonException("{$file} is not exits");
        } elseif (is_null($data = json_decode(file_get_contents($file)))){
            throw new JsonException("{$file} is broken");
        }
        return $data;
    }

    private function addTwigFilter()
    {
        $this->twig->addFilter(
            new \Twig_SimpleFilter('stdClass_to_array', function (\stdClass $std){
                $result = [];
                foreach ($std as $key => $value) {
                    $result[$key] = $value;
                }
                return $result;
            })
        );
    }
}
