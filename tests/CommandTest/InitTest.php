<?php

use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class InitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testExecute()
    {
        $app = new Hrgruri\Saori\Application($this->root);
        $command = $app->find('init');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $data = json_decode(file_get_contents("{$this->root}/contents/config.json"));
        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertRegExp('/^done$/i', $commandTester->getDisplay());
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('contents'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('contents/config.json'));
        $this->assertEquals('username', $data->id);
        $this->assertInstanceOf(\stdClass::class, $data->link);
    }

    public function testExecuteException()
    {
        $app = new Hrgruri\Saori\Application($this->root);
        $command = $app->find('init');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $this->assertEquals(0, $commandTester->getStatusCode());
        $commandTester->execute(['command' => $command->getName()]);
        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}
