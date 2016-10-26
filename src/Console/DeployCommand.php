<?php
namespace Hrgruri\Saori\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Hrgruri\Saori\SiteGenerator;
use hrgruri\saori\exception\GeneratorException;

class DeployCommand extends Command
{
    private $generator;

    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Deploy to github.io')
            ->addArgument(
                'message',
                InputArgument::IS_ARRAY,
                'commit message'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result  = 0;
        $message = count($input->getArgument('message')) > 0
            ? implode($input->getArgument('message'), ' ')
            : 'deploy ' . date('YmdHi');
        $cwd      = getcwd();
        $commands = [
            'git add --all',
            "git commit --message '{$message}'",
            'git push origin master'
        ];
        try {
            $config = $this->getBlogConfig();
            if (!isset($config->id)) {
                throw new \Exception('undefined id on contents/config.json');
            }
            chdir("{$cwd}/{$config->id}.github.io");
            print getcwd()."\n";
            foreach ($commands as $key => $command) {
                exec($command, $lines, $val);
                if ($val != 0) {
                    throw new \Exception(implode($lines, "\n"), $key);
                } else {
                    $output->writeln('<info>'. implode("\n", $lines) . '<info>');
                }
            }
        } catch (\Exception $e) {
            if ($e->getCode() == 1) {
                $msg = "<info>{$e->getMessage()}</info>";
            } else {
                $msg = "<error>{$e->getMessage()}</error>";
            }
            $output->writeln($msg);
            $result = 1;
        } finally {
            chdir($cwd);
        }
        return $result;
    }
}
