<?php
namespace Taniko\Saori\Generator;

use Illuminate\Support\Collection;
use Taniko\Saori\{
    Article,
    Util
};

class ArticleGenerator extends Generator
{
    public static function generate(Environment $env)
    {
        Util::copyDirectory(
            $env->paths['article'],
            "{$env->paths['root']}/article"
        );
        $template   = $env->twig->loadTemplate('template/article.twig');
        if ($env->articles->count() === 0) {
            return;
        }
        $env->articles->each(function ($article) use ($template, $env) {
            $html = $template->render([
                'maker' => $env->maker,
                'article' => $article
            ]);
            Util::putContents("{$env->paths['root']}/article/{$article->path}/index.html", $html);
        });

        /*  generate articles page  */
        $template   = $env->twig->loadTemplate('template/articles.twig');
        $chunks     = $env->articles->reverse()->chunk($env->size);
        $last       = $chunks->count() - 1;
        $chunks->each(function ($articles, $key) use ($template, $env, $last) {
            $html = $template->render([
                'maker'     =>  $env->maker,
                'articles'  =>  $articles,
                'prev_page' =>  $key == 0     ? null : "{$env->maker->url}/page/{$key}",
                'next_page' =>  $key == $last ? null : "{$env->maker->url}/page/".($key+2)
            ]);
            Util::putContents("{$env->paths['root']}/page/" . ($key+1) . '/index.html', $html);
        });
    }

    /**
     * @param string $dir
     * @return Collection
     */
    public static function getArticles(string $dir)
    {
        return self::createArticles(self::collectArticlePaths($dir));
    }

    /**
     * collect article paths
     * @param  string     $root path of contents/articles
     * @return Collection       article paths
     */
    private static function collectArticlePaths(string $root) : Collection
    {
        return Collection::make(Util::getFileList($root, ['md']))->map(function ($item) {
            return
                preg_match('/(.*)\/([0-9]{4}\/[0-9]{2}\/\w+)\/article\.md$/', $item, $m) === 1
                    && file_exists("{$m[1]}/{$m[2]}/config.yml")
                ? "{$m[1]}/{$m[2]}"
                : null;
        })->filter();
    }

    /**
     * create article instances by paths
     * @param  Collection $items article paths
     * @return Collection        article instances
     */
    private static function createArticles(Collection $items) : Collection
    {
        return $items->map(function ($item) {
            return Article::create($item);
        })->filter()->sort(function ($first, $second) {
            if ($first->timestamp === $second->timestamp) {
                return strnatcmp($first->title, $second->title);
            } else {
                return $first->timestamp < $second->timestamp ? -1 : 1;
            }
        })->map(function (Article $article, $key) {
            $article->setId($key);
            return $article;
        });
    }
}
