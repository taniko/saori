<?php

use Taniko\Saori\Generator\ArticleGenerator;
use Taniko\Saori\Application;
use Taniko\Saori\Util;
use Taniko\Saori\Article;
use Faker\Factory;
use Faker\Generator;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class TestCase extends \PHPUnit\Framework\TestCase
{
    private $article_id;
    protected $root;
    protected $asset;
    protected $url = 'http://localhost:8000';
    protected $faker;

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('blog'));
        $this->root  = vfsStream::url('blog');
        $this->asset = __DIR__.'/asset';
        $this->article_id = 1;
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

    protected static function getProperty($object, $name)
    {
        $reflector = new ReflectionClass(get_class($object));
        $property = $reflector->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    protected function getApplication() : Application
    {
        return new Application($this->root);
    }

    /**
     * @param  \Faker\Generator $faker
     * @param  int              $id
     * @param  stdClass         $config
     * @param  array            $paths
     * @return \Taniko\Saori\Article
     */
    protected function makeArticle(
        \Faker\Generator $faker = null,
        int $id                 = null,
        \stdClass $config       = null,
        array $paths            = null
    ) {
        $faker = $faker ?? Factory::create();
        $article = new \Taniko\Saori\Article(
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
    protected function makeArticleConfig(\Faker\Generator $faker = null) : \stdClass
    {
        $faker  = $faker ?? Factory::create();
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

    protected function getTester(string $name)
    {
        $app  = new Taniko\Saori\Application($this->root);
        return new \Symfony\Component\Console\Tester\CommandTester($app->find($name));
    }

    protected function copyAsset()
    {
        Util::copyDirectory("{$this->asset}/blog", $this->root);
    }

    protected function getArticlesByAsset()
    {
        $paths = ArticleGenerator::collectArticlePaths("{$this->root}/contents/article");
        return ArticleGenerator::createArticles($paths);
    }

    protected function faker(): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create();
        }
        return $this->faker;
    }

    /**
     * generate article and config files
     * @param string $root
     * @param DateTime|null $datetime
     * @param string|null $slug
     * @param array $options
     * @return bool
     */
    protected function generateArticleFile(
        string $root,
        \DateTime $datetime = null,
        string $slug = null,
        array $options = []
    ): bool {
        if (isset($slug) && preg_match('/^[\w-_]+$/', $slug) !== 1) {
            throw new \InvalidArgumentException('slug must be alphabet(s) or underscore');
        }
        if ($datetime === null) {
            $datetime = $this->faker()->dateTimeBetween('-1 years');
        }

        $dir = implode('/', [$root, $datetime->format('Y/m/'), $slug ?? $datetime->format('His')]);
        $faker = $this->faker();
        try {
            Util::putContents("{$dir}/article.md", '');
            Util::putYamlContents("{$dir}/config.yml", [
                'title' => $options['title'] ?? $faker->words(2, true),
                'tag' => $options['tag'] ?? [],
                'timestamp' => $datetime->getTimestamp(),
            ]);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}
