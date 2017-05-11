<?php
namespace Taniko\Saori\Generator;

use cebe\markdown\GithubMarkdown;
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
            Util::putContents("{$env->paths['root']}/article{$article->link}/index.html", $html);
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

    public static function getArticles(string $dir, string $url)
    {
        return self::createArticles(self::collectArticlePaths($dir), $url);
    }

    /**
     * collect article paths
     * @param  string     $root path of contents/articles
     * @return Collection       article paths
     */
    public static function collectArticlePaths(string $root) : Collection
    {
        return Collection::make(Util::getFileList($root, ['md']))->map(function ($item) {
            return
                preg_match('/(.*)\/([0-9]{4}\/[0-9]{2}\/\w+)\/article\.md$/', $item, $m) === 1
                    && file_exists("{$m[1]}/{$m[2]}/config.yml")
                ? ['path' => "{$m[1]}/{$m[2]}", 'link' => "/{$m[2]}"]
                : null;
        })->filter();
    }

    /**
     * create article instances by paths
     * @param  Collection $items article paths
     * @return Collection        article instances
     */
    public static function createArticles(Collection $items, $url) : Collection
    {
        return $items->map(function ($item) {
            $config = Util::getYamlContents("{$item['path']}/config.yml");
            return [
                'link'   => $item['link'],
                'path'   => $item['path'],
                'config' => $config
            ];
        })->sort(function ($first, $second) {
            if ($first['config']['timestamp'] === $second['config']['timestamp']) {
                return strnatcmp($first['config']['title'], $second['config'['title']]);
            } else {
                return $first['config']['timestamp'] < $second['config']['timestamp'] ? -1 : 1;
            }
        })->values()->map(function ($data, $key) use ($url) {
            return new Article($key, $data, $url);
        });
    }
}
