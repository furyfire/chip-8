<?php
include "vendor/autoload.php";

use Symfony\Component\Console\Application;

use furyfire\chip8\Command\TextCommand;

$application = new Application();
$application->add(new TextCommand());
$application->run();
