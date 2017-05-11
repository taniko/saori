<?php
namespace Test\Generator;

use Taniko\Saori\Util;
use Illuminate\Support\Collection;
use Taniko\Saori\Generator\tagPageGenerator;

class TagPageGeneratorTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
        Util::copyDirectory("{$this->asset}/blog", $this->root);
    }

    public function testGetTagList()
    {
        $articles = $this->getArticlesByAsset();
        $tag_list = TagPageGenerator::getTagList($articles);
        foreach ($tag_list as $tag => $keys) {
            $keys = $keys->toArray();
            $this->assertContains($tag, $articles->get($keys[array_rand($keys)])->tags);
        }
    }
}
