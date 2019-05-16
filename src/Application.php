<?php
namespace Taniko\Saori;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Taniko\Saori\Console\{
    ThemeCommand,
    InitCommand,
    PageCommand,
    DraftCommand,
    PostCommand,
    BuildCommand
};

class Application extends \Symfony\Component\Console\Application
{
    protected $path;
    protected $local_path = null;
    protected $public_path = null;
    protected $config = [];
    protected $commands = [
        ThemeCommand::class,
        InitCommand::class,
        PageCommand::class,
        DraftCommand::class,
        PostCommand::class,
        BuildCommand::class,
    ];

    public function __construct($path)
    {
        parent::__construct();
        if (!preg_match('/^vfs:\/\//', $path) == 1) {
            $path = realpath($path);
        }
        $this->path = $path;
        $this->config = [
            'root'   => $this->path,
            'local_path'    => $this->local_path,
            'public_path'   => $this->public_path,
            'themes' => $this->collectThemePaths(realpath(__DIR__ . '/theme')),
            'env'    => Util::getYamlContents("{$this->path}/config/env.yml"),
            'theme'  => Util::getYamlContents("{$this->path}/config/theme.yml") ?? [],
        ];
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->registerCommands();
        return parent::run();
    }

    private function registerCommands()
    {
        $this->config['local_path']  = $this->local_path;
        $this->config['public_path'] = $this->public_path;
        $config = new Config($this->config);
        foreach ($this->commands as $key => $command) {
            if (get_parent_class($command) === 'Taniko\Saori\Console\Command') {
                $this->add(new $command($config));
            } else {
                $this->add(new $command());
            }
        }
    }

    public function collectThemePaths(string $dir = null) : array
    {
        $result = [];
        $dir    = $dir ?? realpath(__DIR__ . '/theme');
        if (is_dir($dir) && ($dh = opendir($dir))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$dir}/{$file}")) {
                    $result[$file] = realpath("{$dir}/{$file}");
                }
            }
            closedir($dh);
        }
        return $result;
    }

    public function addTheme(string $name, string $path)
    {
        $this->config['themes'][$name] = $path;
    }

    public function addCommand(string $name)
    {
        $this->commands[] = $name;
    }

    public function getThemes()
    {
        return $this->config['themes'];
    }

    /**
     * @param string $path output path for building a local site
     */
    public function setLocalBuildPath(string $path)
    {
        $this->local_path = rtrim($path, '/');
    }

    /**
     * @param string $path output path for building a public site
     */
    public function setPublicBuildPath(string $path)
    {
        $this->public_path = rtrim($path, '/');
    }
}
