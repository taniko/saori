<?php

use Faker\Factory as Faker;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $root;

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('saori'));
        $this->root = vfsStream::url('saori');
    }
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

    /**
     * @param  string $name file name
     * @return string
     */
    protected function file(string $name) : string
    {
        $name = ltrim($name, '/');
        return "{$this->root}/$name";
    }

    /**
     * get configuration
     * @return \stdClass
     */
    protected function getConfig() : \stdClass
    {
        $config = (object)[
            'id'    =>  'username',
            'local' =>  'http://localhost:8000',
            'title' =>  'Sample Blog',
            'author'=>  'John Doe',
            'theme' =>  'saori',
            'lang'  =>  'en',
            'link'  =>  [
                'github'    =>  'https://github.com',
                'twitter'   =>  'https://twitter.com'
            ],
            'feed'  =>  [
                'type'      =>  'atom',
                'number'    =>  50
            ]
        ];
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config->$key = (object)$value;
            }
        }
        return $config;
    }

    protected function getThemeConfig() : array
    {
        $theme_config = [
            'color' => [
                'main' => 'white'
            ]
        ];
        $user_theme = [
            'saori' => [
                'color' => [
                    'main' => 'black'
                ]
            ]
        ];

        return [
            'theme' => json_decode(json_encode($theme_config)),
            'user'  => json_decode(json_encode($user_theme))
        ];
    }
}
