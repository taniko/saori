<?php
namespace Test\Unit;

use Taniko\Saori\Article;
use Taniko\Saori\Util;
use Taniko\Saori\Generator\ArticleGenerator;

class ArticleTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
        Util::copyDirectory("{$this->asset}/blog", $this->root);
    }

    public function testCache()
    {
        $paths    = ArticleGenerator::collectArticlePaths("{$this->root}/contents/article");
        $articles = ArticleGenerator::createArticles($paths);
        $article  = $articles->first();
        $article->cache("{$this->asset}/blog/contents/article", "{$this->root}/cache/article");
        $this->assertTrue(file_exists("{$this->root}/cache/article{$article->link}/article.html"));
    }
}
