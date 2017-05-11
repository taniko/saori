<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use Taniko\Saori\Util;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class BuildTest extends \TestCase
{
    const NAME = 'build';

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
}
