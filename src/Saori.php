<?php
namespace hrgruri\saori;

use hrgruri\saori\{ArticleInfo, Maker};
use cebe\markdown\GithubMarkdown;

class Saori
{
    const SAORI_COMMANd =   ['init', 'post', 'make'];
    const CONFIG_LIST   =   ['id', 'local', 'title', 'author', 'theme', 'lang', 'link'];
    private $root;
    private $path;
    private $article_list;
    private $theme_config;
    private $tag_list;

    public function __construct(string $root)
    {
        $this->root = rtrim($root, '/');
        $this->path = [
            'local'     =>  "{$this->root}/local",
            'public'    =>  '',
            'contents'  =>  "{$this->root}/contents",
            'article'   =>  "{$this->root}/contents/article",
            'markdown'  =>  "{$this->root}/contents/markdown",
            'cache'     =>  "{$this->root}/cache"
        ];
    }

    public function run(array $argv)
    {
        foreach($argv as $key => $val) {
            $argv[$key] = strtolower($val);
        }
        try {
            $command = strtolower($argv[1] ?? '');
            if (!in_array($command, self::SAORI_COMMANd)) {
                throw new \Exception('not found command');
            }
            unset($argv[0]);
            unset($argv[1]);
            $this->checkConfig();
            $this->loadConfig();
            $this->{$command}(array_values($argv));
        } catch (\Exception $e) {
            print "ERROR\n". ($e->getMessage() ?? '') ."\n";
        }

    }

    private function init(array $option)
    {
        if (is_dir($this->path['local'])) {
            throw new \Exception("directory({$this->path['local']}) already exists");
        } elseif (is_dir($this->path['public'])) {
            throw new \Exception("directory({$this->path['public']}) already exists");
        } elseif (is_dir($this->path['article'])) {
            throw new \Exception("directory({$this->path['article']}) already exists");
        }
        mkdir($this->path['local'], 0700);
        mkdir($this->path['public'], 0700);
        mkdir($this->path['article'], 0700, true);
    }

    private function post(array $option)
    {
        $dir        = date('Y/m');
        $title      = $option[0] ?? date('dHi');
        $timestamp   = date('YmdHis');
        if (preg_match('/^[\w-]*$/', $title) !== 1) {
            throw new \Exception('error: title');
        }
        $dir = "{$this->path['article']}/{$dir}/{$title}";
        if (is_dir($dir)) {
            throw new \Exception("this title({$title}) already exist");
        }
        mkdir($dir, 0700, true);
        touch("{$dir}/article.md");
        $tmp = [
            "title"     =>  (string)$title,
            "tag"       =>  [],
            "timestamp"  =>  time()
        ];
        file_put_contents("{$dir}/config.json", json_encode($tmp, JSON_PRETTY_PRINT));
    }

    private function make(array $option)
    {
        $this->clearDirectory($this->path['local'], true);
        $this->clearDirectory($this->path['public'], true);
        $this->clearDirectory($this->path['cache']);
        $this->article_list =   $this->getArticleList();
        $this->cacheArticle();
        // rewrite path
        foreach ($this->article_list as &$article) {
            $article->path = "{$this->path['cache']}{$article->link}";
        }

        /* make local   */
        $url    = $this->config->local;
        $path   = $this->path['local'];
        $this->copyTheme($path);
        $this->copyDirectory($this->path['article'], "{$path}/article");
        $this->makeIndex($url, $path);
        $this->makeArticle($url, $path);
        $this->makeArticlesPage($url, $path);
        $this->makeTagPage($url, $path);

        /* make Public  */
        $url    = "https://{$this->config->id}.github.io";
        $path   = $this->path['public'];
        $this->copyTheme($path);
        $this->copyDirectory($this->path['article'], "{$path}/article");
        $this->makeIndex($url, $path);
        $this->makeArticle($url, $path);
        $this->makeArticlesPage($url, $path);
        $this->makeTagPage($url, $path);

        /*  clear cache */
        $this->clearDirectory($this->path['cache']);
    }

    private function checkConfig()
    {
        if (!file_exists("{$this->root}/config.json")) {
            throw new \Exception('config.json does not exist');
        } elseif (is_null($config = json_decode(file_get_contents("{$this->root}/config.json")))) {
            throw new \Exception('cannot open or decode config.json');
        }
        $flag = true;
        foreach (self::CONFIG_LIST as $key) {
            $flag = $flag && isset($config->{$key});
        }
        if ($flag !== true) {
            throw new \Exception("undefined value exists. please check config.json");
        } elseif (!($config->link instanceof \stdClass)) {
            throw new \Exception('link must be object');
        } elseif (!is_dir(__DIR__. "/theme/{$config->theme}")) {
            throw new \Exception('not found theme');
        }
        return $flag;
    }

    private function loadConfig()
    {
        $data   = json_decode(file_get_contents("{$this->root}/config.json"));
        $data->local = rtrim($data->local, '/');
        $this->path['public']   =   "{$this->root}/{$data->id}.github.io";
        $this->path['theme']    =   __DIR__."/theme/{$data->theme}";
        $this->config           =   $data;
        if (file_exists(__DIR__ . "/theme/{$data->theme}/config.json")) {
            $this->theme_config = json_decode(
                file_get_contents(__DIR__ ."/theme/{$data->theme}/config.json")
            );
        } else {
            $this->theme_config = null;
        }
    }

    private function clearDirectory(string $dir, bool $flag = true)
    {
        if (!file_exists($dir)) {
            return;
        }
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif(($file === '.git' || $file === '.gitkeep') && $flag === true) {
                    continue;
                }
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->clearDirectory($path, false);
                }else{
                    unlink($path);
                }
            }
            closedir($dh);
        }
        if ($flag !== true) {
            rmdir($dir);
        }
    }

    private function copyDirectory(string $from, string $to)
    {
        if (!is_dir($to)) {
            mkdir($to);
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

    private function getArticleList()
    {
        $tags = [];
        $result = [];
        $dir = $this->path['article'];
        if (!is_dir($dir)) {
            throw new \Exception();
        }
        if ($dh = opendir($dir)) {
            while (($year = readdir($dh)) !== false) {
                if ($year === '.' || $year === '..') {
                    continue;
                }
                if ($dh_year = opendir("{$dir}/{$year}")) {
                    while (($month = readdir($dh_year)) !== false) {
                        if ($month === '.' || $month === '..') {
                            continue;
                        }
                        if ($dh_month = opendir("{$dir}/{$year}/{$month}")) {
                            while (($title = readdir($dh_month)) !== false) {
                                $path = "{$dir}/{$year}/{$month}/{$title}";
                                if ($title === '.' || $title === '..') {
                                    continue;
                                } elseif (!file_exists("{$path}/article.md") || !file_exists("{$path}/config.json")) {
                                    continue;
                                }
                                $info       = json_decode(file_get_contents("{$path}/config.json"));
                                $result[]   = new ArticleInfo(
                                    $info->timestamp,
                                    $path,
                                    $info->title,
                                    $info->tag,
                                    "/article/{$year}/{$month}/{$title}"
                                );
                            }
                        }
                    }
                }
            }
            closedir($dh);
        }
        usort($result, function ($a, $b) {
            return $b->timestamp <=> $a->timestamp
                ?: strnatcmp($a->title, $b->title);
        });
        $i = 0;
        foreach ($result as $article) {
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
        return $result;
    }

    private function makeArticle(string $url, string $to)
    {
        if (!is_dir("{$this->path['contents']}/article")) {
            $this->copyDirectory("{$this->path['contents']}/article", "{$to}/article");
        }
        $maker      = $this->getMaker();
        $twig       = new \Twig_Environment(new \Twig_Loader_Filesystem("{$this->path['theme']}/template"));
        $template   = $twig->loadTemplate('article.twig');
        $article    = $maker->getNewestArticle()[0];
        for($i = 0; $i < count($this->article_list); $i++) {
            $html = $template->render(array(
                'maker' => $maker,
                'article' => $article
            ));
            file_put_contents("{$to}{$article->link}/index.html", $html);
            $article = $maker->getNextArticle($article)[0];
        }
    }

    private function makeIndex(string $url, string $to)
    {
        $maker      = $this->getMaker();
        $twig       = new \Twig_Environment(new \Twig_Loader_Filesystem("{$this->path['theme']}/template"));
        $template   = $twig->loadTemplate('index.twig');
        $html = $template->render(array(
            'maker' => $maker
        ));
        file_put_contents("{$to}/index.html", $html);
    }

    private function makeArticlesPage(string $url, string $to)
    {
        $maker      = $this->getMaker();
        $twig       = new \Twig_Environment(new \Twig_Loader_Filesystem("{$this->path['theme']}/template"));
        $template   = $twig->loadTemplate('articles.twig');
        $noapp      = $this->theme_config->noapp ?? 10;
        $noapp      = (is_int($noapp) && $noapp > 0) ? $noapp : 10;
        for ($i = 1, $j = count($this->article_list); $j > 0; $j = $j - $noapp, $i++) {
            mkdir("{$to}/page/{$i}", 0700, true);
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
            file_put_contents("{$to}/page/{$i}/index.html", $html);
        }
    }

    private function makeTagPage(string $url, string $to)
    {
        $maker      = $this->getMaker();
        $twig       = new \Twig_Environment(new \Twig_Loader_Filesystem("{$this->path['theme']}/template"));
        $template   = $twig->loadTemplate('tags.twig');
        $html = $template->render(array(
            'maker'     =>  $maker
        ));
        mkdir("{$to}/tag", 0700, true);
        file_put_contents("{$to}/tag/index.html", $html);
        $template   = $twig->loadTemplate('articles.twig');
        $noapp      = $this->theme_config->noapp ?? 10;
        $noapp      = (is_int($noapp) && $noapp > 0) ? $noapp : 10;
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
                mkdir("{$to}/tag/{$tag}/{$i}", 0700, true);
                if ($i === 1) {
                    file_put_contents("{$to}/tag/{$tag}/index.html", $html);
                }
                file_put_contents("{$to}/tag/{$tag}/{$i}/index.html", $html);
            }
        }
    }

    private function copyTheme(string $to)
    {
        if (is_dir("{$this->path['theme']}/css")) {
            $this->copyDirectory("{$this->path['theme']}/css", "{$to}/css");
        }
        if (is_dir("{$this->path['theme']}/js")) {
            $this->copyDirectory("{$this->path['theme']}/js", "{$to}/js");
        }
        if (is_dir("{$this->path['theme']}/img")) {
            $this->copyDirectory("{$this->path['theme']}/img", "{$to}/img");
        }
    }

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

    /**
     * @return maker
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
}
