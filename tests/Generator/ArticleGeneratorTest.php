<?php
namespace Test\Generator;

use Taniko\Saori\Util;
use Taniko\Saori\Article;
use Taniko\Saori\Generator\ArticleGenerator;

class ArticleGeneratorTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
        Util::copyDirectory("{$this->asset}/blog", $this->root);
    }

    public function testCreateArticles()
    {
        $articles = ArticleGenerator::getArticles("{$this->root}/contents/article");
        $this->assertTrue($articles->count() > 0);
        $this->assertContainsOnly(Article::class, $articles);
    }
}
