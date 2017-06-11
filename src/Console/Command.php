<?php
namespace Taniko\Saori\Console;

use Taniko\Saori\Config;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected $root;
    protected $paths;
    protected $config;

    /**
     * Command constructor.
     * @param Config $config
     */
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
}
