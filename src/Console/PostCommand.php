<?php
namespace Taniko\Saori\Console;

use Taniko\Saori\Util;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        if (is_null($input->getArgument('title'))) {
            $date  = date('dHi');
            $title = file_exists("{$this->root}/draft/temp") ? 'temp' : $date;
            $dest  = "{$this->paths['article']}/" . date('Y/m') . "/{$date}";
        } else {
            $title  = $input->getArgument('title');
            $dest   = "{$this->paths['article']}/". date('Y/m') . "/{$title}";
        }
        $source = "{$this->root}/draft/{$title}";

        try {
            if (!$this->validateTitle($title)) {
                $str  = "includes characters that cannot be used\n";
                $str .= '<comment>please enter a valid characters(a-z A-z 0-9 _ -)</comment>';
                throw new \Exception($str);
            } elseif (is_dir($dest)) {
                throw new \Exception("{$dest} is already exist");
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
                Util::copyDirectory($source, $dest);

                if (file_exists("{$dest}/config.yml")) {
                    $data = Util::getYamlContents("{$dest}/config.yml");
                    $data['timestamp'] = time();
                    Util::putYamlContents("{$dest}/config.yml", $data, true);
                } else {
                    $this->touchArticleConfig($dest, $title);
                }
                Util::clearDirectory($source);
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
        return Util::putYamlContents(
            "{$dest}/config.yml",
            [
                "title"     =>  (string)$title,
                "tag"       =>  [],
                "timestamp"  =>  time()
            ],
            true
        );
    }

    private function validateTitle(string $title) : bool
    {
        return (preg_match('/^[\w\-_]+$/', $title) === 1);
    }
}
