<?php
namespace Taniko\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;
use Illuminate\Validation\Factory;
use Taniko\Saori\SiteGenerator;
use Taniko\Saori\Config;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected $root;
    protected $paths;
    protected $config;

    public function __construct(Config $config)
    {
        parent::__construct();
        $this->config  = $config;
        $this->root  = rtrim($config->root, '/');
        $this->paths = [
            'local'     =>  "{$this->root}/local",
            'public'    =>  "{$this->root}/public",
            'contents'  =>  "{$this->root}/contents",
            'article'   =>  "{$this->root}/contents/article",
            'markdown'  =>  "{$this->root}/contents/markdown",
            'cache'     =>  "{$this->root}/cache"
        ];
    }
    
    //
    // /**
    //  * get blog configuration file
    //  * @throws \Exception if failed loading configuration file
    //  * @return \stdClass
    //  */
    // protected function getBlogConfig()
    // {
    //     try {
    //         $result = SiteGenerator::loadJson("{$this->paths['contents']}/config.json");
    //         $validator = $this->getFactory()->make((array)$result, [
    //             'id'    => 'required|string',
    //             'local' => 'required|string',
    //             'title' => 'required|string',
    //             'author'=> 'required|string',
    //             'theme' => 'required|string',
    //             'lang'  => 'required|string',
    //             'link'  => 'required',
    //         ]);
    //         if ($validator->fails()) {
    //             $errors = $validator->errors();
    //             $key    = $errors->keys()[0];
    //             throw new \Exception("{$key}: {$errors->get($key)[0]}");
    //         } elseif (! $result->link instanceof \stdClass) {
    //             throw new \Exception('link: must \stdClass');
    //         }
    //     } catch (\Exception $e) {
    //         throw new \Exception($e->getMessage());
    //     }
    //     return $result;
    // }
    //
    // /**
    //  * @return Illuminate\Validation\Factory
    //  */
    // private function getFactory()
    // {
    //     return new Factory(new Translator('ja'));
    // }
    //
}
