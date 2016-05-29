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
        $article    = $tmp[0];
        for($i = 0; $i < count($env->articles); $i++) {
            $html = $template->render(array(
                'maker' => $env->maker,
                'article' => $article
            ));
            self::putContents("{$env->paths['root']}/{$article->link}/index.html", $html);
            $article = $env->maker->getNextArticle($article)[0];
        }

        /*  generate articles page  */
        $template   = $env->twig->loadTemplate('template/articles.twig');
        $noapp      = $env->theme_config->noapp ?? self::NOAPP;
        $noapp      = (is_int($noapp) && $noapp > 0) ? $noapp : self::NOAPP_NOAPP;
        for ($i = 1, $j = count($env->articles); $j > 0; $j = $j - $noapp, $i++) {
            $articles   = null;
            $infos      = array_slice($env->articles, ($i-1)*$noapp, $noapp);
            foreach($infos as $info) {
                $articles[] = new Article($info);
            }
            $html = $template->render(array(
                'maker'     =>  $env->maker,
                'articles'  =>  $articles,
                'prev_page' =>  ($i != 1) ? '/page/'.(string)($i-1) : null,
                'next_page' =>  ($j - $noapp > 0) ? '/page/'.(string)($i+1) : null
            ));
            self::putContents("{$env->paths['root']}/page/{$i}/index.html", $html);
        }
    }
}
