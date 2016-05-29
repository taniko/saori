<?php
namespace hrgruri\saori\generator;

class Environment
{
    public $paths;
    public $maker;
    public $twig;
    public $url;
    public $articles;

    public function __construct(
        array $paths,
        \hrgruri\saori\Maker $maker,
        \Twig_Environment $twig,
        string $url,
        array $articles
    ) {
        $this->paths    = $paths;
        $this->maker    = $maker;
        $this->twig     = $twig;
        $this->url      = $url;
        $this->articles = $articles;
    }
}
