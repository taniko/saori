<?php
namespace Taniko\Saori\Generator;

use Taniko\Saori\Util;
use FeedWriter\ATOM;

class FeedGenerator extends Generator
{
    const FEED_SIZE = 100;

    public static function generate(Environment $env)
    {
        $atom = new ATOM;
        $atom->setTitle($env->maker->env('title'));
        $atom->setLink($env->maker->url);
        $atom->setDate(new \DateTime());
        $size = $env->maker->env('feed.size') ?? self::FEED_SIZE;
        $size = is_int($size) && $size > 0 ? $size : self::FEED_SIZE;
        $articles = $env->maker->articles->reverse()->take($size)->values();
        foreach ($articles as $key => $article) {
            $item = $atom->createNewItem() ;
            $item->setAuthor($env->maker->env('author'));
            $item->setTitle($article->title);
            $item->setLink($article->url);
            $item->setDate($article->timestamp);
            $item->setDescription($article->html());
            $atom->addItem($item);
        }
        Util::putContents("{$env->paths['root']}/feed.atom", $atom->generateFeed());
    }
}
