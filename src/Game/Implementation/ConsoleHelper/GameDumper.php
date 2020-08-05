<?php
namespace App\Game\Implementation\ConsoleHelper;

use App\Game\Contracts\GameStateMachineInterface;
use App\Game\Implementation\GameStateMachine;
use App\Game\ValueObject\FieldPoint;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class GameDumper
{
    public function dumpGameTurns(GameStateMachineInterface $game, OutputInterface $output): void
    {
        $blankField = [];

        $params = $game->getParameters();

        for ($y = 0; $y < $params->getFieldHeight(); $y++) {
            $blankField[$y] = array_fill(0, $params->getFieldWidth(), '');
            for ($x = 0; $x < $params->getFieldWidth(); $x++) {
                $point = FieldPoint::fromCoords($x, $y);

                $blankField[$y][$x] = sprintf('<fg=blue>%d</>', $point->getCellIndex($params->getFieldWidth()));
            }
        }

        for ($turn = 0; $turn < $game->getTurnCount(); $turn++) {
            $snapshot = $game->getTurnSnapshot($turn);

            $output->writeln(sprintf('Turn #%d, %s turns: ', $turn, $snapshot->getTurnAuthor()));

            $fieldData = array_merge([], $blankField);

            $table = new Table($output);

            for ($y = 0; $y < $params->getFieldHeight(); $y++) {
                for ($x = 0; $x < $params->getFieldWidth(); $x++) {
                    $point = FieldPoint::fromCoords($x, $y);
                    if ($snapshot->getXPlayerFieldState()->isOccupied($point)) {
                        $fieldData[$y][$x] = GameStateMachine::X;
                    }
                    if ($snapshot->getOPlayerFieldState()->isOccupied($point)) {
                        $fieldData[$y][$x] = GameStateMachine::O;
                    }
                }

                $turnPoint = $snapshot->getTurnCoordinate();
                $fieldData[$turnPoint->getY()][$turnPoint->getX()] = '<fg=green>' . $snapshot->getTurnAuthor() . '</>';

                $table->addRow($fieldData[$y]);
                if ($y < $params->getFieldHeight() - 1) {
                    $table->addRow(new TableSeparator());
                }
            }

            $table->render();

            $output->writeln('');
        }
    }
}