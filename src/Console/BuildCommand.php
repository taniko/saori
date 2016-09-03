<?php
namespace Hrgruri\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Hrgruri\Saori\SiteGenerator;
use hrgruri\saori\exception\GeneratorException;

class BuildCommand extends Command
{
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
        $this->loadConfig();
        $this->generator = new SiteGenerator(
            $this->paths,
            $this->config,
            $this->theme_config,
            $this->ut_config
        );
        $this->clearDirectory($this->paths['cache']);
        try {
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
        } catch (GeneratorException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        } finally {
            $this->clearDirectory($this->paths['cache']);
        }
    }

    private function build(string $type)
    {
        $this->loadConfig();
        $this->clearDirectory($this->paths[$type], true);
        $this->generator->generate(
            $this->config->{$type},
            $this->paths[$type],
            $type === 'public' ? true : false
        );
    }
}
