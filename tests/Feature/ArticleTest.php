<?php
namespace Test\Feature;

class ArticleTest extends \TestCase
{
    public function testCreate()
    {
        $this->generateArticleFile("{$this->root}/contents");
        $this->assertTrue(true);
    }
}
