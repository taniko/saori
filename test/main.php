<?php
require (__DIR__.'/../vendor/autoload.php');
$saori = new Hrgruri\Saori\Saori(__DIR__);
$saori->run($argv);
