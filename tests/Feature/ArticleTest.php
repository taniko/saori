<?php
namespace Test\Feature;

class ArticleTest extends \TestCase
{
    public function testCreate()
    {
        $this->createArticleData();
        $this->assertTrue(true);
    }
}