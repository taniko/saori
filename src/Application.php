<?php
namespace Hrgruri\Saori;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct($path)
    {
        parent::__construct();
        $this->add(new Console\InitCommand($path));
        $this->add(new Console\DraftCommand($path));
        $this->add(new Console\PostCommand($path));
        $this->add(new Console\BuildCommand($path));
    }
}
