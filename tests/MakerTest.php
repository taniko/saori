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
            true
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
}
