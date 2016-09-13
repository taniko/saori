<?php
namespace Hrgruri\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            mkdir($this->paths['contents'], 0700, true);
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
                        ],
                        'feed'  =>  [
                            'type'      =>  'atom',
                            'number'    =>  50
                        ]
                    ],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );
            $output->writeln('<info>done</info>');
            $result = 0;
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            $result = 1;
        }
        return $result;
    }
}
