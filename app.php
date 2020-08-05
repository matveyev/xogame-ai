<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \App\Command\PlayHumanVsAiCommand());
$application->add(new \App\Command\PlayHumanVsRandomCommand());
$application->add(new \App\Command\PlayHumanVsAlgorithm());
$application->add(new \App\Command\PrepareTrainDataCommand());
$application->add(new \App\Command\TrainCommand());

$application->run();