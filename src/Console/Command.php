<?php
namespace Hrgruri\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Hrgruri\Saori\SiteGenerator;
use hrgruri\saori\exception\{
    GeneratorException,
    JsonException
};
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected $root;
    protected $paths;

    public function __construct(string $root)
    {
        parent::__construct();
        $this->root = rtrim($root, '/');
        $this->paths = [
            'local'     =>  "{$this->root}/local",
            'public'    =>  '',
            'contents'  =>  "{$this->root}/contents",
            'article'   =>  "{$this->root}/contents/article",
            'markdown'  =>  "{$this->root}/contents/markdown",
            'cache'     =>  "{$this->root}/cache"
        ];
    }

    protected function loadConfig()
    {
        // load site config
        $config = SiteGenerator::loadJson("{$this->paths['contents']}/config.json");
        $config->local = rtrim($config->local, '/');

        // load theme config
        try {
            $data = SiteGenerator::loadJson(__DIR__ . "/../theme/{$config->theme}/config.json");
        } catch (JsonException $e) {
            $data = new \stdClass;
        }
        $this->theme_config = $data;

        // load user's theme config
        try {
            $data = SiteGenerator::loadJson("{$this->paths['contents']}/theme.json");
        } catch (JsonException $e) {
            $data = new \stdClass;
        }
        $this->ut_config = $data;

        $this->paths['public']  =   "{$this->root}/{$config->id}.github.io";
        $this->paths['theme']   =   __DIR__."/../theme/{$config->theme}";
        $this->config           =   $config;
        $this->config->public   = "https://{$this->config->id}.github.io";

    }

    protected function clearDirectory(string $dir, bool $flag = true)
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

    /**
     * make directory
     * @param  string $path
     * @return boolean
     */
    protected function mkdir(string $path)
    {
        $result = false;
        if (!file_exists($path)) {
            $result = mkdir($path, 0700, true);
        }
        return $result;
    }
}
