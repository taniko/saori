<?php

use Hrgruri\Saori\Console\InitCommand as Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class CommandTest extends TestCase
{
    private $instance;

    public function setUp()
    {
        parent::setUp();
        if (!isset($this->instance)) {
            $this->instance = new class($this->root) extends Command {};
        }
    }

    public function testGetBlogConfig()
    {
        $app = new Hrgruri\Saori\Application($this->root);
        $command = $app->find('init');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
        $result = $this->callMethod($this->instance, 'getBlogConfig');
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    public function testCatchExceptionGetBlogConfig()
    {
        $flag = false;
        try {
            $this->callMethod($this->instance, 'getBlogConfig');
        } catch (\Exception $e) {
            $flag = true;
        } finally {
            $this->assertTrue($flag, 'not catch Exception');
        }
    }
}
