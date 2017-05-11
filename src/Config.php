<?php
namespace Taniko\Saori;

class Config
{
    private $root;   // path to blog directory
    private $themes; // theme list
    private $theme;  // theme environment
    private $env;    // blog environment
    private $paths;  // path list

    public function __construct($config)
    {
        $this->root     = rtrim($config['root'], '/');
        $this->themes   = $config['themes'] ?? [];
        $this->env      = $config['env']    ?? [];
        $this->theme    = $config['theme']  ?? [];
        $this->paths    = $this->generatePaths($this->root);
    }

    public function __get(string $name)
    {
        return $this->{$name} ?? null;
    }

    /**
     * generate paths used by generator
     * @param  string $root path to blog
     * @return array        path list
     */
    public function generatePaths(string $root) : array
    {
        $theme = null;
        if (isset($this->env['theme'])) {
            $theme = $this->themes[$this->env['theme']];
        }
        return [
            'cache'     => "{$root}/cache",
            'public'    => "{$root}/public",
            'local'     => "{$root}/local",
            'contents'  => "{$root}/contents",
            'article'   => "{$root}/contents/article",
            'page'      => "{$root}/contents/page",
            'file'      => "{$root}/contents/file",
            'markdown'  => "{$root}/contents/markdown",
            'theme'     => $theme,
        ];
    }

    /**
     * get path
     * @param  string $name required path name
     * @return string|null  return path to directory or null
     */
    public function path(string $name)
    {
        return $this->paths[$name] ?? null;
    }
}
