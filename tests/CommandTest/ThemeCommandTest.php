<?php

use Symfony\Component\Console\Tester\CommandTester;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class ThemeCommandTest extends TestCase
{
    const NAME = 'theme';

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function displayList()
    {
        $tester = $this->getTester(self::NAME);
        $result = $tester->execute(['command' => self::NAME]);
        $this->assertRegExp('/Theme list/', $tester->getDisplay());
    }

    /**
     * @test
     */
    public function displayConfig()
    {
        $tester = $this->getTester(self::NAME);
        $result = $tester->execute(['command' => self::NAME, 'name' =>'saori']);
        $this->assertRegExp('/saori\/config\.json/', $tester->getDisplay());
    }
}
