<?php

use Faker\Factory as Faker;
use Hrgruri\Saori\Article;

class ArticleTest extends TestCase
{

    public function testGetId()
    {
        $faker   = Faker::create();
        $id      = rand();
        $article = $this->createArticle($faker, $id);
        $this->assertEquals($id, $article->getId());
    }

    public function testGetTimestamp()
    {
        $faker   = Faker::create();
        $config  = $this->createArticleConfig($faker);
        $article = $this->createArticle($faker, null, $config);
        $this->assertEquals($config->timestamp, $article->getTimestamp());
    }

    public function testGetDate()
    {
        $faker   = Faker::create();
        $config  = $this->createArticleConfig($faker);
        $article = $this->createArticle($faker, null, $config);
        $this->assertEquals(
            date('F j, Y', $config->timestamp),
            $article->getDate()
        );
        $this->assertEquals(
            date('Y-m-d',  $config->timestamp),
            $article->getDate('Y-m-d')
        );
    }

    /**
     * @test
     */
    public function get()
    {
        $config  = $this->createArticleConfig();
        $article = $this->createArticle(null, null, $config);
        $this->assertEquals($config->title, $article->title);
        $this->assertEquals($config->timestamp, $article->timestamp);
        $this->assertInternalType('array', $article->tags);
        $this->assertInternalType('int', $article->id);
    }
}
