<?php

use Faker\Factory as Faker;
use Hrgruri\Saori\Article;

class ArticleTest extends TestCase
{
    private function createArticleConfig()
    {
        $faker = Faker::create();
        $config = new \stdClass;
        $config->title      = $faker->text(20);
        $config->tag        = $faker->words(3, false);
        $config->timestamp  = $faker->unixTime();
        return $config;
    }

    public function testGetId()
    {
        $id = rand();
        $article = new Article(
            $id,
            $this->createArticleConfig(),
            [
                'cache' => '',
                'link'  => '',
                'newer' => '',
                'older' => ''
            ]
        );
        $this->assertEquals($id, $article->getId());
    }

    public function testGetTimestamp()
    {
        $config  = $this->createArticleConfig();
        $article = new Article(
            rand(),
            $config,
            [
                'cache' => '',
                'link'  => '',
                'newer' => '',
                'older' => ''
            ]
        );
        $this->assertEquals($config->timestamp, $article->getTimestamp());
    }

    public function testGetDate()
    {
        $config  = $this->createArticleConfig();
        $article = new Article(
            rand(),
            $config,
            [
                'cache' => '',
                'link'  => '',
                'newer' => '',
                'older' => ''
            ]
        );
        $this->assertEquals(
            date('F j, Y', $config->timestamp),
            $article->getDate()
        );
        $this->assertEquals(
            date('Y-m-d',  $config->timestamp),
            $article->getDate('Y-m-d')
        );
    }
}
