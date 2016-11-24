<?php
namespace Hrgruri\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;
use Illuminate\Validation\Factory;
use Hrgruri\Saori\SiteGenerator;
use hrgruri\saori\exception\{
    GeneratorException,
    JsonException
};
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected $root;
    protected $paths;
    protected $config;

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

    /**
     * load configuration file
     * @throws \Exception if failed loading configuration file
     */
    protected function loadConfig()
    {
        // load site config
        $config = $this->getBlogConfig();;
        $config->local = rtrim($config->local, '/');

        // load theme config
        try {
            $data = SiteGenerator::loadJson(__DIR__ . "/../theme/{$config->theme}/theme.json");
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

    /**
     * get blog configuration file
     * @throws \Exception if failed loading configuration file
     * @return \stdClass
     */
    protected function getBlogConfig()
    {
        try {
            $result = SiteGenerator::loadJson("{$this->paths['contents']}/config.json");
            $validator = $this->getFactory()->make((array)$result, [
                'id'    => 'required|string',
                'local' => 'required|string',
                'title' => 'required|string',
                'author'=> 'required|string',
                'theme' => 'required|string',
                'lang'  => 'required|string',
                'link'  => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $key    = $errors->keys()[0];
                throw new \Exception("{$key}: {$errors->get($key)[0]}");
            } elseif (! $result->link instanceof \stdClass) {
                throw new \Exception('link: must \stdClass');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    /**
     * @return Illuminate\Validation\Factory
     */
    private function getFactory()
    {
        return new Factory(new Translator('ja'));
    }

    /**
     * update paths
     * @param  array  $paths
     * @param  string $id    GitHub ID
     * @param  string $theme theme name
     * @return array
     */
     protected function updatePaths(array $paths, string $id, string $theme) : array
    {
        $paths['public']  =   "{$this->root}/{$id}.github.io";
        $paths['theme']   =   realpath(__DIR__."/../theme/{$theme}");
        return $paths;
    }
}
