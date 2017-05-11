<?php
namespace Taniko\Saori\Generator;

use cebe\markdown\GithubMarkdown;

abstract class Generator
{
    abstract public static function generate(Environment $env);

    protected static function trimFilePath(string $file, string $path, bool $flag = false) : string
    {
        $path = rtrim($path, '/') . '/';
        if ($flag == true) {
            if (preg_match('/(.*)\..*$/', $file, $matched) == 1) {
                $str = substr($matched[1], strlen($path));
            } else {
                $str = substr($file, strlen($path));
            }
        } else {
            $str = substr($file, strlen($path));
        }
        return $str;
    }

    /**
     * @param  string $file
     * @return string | null
     */
    protected static function getHtml(string $file)
    {
        $html = null;
        if (file_exists($file)) {
            $html = (new GithubMarkdown)->parse(file_get_contents($file));
        }
        return $html;
    }

    /**
     * @param  string $str
     * @return string
     */
    protected static function getHtmlByString(string $str) : string
    {
        return (new GithubMarkdown)->parse($str);
    }

    protected static function rewriteImagePath(string $file, string $path) : string
    {
        return preg_replace(
            '/\!\[(.*)\]\(([a-zA-Z0-9\-_\/]+\.[a-zA-Z]+)(\s+\".*\"|)\)/',
            '![${1}]('. rtrim($path, '/') .'/${2}${3})',
            file_get_contents($file)
        );
    }
}
