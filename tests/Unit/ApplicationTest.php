<?php
namespace Test\Unit;

class ApplicationTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testCollectThemePaths()
    {
        $app = $this->getApplication();
        $themes = $app->collectThemePaths();
        $this->assertTrue(array_key_exists('saori', $themes));
    }

    public function testAddThemes()
    {
        $app = $this->getApplication();
        $app->addTheme('append-theme', realpath(__DIR__.'/../asset/theme/append-theme'));
        $themes = $app->getThemes();
        $this->assertTrue(array_key_exists('append-theme', $themes));
    }
}
