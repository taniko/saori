<?php
namespace Hrgruri\Saori\Console;

use Hrgruri\Saori\SiteGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('page')
            ->setDescription('Generate page file')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'page path'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name   = rtrim(ltrim($input->getArgument('path'), '/'), '/');
        $path   = $this->paths['contents'].'/page/'.$name;
        try {
            if (preg_match('/(.+)\/(\w+)$/', $path, $matched) !== 1) {
                throw new \Exception("includes characters that cannot be used\n<comment>please enter a valid characters(a-z A-z 0-9 _ -)</comment>");
            } elseif (file_exists("{$path}.md")) {
                throw new \Exception("{$input->getArgument('path')} already exists");
            }
            $this->mkdir($matched[1]);
            touch("{$path}.md");
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
