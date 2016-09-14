<?php

use Hrgruri\Saori\Maker;

class MakerTest extends TestCase
{
    private $maker;

    public function setUp()
    {
        parent::setUp();
        if (!isset($this->maker)) {
            $this->maker = new Maker(
                $this->getConfig(),
                [],
                "{$this->root}/contents",
                new \stdClass,
                new \stdClass,
                [],
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
}
