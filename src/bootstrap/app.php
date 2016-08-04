<?php
use Hrgruri\Saori\Console\{
    InitCommand,
    DraftCommand,
    PostCommand,
    MakeCommand,
    BuildCommand
};
use Symfony\Component\Console\Application;

$path =  __DIR__.'/../../';
$app = new Application();

$app->add(new InitCommand($path));
$app->add(new DraftCommand($path));
$app->add(new PostCommand($path));
$app->add(new BuildCommand($path));

return $app;
