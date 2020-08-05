<?php
namespace App\Game\Implementation\ConsoleHelper;

use App\Game\Contracts\GameStateMachineInterface;
use App\Game\Implementation\GameStateMachine;
use App\Game\ValueObject\FieldPoint;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class FieldStateRenderer
{
    public function renderFieldState(GameStateMachineInterface $game, OutputInterface $output): void
    {
        $table = new Table($output);

        $fieldData = [];

        $params = $game->getParameters();

        for ($y = 0; $y < $params->getFieldHeight(); $y++) {
            $fieldData[$y] = array_fill(0, $params->getFieldWidth(), '');
            for ($x = 0; $x < $params->getFieldWidth(); $x++) {
                $point = FieldPoint::fromCoords($x, $y);

                $fieldData[$y][$x] = sprintf('<fg=blue>%d</>', $point->getCellIndex($params->getFieldWidth()));

                if ($game->getXPlayerField()->isOccupied($point)) {
                    $fieldData[$y][$x] = GameStateMachine::X;
                }

                if ($game->getOPlayerField()->isOccupied($point)) {
                    $fieldData[$y][$x] = GameStateMachine::O;
                }
            }

            $table->addRow($fieldData[$y]);
            if ($y < $params->getFieldHeight() - 1) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();

        $output->writeln('');
    }
}