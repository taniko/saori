<?php
namespace Taniko\Saori;

use Symfony\Component\Yaml\Yaml;

class Util
{
    /**
     * mkdir and put file
     * @param  string   $filename output file name
     * @param  mixed    $data contents data
     * @param  bool     $override if a file already exists, save the data
     * @throws \Exception
     * @return mixed if successed, return bytes. if failed, return false
     */
    public static function putContents(string $filename, $data, bool $override = false)
    {
        $result = false;
        $dirname = dirname($filename);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0700, true);
        }
        if (!file_exists($filename) || $override) {
            $result = file_put_contents($filename, $data);
        } else {
            throw new \Exception("{$filename} is already exists");
        }
        return $result;
    }

    /**
     * mkdir and put yaml file
     * @param  string   $filename output file name
     * @param  mixed    $data contents data
     * @param  bool     $override if a file already exists, save the data
     * @return mixed if successed, return bytes. if failed, return false
     */
    public static function putYamlContents(string $filename, $data, bool $override = false)
    {
        return self::putContents($filename, Yaml::dump($data), $override);
    }

    /**
     * get yaml file data as array
     * @param  string $filename source file name
     * @return array|null
     */
    public static function getYamlContents(string $filename)
    {
        $result = null;
        if (file_exists($filename)) {
            $result = Yaml::parse(file_get_contents($filename));
        }
        return $result;
    }

    /**
     * clear directory
     * @param  string  $dir  path to directory
     * @param  boolean $flag if .git or .gitkeep are exists, set true
     */
    public static function clearDirectory(string $dir, bool $flag = true)
    {
        if (!file_exists($dir)) {
            return;
        }
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (($file === '.git' || $file === '.gitkeep') && $flag === true) {
                    continue;
                }
                $path = "{$dir}/{$file}";
                if (is_dir($path)) {
                    self::clearDirectory($path, false);
                } else {
                    unlink($path);
                }
            }
            closedir($dh);
        }
        if ($flag !== true) {
            rmdir($dir);
        }
    }

    /**
     * copy directory
     * @param  string $source path to source directory
     * @param  string $dest   pasy to destination directory
     */
    public static function copyDirectory(string $source, string $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0700, true);
        }
        if (is_dir($source) && ($dh = opendir($source))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$source}/{$file}")) {
                    self::copyDirectory("{$source}/{$file}", "{$dest}/{$file}");
                } else {
                    copy("{$source}/{$file}", "{$dest}/{$file}");
                }
            }
            closedir($dh);
        }
    }

    /**
     * mkdir and copy file
     * @param  string $source path to source file
     * @param  string $dest   path to destination
     */
    public static function copyFile(string $source, string $dest)
    {
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        copy($source, $dest);
    }

    /**
     * get file paths
     * @param  string $path
     * @param  array  $exts extensions
     * @return array
     */
    public static function getFileList(string $path, array $exts = null) : array
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

    public static function rewriteImagePath(string $file, string $path) : string
    {
        return preg_replace(
            '/\!\[(.*)\]\(([a-zA-Z0-9\-_\/]+\.[a-zA-Z]+)(\s+\".*\"|)\)/',
            '![${1}]('. rtrim($path, '/') .'/${2}${3})',
            file_get_contents($file)
        );
    }
}
