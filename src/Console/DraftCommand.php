<?php
namespace Hrgruri\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
                throw new \Exception("includes characters that cannot be used\n<comment>please enter a valid characters(a-z A-z 0-9 _ -)</comment>");
            } elseif (file_exists($dir)) {
                throw new \Exception("draft/{$name} already exists");
            }
            mkdir($dir, 0700, true);
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
            $output->writeln("<info>generate (draft/{$name})</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
