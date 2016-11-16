<?php
namespace Hrgruri\Saori\Generator;

use Hrgruri\Saori\Article;
use cebe\markdown\GithubMarkdown;
use Illuminate\Support\Collection;

class ArticleGenerator extends Generator
{
    public static function generate(Environment $env)
    {
        self::copyDirectory(
            $env->paths['article'],
            "{$env->paths['root']}/article"
        );
        $template   = $env->twig->loadTemplate('template/article.twig');
        $tmp        = $env->maker->getNewestArticle();
        if (!isset($tmp[0])) {
            return;
        }
        $env->articles->each(function ($article) use ($template, $env) {
            $html = $template->render(array(
                'maker' => $env->maker,
                'article' => $article
            ));
            self::putContents("{$env->paths['root']}/{$article->link}/index.html", $html);
        });

        /*  generate articles page  */
        $template   = $env->twig->loadTemplate('template/articles.twig');
        $chunks     = $env->articles->chunk($env->noapp);
        $last       = $chunks->count() - 1;
        $chunks->each(function ($articles, $key) use ($template, $env, $last) {
            $html = $template->render(array(
                'maker'     =>  $env->maker,
                'articles'  =>  $articles,
                'prev_page' =>  $key == 0 ? null : "/page/{$key}",
                'next_page' =>  $key == $last ? null : '/page/'. $key+1
            ));
            self::putContents("{$env->paths['root']}/page/{$key}/index.html", $html);
        });
    }

    public static function getArticles(array $paths) : Collection
    {
        $infos = [];
        foreach(self::getFileList($paths['article'], ['md']) as $file) {
            try {
                if (preg_match('/(.*)\/article\/(.*)\/article\.md/', $file, $m) !== 1
                    || !file_exists("{$m[1]}/config.json")
                    || is_null($info = json_decode(file_get_contents("{$m[1]}/article/{$m[2]}/config.json")))
                ) {
                    continue;
                } else {
                    $infos[] = [
                        'path'      => "/article/{$m[2]}",
                        'timestamp' => $info->timestamp,
                        'title'     => $info->title
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        usort($infos, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp']
                ?: strnatcmp($a['title'], $b['title']);
        });
        $articles = [];
        $i = 0;
        foreach ($infos as $info) {
            $articles[] = new Article(
                $i,
                json_decode(file_get_contents("{$paths['contents']}{$info['path']}/config.json")),
                [
                    'cache' =>  "{$paths['cache']}{$info['path']}",
                    'link'  =>  $info['path'],
                    'newer' =>  isset($infos[$i -1]) ? $infos[$i -1]['path'] : null,
                    'older' =>  isset($infos[$i +1]) ? $infos[$i +1]['path'] : null
                ]
            );
            $i++;
        }
        return Collection::make($articles);
    }

    public static function cacheArticle(array $paths)
    {
        foreach(self::getFileList($paths['article'], ['md']) as $file) {
            if (preg_match('/(.*)\/article\/(.*)\/article\.md/', $file, $matched) !== 1) {
                continue;
            }
            if (!is_dir("{$paths['cache']}/article/$matched[2]}")) {
                mkdir("{$paths['cache']}/article/{$matched[2]}", 0700, true);
                file_put_contents(
                    "{$paths['cache']}/article/{$matched[2]}/article.html",
                    (new GithubMarkdown)->parse(self::rewriteImagePath($file, "/article/{$matched[2]}"))
                );
            }
        }
    }
}
