<?php
namespace hrgruri\saori;

use hrgruri\saori\{ArticleInfo, Maker, SiteGenerator};
use hrgruri\saori\exception\GeneratorException;
use cebe\markdown\GithubMarkdown;
use \FeedWriter\{Item, ATOM, Feed};

class Saori
{
    const SAORI_COMMANd =   ['init', 'post', 'make'];
    const CONFIG_LIST   =   ['id', 'local', 'title', 'author', 'theme', 'lang', 'link'];
    private $config;
    private $root;
    private $path;
    private $theme_config;

    public function __construct(string $root)
    {
        $this->root = rtrim($root, '/');
        $this->path = [
            'local'     =>  "{$this->root}/local",
            'public'    =>  '',
            'contents'  =>  "{$this->root}/contents",
            'article'   =>  "{$this->root}/contents/article",
            'markdown'  =>  "{$this->root}/contents/markdown",
            'cache'     =>  "{$this->root}/cache"
        ];
    }

    public function run(array $argv)
    {
        foreach($argv as $key => $val) {
            $argv[$key] = strtolower($val);
        }
        try {
            $command = strtolower($argv[1] ?? '');
            if (!in_array($command, self::SAORI_COMMANd)) {
                throw new \Exception('not found command');
            }
            unset($argv[0]);
            unset($argv[1]);
            $this->checkConfig();
            $this->loadConfig();
            $this->{$command}(array_values($argv));
        } catch (\Exception $e) {
            print "ERROR\n". ($e->getMessage() ?? '') ."\n";
        }

    }

    private function init(array $option)
    {
        $result = true;
        if (is_dir($this->path['local'])) {
            throw new \Exception("directory({$this->path['local']}) already exists");
        } elseif (is_dir($this->path['public'])) {
            throw new \Exception("directory({$this->path['public']}) already exists");
        } elseif (is_dir($this->path['article'])) {
            throw new \Exception("directory({$this->path['article']}) already exists");
        }
        mkdir($this->path['local'], 0700, true);
        mkdir($this->path['public'], 0700, true);
        mkdir($this->path['article'], 0700, true);
    }

    private function post(array $option)
    {
        $dir        = date('Y/m');
        $title      = $option[0] ?? date('dHi');
        $timestamp   = date('YmdHis');
        if (preg_match('/^[\w-_]+$/', $title) !== 1) {
            throw new \Exception('error: title');
        }
        $dir = "{$this->path['article']}/{$dir}/{$title}";
        if (is_dir($dir)) {
            throw new \Exception("this title({$title}) already exist");
        }
        mkdir($dir, 0700, true);
        touch("{$dir}/article.md");
        $tmp = [
            "title"     =>  (string)$title,
            "tag"       =>  [],
            "timestamp"  =>  time()
        ];
        file_put_contents(
            "{$dir}/config.json",
            json_encode(
                [
                    "title"     =>  (string)$title,
                    "tag"       =>  [],
                    "timestamp"  =>  time()
                ],
                JSON_PRETTY_PRINT
            )
        );
    }

    /**
     * generate static site
     * @param  array  $option
     */
    private function make(array $option)
    {
        $this->clearDirectory($this->path['local'], true);
        $this->clearDirectory($this->path['public'], true);
        $this->clearDirectory($this->path['cache']);
        try {
            $generator = new SiteGenerator(
                $this->path,
                $this->config,
                $this->theme_config
            );
            $generator->generate(
                $this->config->local,
                $this->path['local']
            );
            $generator->generate(
                "https://{$this->config->id}.github.io",
                $this->path['public']
            );
        } catch (GeneratorException $e) {
            print "GENERATOR EXCEPTION\n";
            $this->clearDirectory($this->path['local'], true);
            $this->clearDirectory($this->path['public'], true);
            $result = false;
        } catch (\Exception $e) {
            print $e->getMessage();
            $this->clearDirectory($this->path['local'], true);
            $this->clearDirectory($this->path['public'], true);
        } finally {
            /*  clear cache */
            $this->clearDirectory($this->path['cache']);
        }
    }

    private function checkConfig()
    {
        if (!file_exists("{$this->root}/config.json")) {
            throw new \Exception('config.json does not exist');
        } elseif (is_null($config = json_decode(file_get_contents("{$this->root}/config.json")))) {
            throw new \Exception('cannot open or decode config.json');
        }
        $flag = true;
        foreach (self::CONFIG_LIST as $key) {
            $flag = $flag && isset($config->{$key});
        }
        if ($flag !== true) {
            throw new \Exception("undefined value exists. please check config.json");
        } elseif (!($config->link instanceof \stdClass)) {
            throw new \Exception('link must be object');
        } elseif (!is_dir(__DIR__. "/theme/{$config->theme}")) {
            throw new \Exception('not found theme');
        }
        return $flag;
    }

    private function loadConfig()
    {
        $data   = json_decode(file_get_contents("{$this->root}/config.json"));
        $data->local = rtrim($data->local, '/');
        $this->path['public']   =   "{$this->root}/{$data->id}.github.io";
        $this->path['theme']    =   __DIR__."/theme/{$data->theme}";
        $this->config           =   $data;
        if (file_exists(__DIR__ . "/theme/{$data->theme}/config.json")) {
            $this->theme_config = json_decode(
                file_get_contents(__DIR__ ."/theme/{$data->theme}/config.json")
            );
        } else {
            $this->theme_config = new \stdClass;
        }
    }

    private function clearDirectory(string $dir, bool $flag = true)
    {
        if (!file_exists($dir)) {
            return;
        }
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif(($file === '.git' || $file === '.gitkeep') && $flag === true) {
                    continue;
                }
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->clearDirectory($path, false);
                }else{
                    unlink($path);
                }
            }
            closedir($dh);
        }
        if ($flag !== true) {
            rmdir($dir);
        }
    }
}
