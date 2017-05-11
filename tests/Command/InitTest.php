<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class InitTest extends \TestCase
{
    const NAME = 'init';

    public function testExecute()
    {
        $app = $this->getApplication();
        $this->callMethod($app, 'registerCommands');
        $command = $app->find(self::NAME);
        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName()]);
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('config/env.yml'));
        $data = Yaml::parse(file_get_contents("{$this->root}/config/env.yml"));
    }

    public function testExecuteException()
    {
        $app = $this->getApplication();
        $this->callMethod($app, 'registerCommands');
        $command = $app->find(self::NAME);
        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName()]);
        $this->assertEquals(0, $tester->getStatusCode());
        $tester->execute(['command' => $command->getName()]);
        $this->assertEquals(1, $tester->getStatusCode());
    }
}
