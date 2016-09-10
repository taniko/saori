<?php
namespace Hrgruri\Saori\Console;

use Hrgruri\Saori\SiteGenerator;
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
        $title  = $input->getArgument('title') ?? date('dHi');
        $source = "{$this->root}/draft/{$title}";
        $dest   = "{$this->paths['article']}/". date('Y/m')."/{$title}";

        try {
            if (preg_match('/^[\w-_]+$/', $title) !== 1) {
                throw new \Exception("includes characters that cannot be used\n<comment>please enter a valid characters(a-z A-z 0-9 _ -)</comment>");
            } elseif (is_dir($dest)) {
                throw new \Exception("this title({$title}) already exist");
            }
            if (file_exists($source)) {
                if ($title === 'temp') {
                    $source = "{$this->root}/draft/temp";
                    $dest   = "{$this->paths['article']}/".date('Y/m/dHi');
                    $title  = date('dHi');
                }
                if (file_exists($dest)) {
                    throw new \Exception("this title({$title}) already exist");
                }
                SiteGenerator::copyDirectory($source, $dest);

                if (file_exists("{$dest}/config.json")) {
                    $data = SiteGenerator::loadJson("{$dest}/config.json");
                    $data->timestamp = time();
                    file_put_contents(
                        "{$dest}/config.json",
                        json_encode(
                            $data,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                        )
                    );
                } else {
                    $this->touchArticleConfig($dest, $title);
                }
                $this->clearDirectory($source);
                rmdir($source);
            } else {
                mkdir($dest, 0700, true);
                touch("{$dest}/article.md");
                $this->touchArticleConfig($dest, $title);
            }
            $output->writeln('<info>generate article file (contents/article/'.date('Y/m'). "/{$title})</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }

    private function touchArticleConfig(string $dest, string $title)
    {
        file_put_contents(
            "{$dest}/config.json",
            json_encode(
                [
                    "title"     =>  (string)$title,
                    "tag"       =>  [],
                    "timestamp"  =>  time()
                ],
                JSON_PRETTY_PRINT
            )
        );
    }
}
