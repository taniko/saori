<?php
namespace Hrgruri\Saori;

class MakerTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->maker = new Maker(
            null,
            null,
            null,
            null,
            null,
            null,
            true,
            'http://localhost:8000'
        );
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
