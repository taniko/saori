<?php
namespace Hrgruri\Saori\Generator;

use Hrgruri\Saori\Article;
use FeedWriter\{Item, ATOM, Feed};

class FeedGenerator extends Generator
{
    const FEED_NUMBER   =   100;

    public static function generate(
        Environment $env,
        \stdClass $config
    ) {
        $atom = new ATOM;
        $atom->setTitle((string)$config->title);
        $atom->setLink($env->url);
        $atom->setDate(new \DateTime());
        $number = $config->feed->number ?? self::FEED_NUMBER;
        $number = is_int($number) && $number > 0 ? $number : self::FEED_NUMBER;
        for ($i = 0 ; $i < $number && $i < count($env->articles); $i++) {
            $article    = $env->articles[$i];
            $item       = $atom->createNewItem() ;
            $item->setAuthor((string)($config->author));
            $item->setTitle($article->title);
            $item->setLink($article->link);
            $item->setDate($article->getTimestamp());
            $item->setDescription($article->html());
            $atom->addItem($item);
        }
        self::putContents("{$env->paths['root']}/feed.atom", $atom->generateFeed());
    }
}
