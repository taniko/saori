<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class PageTest extends \TestCase
{
    const NAME = 'page';

    public function testExecute()
    {
        $app = $this->getApplication();
        $this->callMethod($app, 'registerCommands');
        $command = $app->find(self::NAME);
        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'path' => 'about']);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('contents/page/about.md'));
    }
}
