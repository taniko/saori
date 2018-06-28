<?php
namespace Test\Unit;

use Taniko\Saori\Maker;
use Taniko\Saori\Config;
use Taniko\Saori\Generator\{
    ArticleGenerator,
    TagPageGenerator
};
use Illuminate\Support\Collection;

class MakerTest extends \TestCase
{
    private $maker;
    private $config;

    public function setUp()
    {
        parent::setUp();
        $this->copyAsset();
        if (!isset($this->maker)) {
            $app = $this->getApplication();
            $articles = ArticleGenerator::getArticles("{$this->root}/contents/article");
            $tag_list = TagPageGenerator::getTagList($articles);
            $this->config = new Config($this->getProperty($app, 'config'));
            $this->maker = new Maker(
                $this->config,
                $articles,
                $tag_list,
                false,
                'http://localhost:8000'
            );
        }
    }

    public function testIsPublicAndLocal()
    {
        $this->assertTrue($this->maker->isLocal());
        $this->assertFalse($this->maker->isPublic());
    }

    public function testGetTitle()
    {
        $this->assertEquals($this->config->env['title'], $this->maker->title());
    }

    public function testGetEnv()
    {
        $this->assertEquals($this->config->env['local'], $this->maker->env('local'));
    }

    public function testGetColor()
    {
        $color = $this->config->theme[$this->config->env['theme']]['color'];
        $this->assertEquals($color['body'], $this->maker->color('body'));
    }

    public function testGetTheme()
    {
        $color = $this->config->theme[$this->config->env['theme']]['color'];
        $this->assertEquals($color['body'], $this->maker->theme('color.body'));
        $this->assertNotnull($this->maker->theme('color.body', false));
        $this->assertNotEquals($color['body'], $this->maker->theme('color.body', false));
        $this->assertNull($this->maker->theme('color.body.abc'));
        $this->assertEquals($color['body'], $this->maker->color('body'));
    }

    public function testGetNewestArticles()
    {
        $articles = $this->maker->getNewestArticles(2);
        $this->assertTrue($articles->get(0)->timestamp > $articles->get(1)->timestamp);
    }

    public function testGetOldestArticles()
    {
        $articles = $this->maker->getOldestArticles(2);
        $keys = $articles->keys();
        $this->assertTrue($articles->get(0)->timestamp < $articles->get(1)->timestamp);
    }

    public function testGetNewerArticles()
    {
        $articles = $this->maker->getNewerArticles($this->maker->articles->first());
        $this->assertTrue($this->maker->articles->first()->timestamp < $articles->get(0)->timestamp);
        $this->assertTrue($articles->get(0)->timestamp < $articles->get(1)->timestamp);
    }

    public function testGetOlderArticles()
    {
        $articles = $this->maker->getOlderArticles($this->maker->articles->last());
        $this->assertTrue($this->maker->articles->last()->timestamp > $articles->get(0)->timestamp);
        $this->assertTrue($articles->get(0)->timestamp > $articles->get(1)->timestamp);
    }

    public function testGetArticlesByTag()
    {
        $tag = 'php';
        $articles = $this->maker->getArticlesByTag($tag);
        $this->assertTrue($articles->get(0)->timestamp > $articles->get(1)->timestamp);
        $this->assertTrue(in_array($tag, $articles->get(0)->tags));
    }

    public function testGetArticlesByTagAsc()
    {
        $tag = 'php';
        $articles = $this->maker->getArticlesByTagAsc($tag);
        $this->assertTrue($articles->get(0)->timestamp < $articles->get(1)->timestamp);
        $this->assertTrue(in_array($tag, $articles->get(0)->tags));
    }

    public function testMarkdown()
    {
        $this->assertInternalType('string', $this->maker->markdown('profile.md'));
        $cached = false;
        try {
            $this->maker->markdown('not_exists_file');
        } catch (\Exception $e) {
            $cached = true;
        } finally {
            $this->assertTrue($cached, 'uncached \Exception');
        }
    }

    public function testGetTagListLength()
    {
        $keys = $this->maker->tag_list->keys();
        $this->assertTrue($this->maker->getTagListLength($keys->random()) > 0);
    }
}
