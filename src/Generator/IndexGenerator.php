<?php
namespace Taniko\Saori\Generator;

use Taniko\Saori\Util;

class IndexGenerator extends Generator
{
    public static function generate(Environment $env)
    {
        $template   = $env->twig->loadTemplate('template/index.twig');
        $html = $template->render([
            'maker' => $env->maker
        ]);
        Util::putContents("{$env->paths['root']}/index.html", $html);
    }
}
