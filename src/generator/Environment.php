<?php
namespace Hrgruri\Saori\Generator;

class Environment
{
    public $paths;
    public $maker;
    public $twig;
    public $url;
    public $articles;
    public $theme_config;
    public $tag_list;

    public function __construct(
        \Hrgruri\Saori\Maker $maker,
        \Twig_Environment $twig
    ) {
        $this->maker    = $maker;
        $this->twig     = $twig;
    }
}
