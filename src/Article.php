<?php
namespace hrgruri\saori;

use hrgruri\saori\ArticleInfo;
use cebe\markdown\GithubMarkdown;

class Article
{
    private $id;
    public $title;
    public $tag;
    public $html;
    public $timestamp;
    public $link;
    public $newer_link;
    public $older_link;

    public function __construct(ArticleInfo $info)
    {
        $this->id           =   $info->id;
        $this->title        =   $info->title;
        $this->tag          =   $info->tag;
        $this->html         =   (new GithubMarkdown)->parse(file_get_contents("{$info->path}/article.md"));
        $this->timestamp    =   $info->timestamp;
        $this->link         =   $info->link;
        $this->newer_link   =   $info->newer_link;
        $this->older_link   =   $info->older_link;
    }

    /**
     * get article id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int $length
     * @return string
     */
    public function striptags(int $length = null){
        if (is_int($length) && $length > 0) {
            $result = mb_substr(strip_tags($this->html), 0, $length);
        } else {
            $result = strip_tags($this->html);
        }
        return $result;
    }

    public function getDate(string $format = 'F j, Y')
    {
        return date($format, $this->timestamp);
    }
}
