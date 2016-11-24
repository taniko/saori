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
    private $root;      // directory
    private $url;       // site url
    private $paths;
    private $config;        // blog config
    private $theme_config;  // theme config
    private $ut_config;     // user theme config
    private static $articles;
    private static $tag_list;
    private static $css_files;

    public function __construct(array $paths, \stdClass $config, \stdClass $tc, \stdClass $ut)
    {
        $this->paths        =   $paths;
        $this->config       =   $config;
        $this->theme_config =   $tc;
        $this->ut_config    =   $ut;
    }

    /**
     * generate site
     * @param  string public|local
     */
    public function generate(string $type)
    {
        $this->public   = ($type === 'public' ? true : false);
        $this->url      = $this->public ? "https://{$this->config->id}.github.io" : $this->config->local;
        $this->root     = $this->paths[$type];
        $this->paths['root'] = $this->root;

        if (!isset(self::$articles)) {
            ArticleGenerator::cacheArticle($this->paths);
            self::$articles = ArticleGenerator::getArticles($this->paths);
            self::$tag_list = TagPageGenerator::getTagList(self::$articles);
        }
        $env = $this->getEnvironment();
        $this->copyTheme();
        // copy user files
        if (is_dir("{$this->paths['contents']}/file")) {
            self::copyDirectory("{$this->paths['contents']}/file", $this->root);
        }

        IndexGenerator::generate($env);
        ArticleGenerator::generate($env);
        TagPageGenerator::generate($env);
        FeedGenerator::generate($env);
        UserPageGenerator::generate($env);
        ThemePageGenerator::generate($env);
    }

    /**
     * @return \Hrgruri\Saori\Maker
     */
    private function getMaker()
    {
        return new Maker(
            $this->config,
            self::$articles,
            $this->paths['contents'],
            $this->theme_config,
            $this->ut_config,
            self::$tag_list,
            $this->public,
            $this->url
        );
    }

    /**
     * get environment
     * @return \Hrgruri\Saori\Generator\Environment
     */
    private function getEnvironment()
    {
        return new \Hrgruri\Saori\Generator\Environment(
            $this->getMaker(),
            $this->getTwigEnv("{$this->paths['theme']}/twig"),
            $this->paths
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
        if (is_dir("{$this->paths['theme']}/css")) {
            $this->copyCSS("{$this->paths['theme']}/css", "{$this->root}/css");
        }
        if (is_dir("{$this->paths['theme']}/js")) {
            self::copyDirectory("{$this->paths['theme']}/js", "{$this->root}/js");
        }
        if (is_dir("{$this->paths['theme']}/img")) {
            self::copyDirectory("{$this->paths['theme']}/img", "{$this->root}/img");
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
     * @throws Hrgruri\Saori\Exception\JsonException
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
            new \Twig_SimpleFilter('stdClass_to_array', function (\stdClass $std){
                $result = [];
                foreach ($std as $key => $value) {
                    $result[$key] = $value;
                }
                return $result;
            })
        );
        return $twig;
    }

    /**
     * @param  string $path
     * @param  array  $exts extensions
     * @return array
     */
    public static function getFileList(string $path, array $exts = null) : array
    {
        $files = [];
        if (is_dir($path) && ($dh = opendir($path))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..' || $file === '.DS_Store') {
                    continue;
                } elseif (is_dir("{$path}/{$file}")) {
                    $files = array_merge($files, self::getFileList("{$path}/{$file}", $exts));
                } elseif (is_null($exts)) {
                    $files[] = realpath("{$path}/{$file}");
                } elseif (in_array(pathinfo("{$path}/{$file}", PATHINFO_EXTENSION), $exts)) {
                    $files[] = realpath("{$path}/{$file}");
                } else {
                    continue;
                }
            }
            closedir($dh);
        }
        return $files;
    }

    private function copyCSS(string $from, string $to)
    {
        if (!isset(self::$css_files)) {
            self::$css_files = [];
            self::$css_files['css'] = self::getFileList($from, ['css']);
            self::$css_files['twig'] = self::getFileList($from, ['twig']);
        }

        foreach (self::$css_files['css'] as $source) {
            $dest = $to . substr($source, strlen($from));
            if (!is_dir(dirname($dest))) {
                mkdir (dirname($dest, true));
            }
            copy($source, $dest);
        }

        $twig = self::getTwigEnv($from);
        $maker = $this->getMaker();
        foreach (self::$css_files['twig'] as $source) {
            $source = substr($source, strlen($from));
            $dir    = dirname($to . substr($source, strlen($from)));
            if (preg_match('/(.*)\/(\w*)\.css\.twig$/', $source, $m) != 1) {
                throw new LogicException("not matched css.twig");
            }
            if (!is_dir($dir)) {
                mkdir($dir, true);
            }
            file_put_contents("{$to}{$m[1]}/{$m[2]}.css", $twig->render($source, ['maker' => $maker]));
        }
    }
}
