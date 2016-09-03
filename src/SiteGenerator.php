<?php
namespace Hrgruri\Saori;

use Hrgruri\Saori\Maker;
use Hrgruri\Saori\Exception\{
    GeneratorException,
    JsonException
};
use Hrgruri\Saori\Generator\{
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
    private $twig;
    private static $articles;
    private static $tag_list;

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

    public function generate(string $url, string $to, bool $public)
    {
        $this->public       = $public;
        $this->url          = rtrim($url, '/');
        $this->root         = rtrim($to, '/');
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
     * @return \Hrgruri\Saori\Maker
     */
    private function getMaker()
    {
        return new Maker(
            $this->config,
            self::$articles,
            $this->path['contents'],
            $this->theme_config,
            $this->ut_config,
            self::$tag_list,
            $this->public
        );
    }

    /**
     * get environment
     * @return \Hrgruri\Saori\Generator\Environment
     */
    private function getEnvironment()
    {
        $env = new \Hrgruri\Saori\Generator\Environment(
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
            self::copyDirectory("{$this->path['theme']}/css", "{$this->root}/css");
        }
        if (is_dir("{$this->path['theme']}/js")) {
            self::copyDirectory("{$this->path['theme']}/js", "{$this->root}/js");
        }
        if (is_dir("{$this->path['theme']}/img")) {
            self::copyDirectory("{$this->path['theme']}/img", "{$this->root}/img");
        }
    }

    public static function copyDirectory(string $from, string $to)
    {
        if (!is_dir($to)) {
            mkdir($to, 0700, true);
        }
        if (is_dir($from) && ($dh = opendir($from))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$from}/{$file}")) {
                    self::copyDirectory("{$from}/{$file}", "{$to}/{$file}");
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
