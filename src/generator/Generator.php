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
     * @param  array  $exts extensions
     * @return array
     */
    protected static function getFileList(string $path, array $exts = null) : array
    {
        $files = [];
        if (is_dir($path) && ($dh = opendir($path))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..' || $file === '.DS_Store') {
                    continue;
                } elseif (is_dir("{$path}/{$file}")) {
                    $files = array_merge($files, self::getFileList("{$path}/{$file}", $exts));
                } elseif (is_null($exts)) {
                    $files[] = "{$path}/{$file}";
                } elseif (in_array(pathinfo("{$path}/{$file}", PATHINFO_EXTENSION), $exts)) {
                    $files[] = "{$path}/{$file}";
                } else {
                    continue;
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

    protected static function copyFile(string $from, string $to)
    {
        if (preg_match('/(.*)\/.*\..*$/', $to, $matched) == 1) {
            if (!file_exists($matched[1])) {
                mkdir($matched[1], 0700, true);
            }
            if (!file_exists($to)) {
                copy($from, $to);

            } else {
                print "{$to} is already exists\n";
            }
        }
    }

    protected static function rewriteImagePath(string $file, string $path)
    {
        return preg_replace(
            '/\!\[.*\]\(([a-zA-Z0-9\-_\/]+\.[a-zA-Z]+)(\s+\"\w*\"|)\)/',
            '![]('. $path .'/${1}${2})',
            file_get_contents($file)
        );
    }
}
