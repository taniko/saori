<?php
namespace Taniko\Saori\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Taniko\Saori\SiteGenerator;
use Taniko\Saori\Util;
use \Taniko\Saori\Generator\ArticleGenerator;

class BuildCommand extends Command
{
    private $called;
    private $generator;

    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Generate static site')
            ->addOption(
                'local',
                null,
                InputOption::VALUE_NONE,
                'If set, generate only local site'
            )
            ->addOption(
                'public',
                null,
                InputOption::VALUE_NONE,
                'If set, generate only public(github.io) site'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->called = false;
        try {
            SiteGenerator::validate($this->config);
            if ($input->getOption('local')) {
                $this->build('local');
                $output->writeln('<info>generated local site</info>');
            }
            if ($input->getOption('public')) {
                $this->build('public');
                $output->writeln('<info>generated public (github.io) site</info>');
            }
            if (!$input->getOption('local') && !$input->getOption('public')) {
                $this->build('local');
                $this->build('public');
                $output->writeln('<info>generated local & public site</info>');
            }
            $result = 0;
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            $result = 1;
        } finally {
            Util::clearDirectory($this->config->path('cache'));
        }
        return $result;
    }

    /**
     * build static site
     * @param  string $type accepts public or local
     * @throws \InvalidArgumentException
     */
    private function build(string $type)
    {
        if (!in_array($type, ['public', 'local'])) {
            throw new \InvalidArgumentException('type accepts public or local');
        }
        if (!$this->called) {
            $url = $type === 'public' ? $this->config->env['public'] : $this->config->env['local'];
            $url = rtrim($url, '/');
            Util::clearDirectory($this->config->path('cache'));
            $articles        = ArticleGenerator::getArticles($this->config->path('article'), $url);
            $paths = $this->config->paths;
            $articles->each(function ($article) use ($paths) {
                $article->cache($paths['article'], "{$paths['cache']}/article");
            });
            $this->generator = new SiteGenerator($this->config, $articles);
            $this->called    = true;
        }
        Util::clearDirectory($this->config->path($type), true);
        $this->generator->generate($type);
    }
}
