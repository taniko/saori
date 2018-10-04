<?php
namespace Taniko\Saori;

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
    protected $paths;
    protected $config;
    protected $commands = [
        ThemeCommand::class,
        InitCommand::class,
        PageCommand::class,
        DraftCommand::class,
        PostCommand::class,
        BuildCommand::class,
    ];

    /**
     * Application constructor.
     * @param string $root
     */
    public function __construct(string $root)
    {
        parent::__construct();

        $root = $this->realpath($root);
        $this->paths = [
            'root'          => $root,
            'themes'        => $this->collectThemePaths(realpath(__DIR__ . '/theme')),
            'local_path'    => "{$root}/local",
            'public_path'   => "{$root}/public",
        ];
        $this->config = [
            'env'    => Util::getYamlContents("{$root}/config/env.yml"),
            'theme'  => Util::getYamlContents("{$root}/config/theme.yml") ?? [],
        ];
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->registerCommands();
        return parent::run();
    }

    /**
     *
     */
    private function registerCommands()
    {
        $config = new Config($this->config);
        foreach ($this->commands as $key => $command) {
            if (get_parent_class($command) === 'Taniko\Saori\Console\Command') {
                $this->add(new $command($config));
            } else {
                $this->add(new $command());
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function realpath(string $path): string
    {
        return preg_match('/^vfs:\/\//', $path) == 1 ? $path : realpath($path);
    }

    /**
     * @param string|null $dir
     * @return array
     */
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

    /**
     * @param string $name
     * @param string $path
     */
    public function addTheme(string $name, string $path)
    {
        $this->config['themes'][$name] = $path;
    }

    /**
     * @param string $name
     */
    public function addCommand(string $name)
    {
        $this->commands[] = $name;
    }

    /**
     * @return mixed
     */
    public function getThemes()
    {
        return $this->config['themes'];
    }

    /**
     * @param string $path
     */
    public function setLocal(string $path)
    {
        print_r($this->config);
        $this->config['local_path'] = $this->realpath($path);
        print_r($this->config);

    }

    /**
     * @param string $path
     */
    public function setPublic(string $path)
    {
        $this->config['public_pathl_path'] = $this->realpath($path);
    }
}
