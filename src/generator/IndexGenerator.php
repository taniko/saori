<?php
namespace hrgruri\saori\generator;

class IndexGenerator extends Generator
{
    public static function generate(
        Environment $env,
        \stdClass $config
    ) {
        $template   = $env->twig->loadTemplate('template/index.twig');
        $html = $template->render(array(
            'maker' => $env->maker
        ));
        self::putContents("{$env->paths['root']}/index.html", $html);
    }
}
