<?php

use Hrgruri\Saori\Generator\Generator;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class GeneratorTest extends TestCase
{
    private $instance;

    public function setUp()
    {
        parent::setUp();
        $this->instance = new class extends Generator {
            public static function generate(\Hrgruri\Saori\Generator\Environment $env){}
        };
    }

    public function testGetFileList()
    {
        touch($this->file('a.jpg'));
        touch($this->file('b.png'));
        touch($this->file('c.pdf'));
        mkdir($this->file('sub'));
        touch($this->file('sub/d.jpg'));
        touch($this->file('sub/e.pdf'));
        $result = $this->callMethod(
            $this->instance,
            'getFileList',
            [$this->root, ['jpg','png']]
        );
        $this->assertTrue(in_array($this->file('a.jpg'), $result));
        $this->assertTrue(in_array($this->file('b.png'), $result));
        $this->assertTrue(in_array($this->file('sub/d.jpg'), $result));
        $this->assertFalse(in_array($this->file('c.pdf'), $result));
        $this->assertFalse(in_array($this->file('sub/e.pdf'), $result));
    }

    public function testTrimFilePath()
    {
        $result = $this->callMethod(
            $this->instance,
            'trimFilePath',
            [$this->file('a.jpg'), 'vfs://saori/']
        );
        $this->assertEquals('a.jpg', $result);
        $result = $this->callMethod(
            $this->instance,
            'trimFilePath',
            [$this->file('sub/a.jpg'), 'vfs://saori/']
        );
        $this->assertEquals('sub/a.jpg', $result);
    }

    public function testGetHtml()
    {
        $file   = $this->file('file.md');
        file_put_contents($file, '#a');
        $result = $this->callMethod($this->instance, 'getHtml', [$file]);
        $this->assertRegExp('/<h1>a\<\/h1>/', $result);
    }

    public function testGetHtmlByString()
    {
        $text   = '#a';
        $result = $this->callMethod($this->instance, 'getHtmlByString', [$text]);
        $this->assertRegExp('/<h1>a\<\/h1>/', $result);
    }

    public function testCopyDirectory()
    {
        mkdir($this->file('dir1'));
        mkdir($this->file('dir2'));
        touch($this->file('dir1/a'));
        $this->callMethod($this->instance, 'copyDirectory', [$this->file('dir1'), $this->file('dir2')]);
        $this->assertTrue(file_exists($this->file('dir1/a')));
        $this->assertTrue(file_exists($this->file('dir2/a')));
    }

    public function testPutContents()
    {
        $this->assertFalse(file_exists($this->file('a')));
        $this->callMethod($this->instance, 'putContents', [$this->file('a.txt'), 'abc']);
        $this->assertTrue(file_exists($this->file('a.txt')));
        $data = file_get_contents($this->file('a.txt'));
        $this->assertEquals('abc', $data);
    }

    public function testPutContentsCatchException()
    {
        $flag = false;
        $this->callMethod($this->instance, 'putContents', [$this->file('a.txt'), 'abc']);
        try {
            $this->callMethod($this->instance, 'putContents', [$this->file('a.txt'), 'abc']);
        } catch (\Exception $e) {
            $flag = true;
        } finally {
            $this->assertTrue($flag, 'not catch Exception');
        }
    }

    public function testCopyFile()
    {
        $files = ['a.txt', 'b.txt'];
        $this->assertFalse(file_exists($this->file($files[0])));
        touch($this->file($files[0]));
        $this->callMethod(
            $this->instance,
            'copyFile',
            [$this->file($files[0]), $this->file($files[1])]
        );
        $this->assertTrue(file_exists($this->file($files[0])));
        $this->assertTrue(file_exists($this->file($files[1])));
    }

    public function testCopyFileCatchException()
    {
        $flag = false;
        $files = ['a.txt', 'b.txt'];
        $this->assertFalse(file_exists($this->file($files[0])));
        touch($this->file($files[0]));
        $this->callMethod(
            $this->instance,
            'copyFile',
            [$this->file($files[0]), $this->file($files[1])]
        );
        try {
            $this->callMethod(
                $this->instance,
                'copyFile',
                [$this->file($files[0]), $this->file($files[1])]
            );
        } catch (\Exception $e) {
            $flag = true;
        } finally {
            $this->assertTrue($flag, 'not catch Exception');
        }
    }

    public function testRewriteImagePath()
    {
        file_put_contents("{$this->root}/text.md", '![](http://hrgruri.github.io/favicon.ico)');
        $result = $this->callMethod($this->instance, 'rewriteImagePath', ["{$this->root}/text.md", '']);
        $this->assertEquals('![](http://hrgruri.github.io/favicon.ico)', $result);

        file_put_contents("{$this->root}/text.md", '![](favicon.ico)');
        $result = $this->callMethod($this->instance, 'rewriteImagePath', ["{$this->root}/text.md", '/img']);
        $this->assertEquals('![](/img/favicon.ico)', $result);

        file_put_contents("{$this->root}/text.md", '![favicon](favicon.ico)');
        $result = $this->callMethod($this->instance, 'rewriteImagePath', ["{$this->root}/text.md", '/img']);
        $this->assertEquals('![favicon](/img/favicon.ico)', $result);
    }
}
