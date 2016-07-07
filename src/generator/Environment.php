<?php
namespace hrgruri\saori\generator;

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
        \hrgruri\saori\Maker $maker,
        \Twig_Environment $twig
    ) {
        $this->maker    = $maker;
        $this->twig     = $twig;
    }
}
