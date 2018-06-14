<?php
namespace Taniko\Saori\Generator;

use Taniko\Saori\Util;
use Illuminate\Support\Collection;

class TagPageGenerator extends Generator
{
    public static function generate(Environment $env)
    {
        // create tag index
        $template = $env->twig->loadTemplate('template/tags.twig');
        $html = $template->render([
            'maker' => $env->maker
        ]);
        Util::putContents("{$env->paths['root']}/tag/index.html", $html);

        // create tag page
        $template = $env->twig->loadTemplate('template/articles.twig');
        $env->tag_list->keys()->each(function ($tag) use ($env, $template) {
            $chunks = $env->maker->getArticlesByTag($tag)->chunk($env->maker->theme('size'));
            $last = $chunks->count() - 1;
            $chunks->each(function ($articles, $key) use ($env, $template, $last, $tag) {
                $html = $template->render([
                    'maker' => $env->maker,
                    'articles' => $articles,
                    'prev_page' => $key == 0 ? null : "{$env->maker->url}/tag/{$tag}/{$key}",
                    'next_page' => $key == $last ? null : "{$env->maker->url}/tag/{$tag}/" . ($key + 2)
                ]);
                $i = $key + 1;
                if ($i === 1) {
                    Util::putContents("{$env->paths['root']}/tag/{$tag}/index.html", $html);
                }
                Util::putContents("{$env->paths['root']}/tag/{$tag}/{$i}/index.html", $html);
            });
        });
    }

    /**
     * @param Collection $articles
     * @throws \RuntimeException
     * @return Collection
     */
    public static function getTagList(Collection $articles) : Collection
    {
        $tags = [];
        foreach ($articles as $article) {
            foreach ($article->tags as $tag) {
                $tags[$tag][] = $article->id;
            }
        }
        foreach ($tags as $key => $value) {
            $tags[$key] = Collection::make($tags[$key]);
        }
        ksort($tags, SORT_NATURAL);
        $tags = Collection::make($tags);

        $duplicated = self::pluckDuplicatedTags($tags);
        if (count($duplicated)) {
            $msg = "Duplicate tag error. Please unify uppercase letters and lowercase letters.\n\n";
            foreach ($duplicated as $lower => $items) {
                $msg .= 'tags: ' . implode(', ', $items). "\n";
                foreach ($items as $item) {
                    $msg .= "  * {$item}\n";
                    foreach ($tags[$item] as $id) {
                        $msg .= "    - /contents/article{$articles[$id]->link}\n";
                    }
                }
                $msg .= "\n";
            }
            throw new \RuntimeException($msg);
        }

        return $tags;
    }

    /**
     * @param Collection $tags
     * @return array
     */
    private static function pluckDuplicatedTags(Collection $tags) : array
    {
        $lowers = [];
        foreach ($tags as $tag => $items) {
            $lowers[strtolower($tag)][] = $tag;
        }
        return array_filter($lowers, function ($item) {
            return count($item) > 1;
        });
    }
}
