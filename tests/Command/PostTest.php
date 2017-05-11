<?php
namespace Test\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class PostTest extends \TestCase
{
    const NAME = 'post';

    private $app;
    private $command;
    private $tester;

    public function setUp()
    {
        parent::setUp();
        $this->app = $this->getApplication();
        $this->callMethod($this->app, 'registerCommands');
        $this->command = $this->app->find(self::NAME);
        $this->tester = new CommandTester($this->command);
    }

    public function testPostUntitledArticle()
    {
        $names = [];
        $names[] = date('Y/m/dHi');
        $result = $this->tester->execute(['command' => $this->command->getName()]);
        $names[] = date('Y/m/dHi');

        $flag = false;
        foreach ($names as $key => $value) {
            $flag = $flag || vfsStreamWrapper::getRoot()->hasChild("contents/article/{$value}/config.yml");
        }
        $this->assertTrue($flag, "not exists contents/article/{$value}/config.yml");
    }

    public function testPostTemporaryArticle()
    {
        $command = $this->app->find(DraftTest::NAME);
        $tester = new CommandTester($command);
        $result = $tester->execute(['command' => $command->getName()]);

        $names = [];
        $names[] = date('Y/m/dHi');
        $result = $this->tester->execute([
            'command' => $this->command->getName(),
            'title'   => 'temp'
        ]);
        $names[] = date('Y/m/dHi');

        $flag = false;
        foreach ($names as $key => $value) {
            $flag = $flag || vfsStreamWrapper::getRoot()->hasChild("contents/article/{$value}/config.yml");
        }
        $this->assertTrue($flag, "not exists contents/article/{$value}/config.yml");
    }

    public function testPostTittledArticle()
    {
        $title  = 'test_article';
        $names[] = date('Y/m/').$title;
        $result = $this->tester->execute([
            'command'   => $this->command->getName(),
            'title'     => $title
        ]);
        $names[] = date('Y/m/').$title;

        $flag = false;
        foreach ($names as $key => $value) {
            $flag = $flag || vfsStreamWrapper::getRoot()->hasChild("contents/article/{$value}/config.yml");
        }
        $this->assertTrue($flag, "not exists contents/article/{$value}/config.yml");
    }
}
