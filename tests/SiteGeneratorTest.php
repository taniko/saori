<?php

use Hrgruri\Saori\SiteGenerator;
use Symfony\Component\Console\Tester\CommandTester;

class SiteGeneratorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $app = new Hrgruri\Saori\Application($this->root);
        $command = $app->find('init');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }

    public function testCopyDirectory()
    {
        mkdir($this->file('contents/file/slide'), 0700, true);
        touch($this->file('contents/file/slide/app.index'));
        SiteGenerator::copyDirectory($this->file('contents/file'), $this->file('local'));
        $this->assertTrue(file_exists($this->file('local/slide/app.index')));
    }
}
