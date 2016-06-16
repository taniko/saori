<?php
namespace hrgruri\saori\generator;

use hrgruri\saori\Article;

class ArticleGenerator extends Generator
{
    const NOAPP         =   10;

    public static function generate(
        Environment $env,
        \stdClass $config
    ) {
        self::copyDirectory(
            $env->paths['article'],
            "{$env->paths['root']}/article"
        );
        $template   = $env->twig->loadTemplate('template/article.twig');
        $tmp        = $env->maker->getNewestArticle();
        if (!isset($tmp[0])) {
            return;
        }
        foreach ($env->articles as $article) {
            $html = $template->render(array(
                'maker' => $env->maker,
                'article' => $article
            ));
            self::putContents("{$env->paths['root']}/{$article->link}/index.html", $html);
        }

        /*  generate articles page  */
        $template   = $env->twig->loadTemplate('template/articles.twig');
        $noapp      = $env->theme_config->noapp ?? self::NOAPP;
        $noapp      = (is_int($noapp) && $noapp > 0) ? $noapp : self::NOAPP;
        for ($i = 1, $j = count($env->articles); $j > 0; $j = $j - $noapp, $i++) {
            $articles      = array_slice($env->articles, ($i-1)*$noapp, $noapp);
            $html = $template->render(array(
                'maker'     =>  $env->maker,
                'articles'  =>  $articles,
                'prev_page' =>  ($i != 1) ? '/page/'.(string)($i-1) : null,
                'next_page' =>  ($j - $noapp > 0) ? '/page/'.(string)($i+1) : null
            ));
            self::putContents("{$env->paths['root']}/page/{$i}/index.html", $html);
        }
    }

    public static function getArticles(array $paths) : array {
        $infos = [];
        foreach(self::getFileList($paths['article'], ['md']) as $file) {
            try {
                if (preg_match('/(.*)\/article\/(.*)\/article\.md/', $file, $m) !== 1
                    || !file_exists("{$m[1]}/config.json")
                    || is_null($info = json_decode(
                            file_get_contents("{$m[1]}/article/{$m[2]}/config.json"))
                        )
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
        return $articles;
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
                    "{$paths['cache']}/article/{$matched[2]}/article.md",
                    self::rewriteImagePath($file, "/article/{$matched[2]}")
                );
            }
        }
    }
}
