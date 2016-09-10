<?php

use Faker\Factory as Faker;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param  mixed    $instance
     * @param  string   $name method name
     * @param  array    $args arguments
     * @return mixed
     */
    protected function callMethod($instance, string $name, array $args = [])
    {
        $method = new  ReflectionMethod($instance, $name);
        $method->setAccessible(true);
        return $method->invokeArgs($instance, $args);
    }

    /**
     * @param  \Faker\Generator $faker
     * @param  int              $id
     * @param  stdClass         $config
     * @param  array            $paths
     * @return \Hrgruri\Saori\Article
     */
    protected function createArticle(
        \Faker\Generator $faker = null,
        int $id                 = null,
        \stdClass $config       = null,
        array $paths            = null
    ) {
        $faker = $faker ?? Faker::create();
        $article = new \Hrgruri\Saori\Article(
            $id     ?? rand(),
            $config ?? $this->createArticleConfig($faker),
            $paths  ?? ([
                'cache' => '',
                'link'  => '',
                'newer' => '',
                'older' => ''
            ])
        );
        return $article;
    }

    /**
     * @param  \Faker\Generator $faker
     * @return stdClass
     */
    protected function createArticleConfig(\Faker\Generator $faker = null) : \stdClass
    {
        $faker  = $faker ?? Faker::create();
        $config = new \stdClass;
        $config->title      = $faker->text(20);
        $config->tag        = $faker->words(3, false);
        $config->timestamp  = $faker->unixTime();
        return $config;
    }
}
