<?php

namespace App\Command;

use App\Game\Contracts\TurnDecisionMakerInterface;
use App\Game\Implementation\TurnDecisionMakers\RandomDecisionMaker;

class PlayHumanVsRandomCommand extends AbstractPlayCommand
{
    protected static $defaultName = 'play:random';

    protected function configure()
    {
        $this->setDescription('Play with random generator');
    }

    protected function getTurnDecisionMaker(): TurnDecisionMakerInterface
    {
        return new RandomDecisionMaker();
    }
}