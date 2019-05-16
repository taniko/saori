<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Taniko\Saori\Util;

/**
 * @property \Taniko\Saori\Application app
 * @property \Symfony\Component\Console\Command\Command command
 * @property CommandTester tester
 */
class BuildTest extends \TestCase
{
    const NAME = 'build';

    public function setUp()
    {
        parent::setUp();
        Util::copyDirectory("{$this->asset}/blog", $this->root);
        $this->app = $this->getApplication();
        $this->callMethod($this->app, 'registerCommands');
        $this->command = $this->app->find(self::NAME);
        $this->tester = new CommandTester($this->command);
    }

    public function testBuildLocal()
    {
        Util::clearDirectory("{$this->root}/local");
        $this->assertFalse(file_exists("{$this->root}/local/index.html"));
        $result = $this->tester->execute(['command' => $this->command->getName(), '--local' => true]);
        $this->assertEquals(0, $result);
        $this->assertTrue(file_exists("{$this->root}/local/index.html"));
        Util::clearDirectory("{$this->root}/local");
    }

    public function testSetBuildPath()
    {
        $local_path = "{$this->root}/_local";
        $public_path = "{$this->root}/_public";

        Util::clearDirectory($local_path);
        Util::clearDirectory($public_path);
        $this->assertFalse(file_exists("{$local_path}/index.html"));
        $this->assertFalse(file_exists("{$public_path}/index.html"));

        $app = $this->getApplication();
        $app->setLocalBuildPath($local_path);
        $app->setPublicBuildPath($public_path);

        $this->callMethod($app, 'registerCommands');
        $command = $app->find(self::NAME);
        $tester = new CommandTester($command);
        $result = $tester->execute(['command' => $command->getName()]);
        $this->assertEquals(0, $result);
        $this->assertTrue(file_exists("{$local_path}/index.html"));
        $this->assertTrue(file_exists("{$public_path}/index.html"));
        Util::clearDirectory($local_path);
        Util::clearDirectory($public_path);
    }
}
