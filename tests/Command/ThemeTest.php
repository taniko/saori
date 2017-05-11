<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class ThemeTest extends \TestCase
{
    const NAME = 'theme';

    public function testDisplayList()
    {
        $app = $this->getApplication();
        $this->callMethod($app, 'registerCommands');
        $command = $app->find(self::NAME);
        $tester = new CommandTester($command);
        $result = $tester->execute(['command' => self::NAME]);
        $this->assertRegExp('/saori/', $tester->getDisplay());
    }
}
