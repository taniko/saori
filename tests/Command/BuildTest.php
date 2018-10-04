<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Taniko\Saori\Util;

class BuildTest extends \TestCase
{
    const NAME = 'build';

    private $app;

    public function setUp()
    {
        parent::setUp();
        $this->app = $this->getApplication();
        $this->callMethod($this->app, 'registerCommands');
        $this->command = $this->app->find(self::NAME);
        $this->tester = new CommandTester($this->command);
        Util::copyDirectory("{$this->asset}/blog", $this->root);
    }

    public function testBuildLocal()
    {
        $result = $this->tester->execute(['command' => $this->command->getName(), '--local' => true]);
    }

    public function testSetLocalBuildPath()
    {
        $path = "{$this->root}/build/local";
        $this->app->setLocal($path);

        $this->assertFalse(is_dir("{$path}"));
        $this->tester->execute(['command' => $this->command->getName(), '--local' => true]);
        print_r(scandir("{$this->root}/local"));
        $this->assertTrue(is_dir("{$path}/article"));

    }
}
