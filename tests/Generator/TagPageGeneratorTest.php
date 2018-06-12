<?php
namespace Test\Generator;

use Taniko\Saori\Generator\ArticleGenerator;
use Taniko\Saori\Util;
use Illuminate\Support\Collection;
use Taniko\Saori\Generator\tagPageGenerator;

class TagPageGeneratorTest extends \TestCase
{
    public function testGetTagList()
    {
        Util::copyDirectory("{$this->asset}/blog", $this->root);

        $articles = $this->getArticlesByAsset();
        $tag_list = TagPageGenerator::getTagList($articles);
        foreach ($tag_list as $tag => $keys) {
            $keys = $keys->toArray();
            $this->assertContains($tag, $articles->get($keys[array_rand($keys)])->tags);
        }
    }

    public function testFailedDuplicateTags()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateArticleFile("{$this->root}/contents", null, null, ['tag' => ['php', 'PHP']]);
        $articles = ArticleGenerator::getArticles("{$this->root}/contents");
        TagPageGenerator::getTagList($articles);
    }
}
