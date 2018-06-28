<?php
namespace Test\Feature;

use Taniko\Saori\Article;

class ArticleTest extends \TestCase
{
    public function testCreate()
    {
        $path = $this->generateArticleFile("{$this->root}/contents");
        $this->assertInternalType('string', $path);

        $article = Article::create($path);
        $article->setId(1);
        $article->setId(2);
        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals(1, $article->id);
    }

    public function testCache()
    {
        $path = $this->generateArticleFile("{$this->root}/contents");
        $article = Article::create($path);
        $article->setId(1);

        $article->createCache("{$this->root}/cache/article");
        $this->assertTrue(file_exists("{$this->root}/cache/article/{$article->path}/article.html"));
    }

    public function testUrl()
    {
        $path = $this->generateArticleFile();
        $article = Article::create($path);
        $this->assertEquals("/article/{$article->path}", $article->url());
        $this->assertEquals(
            "http://localhost:8000/article/{$article->path}",
            $article->url('http://localhost:8000')
        );
    }
}
