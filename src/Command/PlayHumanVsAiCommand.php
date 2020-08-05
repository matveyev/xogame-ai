<?php

namespace App\Command;

use App\Game\Contracts\GameStateMachineInterface;
use App\Game\Contracts\TurnDecisionMakerInterface;
use App\Game\Implementation\StateAnalysis\DangerDetector;
use App\Game\Implementation\TurnDecisionMakers\AiTurnDecisionMaker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlayHumanVsAiCommand extends AbstractPlayCommand
{
    protected static $defaultName = 'play:ai';

    /** @var DangerDetector  */
    private $dangerDetector;

    /** @var AiTurnDecisionMaker  */
    private $decisionMaker;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->dangerDetector = new DangerDetector($this->getParameters());
        $this->decisionMaker = new AiTurnDecisionMaker("xo.net");
    }

    protected function configure()
    {
        $this->setDescription('Play with AI');
    }

    protected function getTurnDecisionMaker(): TurnDecisionMakerInterface
    {
        return $this->decisionMaker;
    }

    protected function turnCallback(GameStateMachineInterface $game, InputInterface $input, OutputInterface $output): void
    {
        $snapshot = $game->getTurnSnapshot($game->getTurnCount() - 1);

        switch ($snapshot->getTurnAuthor()) {
            case GameStateMachineInterface::X:
                $myField = $snapshot->getXPlayerFieldState();
                $enemyField = $snapshot->getOPlayerFieldState();
                break;
            case GameStateMachineInterface::O:
                $myField = $snapshot->getOPlayerFieldState();
                $enemyField = $snapshot->getXPlayerFieldState();
                break;
        }

        $turnQuality = $this->decisionMaker->estimateTurnQuality($myField, $enemyField, $snapshot->getTurnCoordinate());

        $output->writeln(sprintf(
            '<fg=yellow>AI estimates this turn quality as %.1f%%</>',
            ($turnQuality + 1.0) / 2.0 * 100.0
        ));

        if ($this->dangerDetector->isDangerousSituation($game->getOPlayerField(), $game->getXPlayerField())) {
            $output->writeln('<fg=yellow>O is in danger</>');
        }

        if ($this->dangerDetector->isDangerousSituation($game->getXPlayerField(), $game->getOPlayerField())) {
            $output->writeln('<fg=yellow>X is in danger</>');
        }

        $output->writeln('');
    }
}
