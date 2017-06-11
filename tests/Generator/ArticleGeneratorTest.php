<?php
namespace Test\Generator;

use Taniko\Saori\Util;
use Illuminate\Support\Collection;
use Taniko\Saori\Generator\ArticleGenerator;

class ArticleGeneratorTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
        Util::copyDirectory("{$this->asset}/blog", $this->root);
    }

    public function testCollectArticlePaths()
    {
        $paths = ArticleGenerator::collectArticlePaths("{$this->root}/contents/article");
        $this->assertTrue($paths->count() > 0);
    }

    public function testCreateArticles()
    {
        $paths    = ArticleGenerator::collectArticlePaths("{$this->root}/contents/article");
        $articles = ArticleGenerator::createArticles($paths);
        $this->assertContainsOnly(\Taniko\Saori\Article::class, $articles);
    }
}
