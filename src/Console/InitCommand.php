<?php
namespace Taniko\Saori\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Taniko\Saori\Util;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Initialize')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (is_dir($this->paths['local'])) {
                throw new \Exception("directory({$this->paths['local']}) already exists");
            } elseif (is_dir($this->paths['public'])) {
                throw new \Exception("directory({$this->paths['public']}) already exists");
            } elseif (is_dir($this->paths['contents'])) {
                throw new \Exception("directory({$this->paths['contents']}) already exists");
            }
            Util::putYamlContents(
                "{$this->root}/config/env.yml",
                [
                    'title' =>  'Sample Blog',
                    'author'=>  'John Doe',
                    'local' =>  'http://localhost:8000',
                    'public'=>  'http://localhost:8000',
                    'theme' =>  'saori',
                    'lang'  =>  'en',
                    'link'  =>  [
                        'GitHub'    =>  'https://github.com',
                        'Twitter'   =>  'https://twitter.com'
                    ],
                    'feed'  =>  [
                        'type'      =>  'atom',
                        'number'    =>  50
                    ]
                ]
            );
            $output->writeln('<info>done</info>');
            $result = 0;
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getTraceAsString()}</error>");
            $result = 1;
        }
        return $result;
    }
}
