<?php
namespace Taniko\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Taniko\Saori\Util;

class DraftCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('draft')
            ->setDescription('Generate a draft file of the article')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'draft name'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name') ?? 'temp';
        $dir    =   "{$this->root}/draft/{$name}";
        try {
            if (preg_match('/^[\w-_]+$/', $name) !== 1) {
                $str  = "includes characters that cannot be used\n";
                $str .= "<comment>please enter a valid characters(a-z A-z 0-9 _ -)</comment>";
                throw new \Exception($str);
            } elseif (file_exists($dir)) {
                throw new \Exception("draft/{$name} already exists");
            }
            Util::putContents("{$dir}/article.md", '');
            Util::putYamlContents("{$dir}/config.yml", [
                "title" =>  (string)$name,
                "tag"   =>  []
            ]);
            $output->writeln("<info>generate (draft/{$name})</info>");
            $result = 0;
        } catch (\Exception $e) {
            $result = 1;
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
        return $result;
    }
}
