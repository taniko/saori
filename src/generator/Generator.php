<?php
namespace hrgruri\saori\generator;

use cebe\markdown\GithubMarkdown;

abstract class Generator
{
    abstract public static function generate(
        Environment $env,
        \stdClass $config
    );

    /**
     * @param  string $path
     * @return array
     */
    protected static function getFileList(string $path) : array
    {
        $files = [];
        if (is_dir($path) && ($dh = opendir($path))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$path}/{$file}")) {
                    $files = array_merge($files, self::getFileList("{$path}/{$file}"));
                } else {
                    $files[] = "{$path}/{$file}";
                }
            }
            closedir($dh);
        }
        return $files;
    }

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
    protected static function getHTML(string $file)
    {
        $html = null;
        if (file_exists($file)) {
            $html = (new GithubMarkdown)->parse(file_get_contents($file));
        }
        return $html;
    }

    protected static function copyDirectory(string $from, string $to)
    {
        if (!is_dir($to)) {
            mkdir($to, 0700, true);
        }
        if (is_dir($from) && ($dh = opendir($from))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$from}/{$file}")) {
                    self::copyDirectory("{$from}/{$file}", "{$to}/{$file}");
                } else {
                    copy("{$from}/{$file}", "{$to}/{$file}");
                }
            }
            closedir($dh);
        }
    }

    protected static function putContents(string $path, string $contents)
    {
        if (preg_match('/(.*)\/.*\..*$/', $path, $matched) == 1) {
            if (!file_exists($matched[1])) {
                mkdir($matched[1], 0700, true);
            }
            if (!file_exists($path)) {
                file_put_contents($path, $contents);
            } else {
                print "{$path} is already exists\n";
            }
        }
    }
}
