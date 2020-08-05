<?php

namespace App\Command;

use App\Game\Contracts\GameParametersInterface;
use App\Game\Contracts\GameStateMachineInterface;
use App\Game\Contracts\TurnDecisionMakerInterface;
use App\Game\Implementation\ConsoleHelper\FieldStateRenderer;
use App\Game\Implementation\ConsoleHelper\GameDumper;
use App\Game\Implementation\GameStateMachine;
use App\Game\Implementation\StateAnalysis\VictoryDetector;
use App\Game\ValueObject\FieldPoint;
use App\Game\ValueObject\GameParameters3x3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class AbstractPlayCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $input->setInteractive(true);

        $fieldRenderer = new FieldStateRenderer();
        $questionHelper = $this->getHelper('question');

        $whoFirstQuestion = new ChoiceQuestion(
            'Who plays first? ',
            [GameStateMachineInterface::X, GameStateMachineInterface::O],
            GameStateMachineInterface::X
        );
        $firstPlayer = $questionHelper->ask($input,$output,$whoFirstQuestion);

        $victoryDetector = new VictoryDetector($this->getParameters());
        $game = new GameStateMachine($victoryDetector, $this->getParameters());

        $decisionMaker = $this->getTurnDecisionMaker();

        $game->startGame($firstPlayer);
        while (!$game->isGameFinished()) {
            $output->writeln('Current player: ' . $game->getAllowedToTurnPlayer());
            $fieldRenderer->renderFieldState($game, $output);

            if ($game->getAllowedToTurnPlayer() === GameStateMachineInterface::X) {
                $question = new Question('Input cell number: ', 0);
                $cellNumber = (int)$questionHelper->ask($input, $output, $question);
                $point = FieldPoint::fromCellNumber($cellNumber, $this->getParameters()->getFieldWidth());
            } else {
                $point = $decisionMaker->getTurnPoint($game->getOPlayerField(), $game->getXPlayerField(), $game->getParameters());
            }

            try {
                $game->turn($point);
            } catch (\RuntimeException $e) {
                $output->writeln($e->getMessage());

                continue;
            }

            $this->turnCallback($game, $input, $output);
        }

        $this->gameOverCallback($game, $output);

        return 0;
    }

    abstract protected function getTurnDecisionMaker(): TurnDecisionMakerInterface;

    protected function turnCallback(
        GameStateMachineInterface $game,
        InputInterface $input,
        OutputInterface $output
    ): void {
        // nop
    }

    protected function getParameters(): GameParametersInterface
    {
        static $gameParams;

        if (is_null($gameParams)) {
            $gameParams = new GameParameters3x3();
        }

        return $gameParams;
    }

    /**
     * @param GameStateMachine $game
     * @param OutputInterface $output
     */
    protected function gameOverCallback(GameStateMachine $game, OutputInterface $output): void
    {
        (new GameDumper())->dumpGameTurns($game, $output);

        if ($game->isDraw()) {
            $output->writeln('Game over: DRAW!');
        } else {
            $output->writeln('Congratulations, ' . $game->getWinner() . ' won!!!');
        }
    }
}