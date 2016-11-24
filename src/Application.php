<?php
namespace Hrgruri\Saori;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct($path)
    {
        if (!preg_match('/^vfs:\/\//', $path) == 1) {
            $path = realpath($path);
        }
        parent::__construct();
        $this->add(new Console\InitCommand($path));
        $this->add(new Console\DraftCommand($path));
        $this->add(new Console\PostCommand($path));
        $this->add(new Console\BuildCommand($path));
        $this->add(new Console\PageCommand($path));
        $this->add(new Console\DeployCommand($path));
        $this->add(new Console\ThemeCommand($path));
    }
}
