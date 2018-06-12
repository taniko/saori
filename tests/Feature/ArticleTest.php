<?php
namespace Test\Feature;

use Taniko\Saori\Generator\ArticleGenerator;

class ArticleTest extends \TestCase
{
    public function testCreate()
    {
        $this->assertTrue($this->generateArticleFile("{$this->root}/contents"));
        $this->assertEquals(1, ArticleGenerator::getArticles("{$this->root}/contents")->count());
    }
}
