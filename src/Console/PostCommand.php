<?php
namespace Hrgruri\Saori\Console;

use hrgruri\saori\SiteGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PostCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('post')
            ->setDescription('Generate article file')
            ->addArgument(
                'title',
                InputArgument::OPTIONAL,
                'article title'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $title = $input->getArgument('title') ?? date('dHi');
        $dir        = "{$this->paths['article']}/". date('Y/m')."/{$title}";
        try {
            if (preg_match('/^[\w-_]+$/', $title) !== 1) {
                throw new \Exception("includes characters that cannot be used\n<comment>please enter a valid characters(a-z A-z 0-9 _ -)</comment>");
            } elseif (is_dir($dir)) {
                throw new \Exception("this title/({$title}) already exist");
            }
            $output->writeln('<info>generate article file ('.date('Y/m'). "{$title})</info>");
            if (file_exists("{$this->root}/draft/{$title}")) {
                SiteGenerator::copyDirectory("{$this->root}/draft/{$title}", $dir);
                $this->clearDirectory("{$this->root}/draft/{$title}");
                rmdir("{$this->root}/draft/{$title}");
            } else {
                mkdir($dir, 0700, true);
                touch("{$dir}/article.md");
            }
            file_put_contents(
                "{$dir}/config.json",
                json_encode(
                    [
                        "title"     =>  (string)$title,
                        "tag"       =>  [],
                        "timestamp"  =>  time()
                    ],
                    JSON_PRETTY_PRINT
                )
            );
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
