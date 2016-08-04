<?php
namespace Hrgruri\Saori\Generator;

use Hrgruri\Saori\Article;

class TagPageGenerator extends Generator
{
    const NOAPP         =   10;

    public static function generate(
        Environment $env,
        \stdClass $config
    ) {
        $template   = $env->twig->loadTemplate('template/tags.twig');
        $html = $template->render(array(
            'maker'     =>  $env->maker
        ));
        self::putContents("{$env->paths['root']}/tag/index.html", $html);
        $template   = $env->twig->loadTemplate('template/articles.twig');
        $noapp      = $env->theme_config->noapp ?? self::NOAPP;
        $noapp      = (is_int($noapp) && $noapp > 0) ? $noapp : self::NOAPP;
        foreach ($env->tag_list as $tag => $tag_ids) {
            for ($i = 1; count($tag_ids) > 0; $i++) {
                $articles   = [];
                $ids        = [];
                for ($j = 0; $j < $noapp; $j++) {
                    if (($id = array_shift($tag_ids)) === null) {
                        break;
                    }
                    $ids[] = $id;
                }
                foreach ($ids as $id) {
                    $articles[] = $env->articles[$id];
                }
                $html = $template->render(array(
                    'maker'     =>  $env->maker,
                    'articles'  =>  $articles,
                    'prev_page' =>  ($i != 1)               ? "/tag/{$tag}/".(string)($i-1) : null,
                    'next_page' =>  (count($tag_ids) > 0)   ? "/tag/{$tag}/".(string)($i+1) : null
                ));
                if ($i === 1) {
                    self::putContents("{$env->paths['root']}/tag/{$tag}/index.html", $html);
                }
                self::putContents("{$env->paths['root']}/tag/{$tag}/{$i}/index.html", $html);
            }
        }
    }

    public static function getTagList(array $articles) : array
    {
        $tags = [];
        foreach ($articles as $article) {
            foreach ($article->tags as $tag) {
                $tags[$tag][] = $article->getId();
            }
        }
        ksort($tags, SORT_NATURAL);
        return $tags;
    }
}
