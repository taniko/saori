<?php

use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class DraftTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testExecute()
    {
        $app = new Hrgruri\Saori\Application($this->root);
        $command = $app->find('draft');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'name' => 'test']);
        $data = json_decode(file_get_contents("{$this->root}/draft/test/config.json"));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft/test/config.json'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('draft/test/article.md'));
        $this->assertEquals('test', $data->title);
        $this->assertInternalType('array', $data->tag);
        $this->assertEquals(0, count($data->tag));
    }

    public function testExecuteException()
    {
        $app = new Hrgruri\Saori\Application($this->root);
        $command = $app->find('draft');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'name' => 'test']);
        $this->assertNotRegExp('/already\ exists$/i', $commandTester->getDisplay());
        $commandTester->execute(['command' => $command->getName(), 'name' => 'test']);
        $this->assertRegExp('/already\ exists$/i', $commandTester->getDisplay());

    }
}
