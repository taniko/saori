<?php

use Hrgruri\Saori\Maker;
use Illuminate\Support\Collection;

class MakerTest extends TestCase
{
    private $maker;

    public function setUp()
    {
        parent::setUp();
        if (!isset($this->maker)) {
            $this->maker = new Maker(
                $this->getConfig(),
                Collection::make([]),
                "{$this->root}/contents",
                new \stdClass,
                new \stdClass,
                Collection::make([]),
                true,
                'http://localhost:8000'
            );
        }
    }

    public function testIsPublic()
    {
        $this->assertTrue($this->maker->isPublic());
    }

    public function testIsLocal()
    {
        $this->assertFalse($this->maker->isLocal());
    }

    public function testMagicMethodGet()
    {
        $this->assertEquals('http://localhost:8000', $this->maker->url);
    }

    public function testCatchLogicException()
    {
        $flag = false;
        try {
            $this->maker->undefined_property;
        } catch (\LogicException $e) {
            $flag = true;
        } finally {
            $this->assertTrue($flag, 'not catch LogicException');
        }
    }

    public function testOverrideTheme()
    {
        $theme = $this->getThemeConfig();
        $theme['theme']->color->main            = 'white';
        $theme['theme']->{'date-format'}        = 'Y-m-d';
        $theme['user']->saori->color->main      = 'black';
        $theme['user']->saori->{'date-format'}  = 'F j, Y';

        $maker = new Maker(
            $this->getConfig(),
            Collection::make([]),
            "{$this->root}/contents",
            $theme['theme'],
            $theme['user'],
            Collection::make([]),
            true,
            'http://localhost:8000'
        );
        $this->assertEquals('black', $maker->color('main'));
        $this->assertEquals('black', $maker->color('main', true));
        $this->assertEquals('white', $maker->color('main', false));

        $this->assertEquals('F j, Y', $maker->theme('date-format'));
        $this->assertEquals('F j, Y', $maker->theme('date-format', true));
        $this->assertEquals('Y-m-d',  $maker->theme('date-format', false));
    }
}
