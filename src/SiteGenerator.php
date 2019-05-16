<?php
namespace Taniko\Saori;

use Illuminate\Support\Collection;
use Taniko\Saori\Generator\{
    IndexGenerator,
    UserPageGenerator,
    ArticleGenerator,
    TagPageGenerator,
    FeedGenerator,
    ThemePageGenerator
};

class SiteGenerator
{
    private $url;
    private $root;
    private $public;
    private $config;        // blog config
    private $articles;
    private $tag_list;
    private $css_files;
    private $theme_dir;

    public function __construct(Config $config, Collection $articles)
    {
        $this->config    = $config;
        $this->articles  = $articles;
        $this->tag_list  = TagPageGenerator::getTagList($articles);
        $this->theme_dir = $this->config->themes[$this->config->env['theme']];
        $this->css_files = $this->getCssFiles("{$this->theme_dir}/css");
    }

    /**
     * generate site
     * @param  string $type 'public' or 'local'
     */
    public function generate(string $type)
    {
        $this->public   = $type === 'public';
        $this->url      = $this->public ? $this->config->env['public'] : $this->config->env['local'];
        $this->url      = rtrim($this->url, '/');
        $this->root     = $this->buildPath($this->config, $this->public);
        $this->copyTheme($this->theme_dir, $this->root);
        // copy user files
        if (is_dir("{$this->config->paths['contents']}/file")) {
            Util::copyDirectory("{$this->config->paths['contents']}/file", $this->root);
        }

        // override articles url
        $url = $this->url;
        $this->articles = $this->articles->map(function ($article) use ($url) {
            $article->setUrl($url);
            return $article;
        });

        $env = $this->getEnvironment();
        IndexGenerator::generate($env);
        ArticleGenerator::generate($env);
        FeedGenerator::generate($env);
        TagPageGenerator::generate($env);
        UserPageGenerator::generate($env);
        ThemePageGenerator::generate($env);
    }

    private function getCssFiles(string $source) : array
    {
        return [
            'css'  => Util::getFileList($source, ['css']),
            'twig' => Util::getFileList($source, ['twig'])
        ];
    }

    private function copyTheme(string $source, string $dist)
    {
        if (is_dir("{$source}/css")) {
            $this->copyCSS("{$source}/css", "{$dist}/css");
        }
        if (is_dir("{$source}/js")) {
            Util::copyDirectory("{$source}/js", "{$dist}/js");
        }
        if (is_dir("{$source}/img")) {
            Utill::copyDirectory("{$source}/img", "{$dist}/img");
        }
    }

    /**
     * @return \Taniko\Saori\Maker
     */
    private function getMaker()
    {
        return new Maker(
            $this->config,
            $this->articles,
            $this->tag_list,
            $this->public,
            $this->url
        );
    }

    /**
     * get environment
     * @return \Taniko\Saori\Generator\Environment
     */
    private function getEnvironment()
    {
        $paths         = $this->config->paths;
        $paths['root'] = $this->root;
        return new \Taniko\Saori\Generator\Environment(
            $this->getMaker(),
            $this->getTwigEnv("{$this->theme_dir}/twig"),
            $paths
        );
    }

    private function getSubDirectory(string $path)
    {
        $dirs = [];
        if (is_dir($path) && ($dh = opendir($path))) {
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

    private function getTwigEnv(string $path) : \Twig_Environment
    {
        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($path)
        );
        return $this->addTwigFilter($twig);
    }

    private function addTwigFilter(\Twig_Environment $twig) : \Twig_Environment
    {
        $twig->addFilter(
            new \Twig_SimpleFilter('stdClass_to_array', function (\stdClass $std) {
                $result = [];
                foreach ($std as $key => $value) {
                    $result[$key] = $value;
                }
                return $result;
            })
        );
        return $twig;
    }

    private function copyCSS(string $from, string $to)
    {
        foreach ($this->css_files['css'] as $source) {
            $dest = $to . substr($source, strlen($from));
            Util::copyFile($source, $dest);
        }

        $twig = $this->getTwigEnv($from);
        $maker = $this->getMaker();
        foreach ($this->css_files['twig'] as $source) {
            $source = substr($source, strlen($from));
            $dir    = dirname($to . substr($source, strlen($from)));
            if (preg_match('/(.*)\/(\w*)\.css\.twig$/', $source, $m) != 1) {
                throw new \LogicException("not matched css.twig");
            }
            Util::putContents("{$to}{$m[1]}/{$m[2]}.css", $twig->render($source, ['maker' => $maker]));
        }
    }

    /**
     * @param \Taniko\Saori\Config $config
     * @throws \Exception
     */
    public static function validate(Config $config)
    {
        $required = ['title', 'author', 'public', 'local', 'theme', 'lang'];
        foreach ($required as $value) {
            if (!isset($config->env[$value])) {
                throw new \Exception("{$value} is not setted in config/env.yml");
            }
        }
    }

    /**
     * generate path for build a site
     * @param Config $config
     * @param bool $public_build
     * @return string
     */
    private function buildPath(Config $config, bool $public_build): string
    {
        return $public_build
            ? $config->publicPath() ??  "{$config->root}/public"
            : $config->localPath()  ??  "{$config->root}/local";
    }
}
