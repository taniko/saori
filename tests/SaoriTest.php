<?php

use Hrgruri\Saori\Saori;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class SaoriTest extends TestCase
{
    private $root;

    public function setUp()
    {
        parent::setUp();
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('example'));
        $this->root = vfsStream::url('example');
    }

    public function testMkdir()
    {
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('sub'));
        Saori::mkdir("{$this->root}/sub");
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('sub'));
    }

    public function testClearDirectory()
    {
        mkdir("{$this->root}/foo");
        mkdir("{$this->root}/.bar");
        mkdir("{$this->root}/.git");
        Saori::clearDirectory($this->root);
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('foo'));
        $this->assertFalse(vfsStreamWrapper::getRoot()->hasChild('.bar'));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('.git'));
    }

    public function testInit()
    {
        $saori = new Saori($this->root);
        $saori->init();
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('contents'));
    }

    public function testInitException()
    {
        $saori = new Saori($this->root);
        $saori->init();
        try {
            $flag = false;
            $saori->init();
        } catch (\Exception $e) {
            $flag = true;
        } finally {
            $this->assertTrue($flag, 'uncatch \Exception');
        }
    }

    public function testDraft()
    {
        $title = 'saori';
        $saori = new Saori($this->root);
        $saori->draft($title);
        $data = json_decode(file_get_contents("{$this->root}/draft/saori/config.json"));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild("draft/{$title}"));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild("draft/{$title}/article.md"));
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild("draft/{$title}/config.json"));
        $this->assertEquals($title, $data->title);
        $this->assertInternalType('array', $data->tag);
        $this->assertEquals(0, count($data->tag));
    }

    public function testDraftException()
    {
        $title = 'saori';
        $saori = new Saori($this->root);
        try {
            $flag = false;
            $saori->draft($title);
            $saori->draft($title);
        } catch (\Exception $e) {
            $flag = true;
        } finally {
            $this->assertTrue($flag, 'uncatch \Exception');
        }
    }
}
