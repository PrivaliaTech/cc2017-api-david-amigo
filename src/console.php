<?php

set_time_limit(0);

use App\Command\TestAlgorithmCommand;
use Symfony\Component\Console\Application;

$console = new Application();

$console->add(new TestAlgorithmCommand());
$console->run();
