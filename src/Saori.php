<?php
namespace Hrgruri\Saori;

class Saori
{
    private $root;
    private $paths;

    public function __construct(string $root)
    {
        $root = rtrim($root, '/');
        $this->root  = $root;
        $this->paths = [
            'root'      => $root,
            'contents'  => "{$root}/contents"
        ];
    }

    public static function clearDirectory(string $dir, bool $flag = true)
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
                $path = "{$dir}/{$file}";
                if (is_dir($path)) {
                    self::clearDirectory($path, false);
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

    /**
     * make directory
     * @param  string  $path
     * @param  boolean $flag
     * @throws \Exception
     * @return boolean
     */
    public static function mkdir(string $path, bool $flag = false)
    {
        $result = false;
        if (file_exists($path) && $flag) {
            throw new \Exception("{$path} already exists");
        } elseif (!file_exists($path)) {
            $result = mkdir($path, 0700, true);
        }
        return $result;
    }

    /**
     * Initialize
     */
    public function init()
    {
        self::mkdir($this->paths['contents'], true);
        file_put_contents(
            "{$this->paths['contents']}/config.json",
            json_encode(
                [
                    'id'    =>  'username',
                    'local' =>  'http://localhost:8000',
                    'title' =>  'Sample Blog',
                    'author'=>  'John Doe',
                    'theme' =>  'saori',
                    'lang'  =>  'en',
                    'link'  =>  [
                        'github'    =>  'https://github.com',
                        'twitter'   =>  'https://twitter.com'
                    ]
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /**
     * create draft file
     * @param  string $name
     */
    public function draft(string $name)
    {
        $dir = "{$this->root}/draft/$name";
        try {
            self::mkdir($dir, true);
            touch("{$dir}/article.md");
            file_put_contents(
                "{$dir}/config.json",
                json_encode(
                [
                    "title"     =>  (string)$name,
                    "tag"       =>  [],
                ],
                JSON_PRETTY_PRINT
                )
            );
        } catch (\Exception $e) {
            throw new \Exception("draft/{$name} already exists");
        }
    }
}
