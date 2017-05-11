<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class DraftTest extends \TestCase
{
    const NAME = 'draft';

    public function testExecute()
    {
        $app = $this->getApplication();
        $this->callMethod($app, 'registerCommands');
        $command = $app->find(self::NAME);
        $tester = new CommandTester($command);

        // unset name. create temp draft
        $result = $tester->execute(['command' => $command->getName()]);
        $this->assertEquals(0, $result);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft/temp/config.yml'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft/temp/article.md'));
        $data = Yaml::parse(file_get_contents("{$this->root}/draft/temp/config.yml"));
        $this->assertEquals('temp', $data['title']);

        // set name
        $result = $tester->execute(['command' => $command->getName(), 'name' => 'test']);
        $this->assertEquals(0, $result);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft/test/config.yml'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft/test/article.md'));
        $data = Yaml::parse(file_get_contents("{$this->root}/draft/test/config.yml"));
        $this->assertEquals('test', $data['title']);
    }

    public function testExecuteFailed()
    {
        $app = $this->getApplication();
        $this->callMethod($app, 'registerCommands');
        $command = $app->find(self::NAME);
        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'name' => 'test']);
        $this->assertEquals(
            0,
            $tester->getStatusCode()
        );
        $tester->execute(['command' => $command->getName(), 'name' => 'test']);
        $this->assertEquals(
            1,
            $tester->getStatusCode()
        );
    }
}
