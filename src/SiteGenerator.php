<?php
namespace hrgruri\saori;

use hrgruri\saori\{ArticleInfo, Maker};
use hrgruri\saori\exception\GeneratorException;
use cebe\markdown\GithubMarkdown;
use FeedWriter\{Item, ATOM, Feed};

class SiteGenerator
{
    const NOAPP         =   10;
    const FEED_TYPE     =   'atom';
    const FEED_NUMBER   =   100;
    private $root;
    private $url;
    private $path;
    private $config;
    private $theme_config;
    private $article_list;

    public function __construct(array $path, \stdClass $config, \stdClass $tc)
    {
        $this->path         =   $path;
        $this->config       =   $config;
        $this->theme_config =   $tc;
        $this->article_list =   $this->getArticleList();
        $this->cacheArticle();
    }

    public function generate(string $url, string $to)
    {
        $this->url  = rtrim($url, '/');
        $this->root = rtrim($to, '/');
        $this->copyTheme();
        $this->generateIndex();
        $this->generateArticlePage();
        $this->generateTagPage();
        $this->generateAtomFeed();
    }

    private function generateIndex()
    {
        $maker = $this->getMaker();
        $twig       = $this->getTwigEnvironment();
        $template   = $twig->loadTemplate('generate/index.twig');
        $html = $template->render(array(
            'maker' => $maker
        ));
        file_put_contents("{$this->root}/index.html", $html);
    }

    private function generateArticlePage()
    {
        $this->copyDirectory($this->path['article'], "{$this->root}/article");
        $maker      = $this->getMaker();
        $twig       = $this->getTwigEnvironment();
        /*  generate each article page  */
        $template   = $twig->loadTemplate('generate/article.twig');
        $tmp        = $maker->getNewestArticle();
        if (!isset($tmp[0])) {
            return;
        }
        $article    = $tmp[0];
        for($i = 0; $i < count($this->article_list); $i++) {
            $html = $template->render(array(
                'maker' => $maker,
                'article' => $article
            ));
            file_put_contents("{$this->root}/{$article->link}/index.html", $html);
            $article = $maker->getNextArticle($article)[0];
        }
        /*  generate articles page  */
        $template   = $twig->loadTemplate('generate/articles.twig');
        $noapp      = $this->theme_config->noapp ?? self::NOAPP;
        $noapp      = (is_int($noapp) && $noapp > 0) ? $noapp : self::NOAPP_NOAPP;
        for ($i = 1, $j = count($this->article_list); $j > 0; $j = $j - $noapp, $i++) {
            mkdir("{$this->root}/page/{$i}", 0700, true);
            $articles   = null;
            $infos      = array_slice($this->article_list, ($i-1)*$noapp, $noapp);
            foreach($infos as $info) {
                $articles[] = new Article($info);
            }
            $html = $template->render(array(
                'maker'     =>  $maker,
                'articles'  =>  $articles,
                'prev_page' =>  ($i != 1) ? '/page/'.(string)($i-1) : null,
                'next_page' =>  ($j - $noapp > 0) ? '/page/'.(string)($i+1) : null
            ));
            file_put_contents("{$this->root}/page/{$i}/index.html", $html);
        }
    }

    private function generateTagPage()
    {
        $maker      = $this->getMaker();
        $twig       = $this->getTwigEnvironment();
        $template   = $twig->loadTemplate('generate/tags.twig');
        $html = $template->render(array(
            'maker'     =>  $maker
        ));
        mkdir("{$this->root}/tag", 0700, true);
        file_put_contents("{$this->root}/tag/index.html", $html);
        $template   = $twig->loadTemplate('generate/articles.twig');
        $noapp      = $this->theme_config->noapp ?? self::NOAPP;
        $noapp      = (is_int($noapp) && $noapp > 0) ? $noapp : self::NOAPP;
        foreach ($this->tag_list as $tag => $tag_ids) {
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
                    $articles[] = new Article($this->article_list[$id]);
                }
                $html = $template->render(array(
                    'maker'     =>  $maker,
                    'articles'  =>  $articles,
                    'prev_page' =>  ($i != 1)               ? "/tag/{$tag}/".(string)($i-1) : null,
                    'next_page' =>  (count($tag_ids) > 0)   ? "/tag/{$tag}/".(string)($i+1) : null
                ));
                mkdir("{$this->root}/tag/{$tag}/{$i}", 0700, true);
                if ($i === 1) {
                    file_put_contents("{$this->root}/tag/{$tag}/index.html", $html);
                }
                file_put_contents("{$this->root}/tag/{$tag}/{$i}/index.html", $html);
            }
        }
    }

    private function generateAtomFeed()
    {
        $atom = new ATOM;
        $atom->setTitle((string)$this->config->title);
        $atom->setLink($this->url);
        $atom->setDate(new \DateTime());
        $number = $this->config->feed->number ?? self::FEED_NUMBER;
        $number = is_int($number) && $number > 0 ? $number : self::FEED_TYPE;
        for ($i = 0 ; $i < $number && $i < count($this->article_list); $i++) {
            $article    = new Article($this->article_list[$i]);
            $item       = $atom->createNewItem() ;
            $item->setAuthor((string)($this->config->author));
            $item->setTitle($article->title);
            $item->setLink($article->link);
            $item->setDate($article->timestamp);
            $item->setDescription($article->html);
            $atom->addItem($item);
        }
        mkdir("{$this->root}/feed", 0700, true);
        file_put_contents("{$this->root}/feed.atom", $atom->generateFeed());
    }

    /**
     * @return \hrgruri\saori\Maker
     */
    private function getMaker()
    {
        return new Maker(
            $this->config,
            $this->article_list,
            $this->path['contents'],
            $this->theme_config,
            $this->tag_list
        );
    }

    private function getTwigEnvironment()
    {
        return new \Twig_Environment(
            new \Twig_Loader_Filesystem("{$this->path['theme']}/template")
        );
    }

    private function getArticleList()
    {
        $tags       = [];
        $articles   = [];
        $path = $this->path['article'];
        if (is_dir($path)) {
            foreach ($this->getSubDirectory($path) as $year) {
                foreach ($this->getSubDirectory("{$path}/{$year}") as $month) {
                    foreach ($this->getSubDirectory("{$path}/{$year}/{$month}") as $dir) {
                        $config = $this->loadArticleConfig("{$path}/{$year}/{$month}/{$dir}");
                        if (!is_null($config)) {
                            $articles[]   = new ArticleInfo(
                                $config->timestamp,
                                "{$path}/{$year}/{$month}/{$dir}",
                                $config->title,
                                $config->tag,
                                "/article/{$year}/{$month}/{$dir}"
                            );
                        }
                    }
                }
            }
        }
        usort($articles, function ($a, $b) {
            return $b->timestamp <=> $a->timestamp
                ?: strnatcmp($a->title, $b->title);
        });
        $i = 0;
        foreach ($articles as $article) {
            $article->newer_link = isset($result[$i - 1]) ? $result[$i - 1]->link : null;
            $article->older_link = isset($result[$i + 1]) ? $result[$i + 1]->link : null;
            $article->id = $i++;
            sort($article->tag, SORT_NATURAL);
            foreach($article->tag as $tag) {
                $tags[$tag][] = $article->id;
            }
        }
        ksort($tags, SORT_NATURAL);
        $this->tag_list = $tags;
        return $articles;
    }

    private function getSubDirectory(string $path)
    {
        $dirs = [];
        if ( is_dir($path) && ($dh = opendir($path)) ) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$path}/{$file}")) {
                    $dirs[] = $file;
                }
            }
            closedir($dh);
        }
        return $dirs;
    }

    /**
     * @param  string $dir article directory
     * @return null | \stdClass
     */
    private function loadArticleConfig(string $dir)
    {
        if (file_exists("{$dir}/article.md") && file_exists("{$dir}/config.json")) {
            $config = json_decode(file_get_contents("{$dir}/config.json"));
            if (!isset($config->title) || !is_string($config->title)) {
                $config = null;
            } elseif (!isset($config->timestamp) || !is_int($config->timestamp)) {
                $config = null;
            }
        } else {
            $config = null;
        }
        return $config;
    }

    /**
     * generate article cache
     * @return null
     */
    private function cacheArticle()
    {
        if (!is_dir($this->path['cache'])) {
            mkdir($this->path['cache'], 0700);
        }
        foreach ($this->article_list as $article) {
            if (!is_dir("{$this->path['cache']}{$article->link}")) {
                mkdir("{$this->path['cache']}{$article->link}", 0700, true);
            }
            file_put_contents(
                "{$this->path['cache']}{$article->link}/article.md",
                $this->rewriteImagePath("{$article->path}/article.md", $article->link)
            );
        }
    }

    /**
     * @param  string $file
     * @param  string $path
     * @return string
     */
    private function rewriteImagePath(string $file, string $path)
    {
        return preg_replace(
            '/\!\[.*\]\(([a-zA-Z0-9\-_\/]+\.[a-zA-Z]+)(\s+\"\w*\"|)\)/',
            '![]('. $path .'/${1}${2})',
            file_get_contents($file)
        );
    }

    private function copyTheme()
    {
        if (is_dir("{$this->path['theme']}/css")) {
            $this->copyDirectory("{$this->path['theme']}/css", "{$this->root}/css");
        }
        if (is_dir("{$this->path['theme']}/js")) {
            $this->copyDirectory("{$this->path['theme']}/js", "{$this->root}/js");
        }
        if (is_dir("{$this->path['theme']}/img")) {
            $this->copyDirectory("{$this->path['theme']}/img", "{$this->root}/img");
        }
    }

    private function copyDirectory(string $from, string $to)
    {
        if (!is_dir($to)) {
            mkdir($to, 0700, true);
        }
        if ($dh = opendir($from)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$from}/{$file}")) {
                    $this->copyDirectory("{$from}/{$file}", "{$to}/{$file}");
                } else {
                    copy("{$from}/{$file}", "{$to}/{$file}");
                }
            }
            closedir($dh);
        }
    }
}
