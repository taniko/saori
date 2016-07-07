<?php
namespace hrgruri\saori;

use hrgruri\saori\SiteGenerator;
use hrgruri\saori\exception\{
    GeneratorException,
    JsonException
};

class Saori
{
    const SAORI_COMMAND =   ['init', 'post', 'make'];
    const CONFIG_LIST   =   ['id', 'local', 'title', 'author', 'theme', 'lang', 'link'];
    private $config;
    private $root;
    private $path;
    private $theme_config;
    private $ut_config;

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
            if (!in_array($command, self::SAORI_COMMAND)) {
                throw new \Exception("not found command {$command}");
            }
            unset($argv[0]);
            unset($argv[1]);
            $this->{$command}(array_values($argv));
        } catch (\Exception $e) {
            print "ERROR\n". ($e->getMessage() ?? '') ."\n";
        }

    }

    /**
     * initialization
     * @param  array  $option argv
     */
    private function init(array $option)
    {
        $result = true;
        if (is_dir($this->path['local'])) {
            throw new \Exception("directory({$this->path['local']}) already exists");
        } elseif (is_dir($this->path['public'])) {
            throw new \Exception("directory({$this->path['public']}) already exists");
        } elseif (is_dir($this->path['contents'])) {
            throw new \Exception("directory({$this->path['contents']}) already exists");
        }
        mkdir($this->path['contents'], 0700, true);
        file_put_contents(
            "{$this->path['contents']}/config.json",
            json_encode(
                [
                    'id'    =>  'username',
                    'local' =>  'http://localhost:8000',
                    'title' =>  'Example Blog',
                    'author'=>  'John Doe',
                    'theme' =>  'sample',
                    'lang'  =>  'en',
                    'link'  =>  [
                        'github'    =>  'https://github.com',
                        'twitter'   =>  'https://twitter.com'
                    ],
                    'feed'  =>  [
                        'type'      =>  'atom',
                        'number'    =>  50
                    ]
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /**
     * post article
     * @param  array  $option argv
     */
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
        try {
            $this->loadConfig();
            $this->clearDirectory($this->path['local'], true);
            $this->clearDirectory($this->path['public'], true);
            $this->clearDirectory($this->path['cache']);
            $generator = new SiteGenerator(
                $this->path,
                $this->config,
                $this->theme_config,
                $this->ut_config
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
            print "GENERATOR EXCEPTION\n{$e->getMessage()}\n";
            $this->clearDirectory($this->path['local'], true);
            $this->clearDirectory($this->path['public'], true);
        } catch (\Exception $e) {
            print $e->getMessage().PHP_EOL;
            $this->clearDirectory($this->path['local'], true);
            $this->clearDirectory($this->path['public'], true);
        } finally {
            /*  clear cache */
            $this->clearDirectory($this->path['cache']);
        }
    }

    private function loadConfig()
    {
        // load site config
        $config = SiteGenerator::loadJson("{$this->path['contents']}/config.json");
        $flag = true;
        foreach (self::CONFIG_LIST as $key) {
            $flag = $flag && isset($config->{$key});
        }
        if (!($flag && ($config->link instanceof \stdClass) && ($config->feed instanceof \stdClass))) {
            throw new JsonException("please check config.json\nlink and feed must be object");
        }
        $config->local = rtrim($config->local, '/');

        // load theme config
        try {
            $data = SiteGenerator::loadJson(__DIR__ . "/theme/{$config->theme}/config.json");
        } catch (JsonException $e) {
            $data = new \stdClass;
        }
        $this->theme_config = $data;

        // load user's theme config
        try {
            $data = SiteGenerator::loadJson("{$this->path['contents']}/theme.json");
        } catch (JsonException $e) {
            $data = new \stdClass;
        }
        $this->ut_config = $data;

        $this->path['public']   =   "{$this->root}/{$config->id}.github.io";
        $this->path['theme']    =   __DIR__."/theme/{$config->theme}";
        $this->config           =   $config;
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
                $path = "{$dir}/{$file}";
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
