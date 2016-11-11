<?php
namespace Hrgruri\Saori\Console;

use Hrgruri\Saori\SiteGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeCommand extends Command
{
    private $dir = __DIR__ . '/../theme';
    protected function configure()
    {
        $this
            ->setName('theme')
            ->setDescription('Check theme infomation')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Theme name'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $theme  = $input->getArgument('name');
        $list   = $this->getThemeList();
        if (is_null($theme)) {
            $output->writeln('<info>Theme list</info>');
            $output->writeln('<info>' . implode(', ', $list) .'</info>');
        } elseif (!file_exists("{$this->dir}/{$theme}")) {
            $output->writeln("<comment>{$theme} was not found</comment>");
        } elseif (file_exists("{$this->dir}/{$theme}/theme.json")) {
            $output->writeln("<info>{$theme}/theme.json</info>");
            $output->writeln('<info>' . file_get_contents("{$this->dir}/{$theme}/theme.json") .'</info>');
        } else {
            $output->writeln("<info>{$theme} has not theme.json</info>");
        }
    }

    private function getThemeList() : array
    {
        $result = [];
        if (is_dir($this->dir) && ($dh = opendir($this->dir))) {
            while (($file = readdir($dh)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                } elseif (is_dir("{$this->dir}/{$file}")) {
                    $result[] = $file;
                }
            }
            closedir($dh);
        }
        return $result;
    }
}
