<?php
namespace Taniko\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeCommand extends Command
{
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
        $list   = $this->config->themes;
        $names  = array_keys($list);
        if (is_null($theme)) {
            $output->writeln('<info>Theme list</info>');
            $output->writeln('<info>' . implode(', ', $names) .'</info>');
        } elseif (!in_array($theme, $names)) {
            $output->writeln("<comment>{$theme} was not found</comment>");
        } elseif (file_exists("{$list[$theme]}/setting.yml")) {
            $output->writeln("<info># {$theme}/setting.yml\n</info>");
            $output->writeln('<info>' . file_get_contents("{$list[$theme]}/setting.yml") .'</info>');
        } else {
            $output->writeln("<info>{$theme} has not setting.yml</info>");
        }
    }
}
