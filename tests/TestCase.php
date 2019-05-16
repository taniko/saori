<?php

use Taniko\Saori\Application;
use Taniko\Saori\Util;
use Faker\Factory;
use Faker\Generator;
use org\bovigo\vfs\{
    vfsStream,
    vfsStreamWrapper,
    vfsStreamDirectory
};

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $root;
    protected $asset;
    protected $url = 'http://localhost:8000';
    protected $faker;

    /**
     * @throws \org\bovigo\vfs\vfsStreamException
     */
    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('blog'));
        $this->root  = vfsStream::url('blog');
        $this->asset = __DIR__.'/asset';
    }

    /**
     * @param $instance
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws ReflectionException
     */
    protected function callMethod($instance, string $name, array $args = [])
    {
        $method = new  ReflectionMethod($instance, $name);
        $method->setAccessible(true);
        return $method->invokeArgs($instance, $args);
    }

    /**
     * @param $object
     * @param $name
     * @return mixed
     * @throws ReflectionException
     */
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

    /**
     * @param string $name
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    protected function getTester(string $name)
    {
        $app  = new Taniko\Saori\Application($this->root);
        return new \Symfony\Component\Console\Tester\CommandTester($app->find($name));
    }

    /**
     *
     */
    protected function copyAsset()
    {
        Util::copyDirectory("{$this->asset}/blog", $this->root);
    }

    /**
     * @return Generator
     */
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
     * @return null|string
     */
    protected function generateArticleFile(
        string $root = null,
        \DateTime $datetime = null,
        string $slug = null,
        array $options = []
    ): ?string {
        if (!isset($root)) {
            $root = "{$this->root}/contents";
        }
        if (isset($slug) && preg_match('/^[\w\-_]+$/', $slug) !== 1) {
            throw new \InvalidArgumentException('slug must be alphabet(s) or underscore');
        }
        if ($datetime === null) {
            $datetime = $this->faker()->dateTimeBetween('-1 years');
        }

        $dir = implode('/', [$root, $datetime->format('Y/m'), $slug ?? $datetime->format('His')]);
        $faker = $this->faker();
        try {
            Util::putContents("{$dir}/article.md", '');
            Util::putYamlContents("{$dir}/config.yml", [
                'title' => $options['title'] ?? $faker->words(2, true),
                'tag' => $options['tag'] ?? [],
                'timestamp' => $datetime->getTimestamp(),
            ]);
            $result = $dir;
        } catch (\Exception $e) {
            $result = null;
        }
        return $result;
    }
}
