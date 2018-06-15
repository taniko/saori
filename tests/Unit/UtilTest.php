<?php
namespace Test\Unit;

use Taniko\Saori\Util;
use Symfony\Component\Yaml\Yaml;

class UtilTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testPutYamlContents()
    {
        $filename = "{$this->root}/foo/bar/file.yml";
        $data = [
            'name' => 'saori'
        ];
        $this->assertFalse(file_exists($filename));
        Util::putYamlContents($filename, $data);
        $this->assertTrue(file_exists($filename));
        $this->assertEquals($data, Yaml::parse(file_get_contents($filename)));
    }
}
