<?php
namespace App\Command;

use App\Game\Contracts\TurnDecisionMakerInterface;
use App\Game\Implementation\TurnDecisionMakers\AlgorithmicTurnDecisionMaker;
use App\Game\Implementation\StateAnalysis\DangerDetector;

class PlayHumanVsAlgorithm extends AbstractPlayCommand
{
    protected static $defaultName = 'play:algo';

    protected function configure()
    {
        $this->setDescription('Play with algorithm');
    }

    protected function getTurnDecisionMaker(): TurnDecisionMakerInterface
    {
        $dangerDetector = new DangerDetector($this->getParameters());
        return new AlgorithmicTurnDecisionMaker($dangerDetector);
    }
}