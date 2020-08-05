<?php
namespace App\Command;

use App\Game\Contracts\GameStateMachineInterface;
use App\Game\Implementation\GameStateMachine;
use App\Game\Implementation\StateAnalysis\VictoryDetector;
use App\Game\Implementation\TurnDecisionMakers\RandomDecisionMaker;
use App\Game\ValueObject\FieldPoint;
use App\Game\ValueObject\FieldState;
use App\Game\ValueObject\GameParameters3x3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareTrainDataCommand extends Command
{
    protected static $defaultName = 'ai:prepare';

    protected function configure()
    {
        $this->setDescription('Prepare training data');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $params = new GameParameters3x3();
        $victoryDetector = new VictoryDetector($params);
        $game = new GameStateMachine($victoryDetector, $params);

        $randomDecisionMaker = new RandomDecisionMaker();

        /** @var FieldState[] $myFields */
        $myFields = [];
        /** @var FieldState[] $enemyFields */
        $enemyFields = [];
        /** @var int[] $outFields */
        $outFields = [];

        $fieldCellCount = $params->getFieldWidth() * $params->getFieldHeight();

        for ($startPoint = 0; $startPoint < $fieldCellCount; $startPoint++) {
            $count = 12000;
            while ($count--) {
                $isFirstStep = true;
                $game->startGame(GameStateMachineInterface::X);
                while (!$game->isGameFinished()) {
                    if ($game->getAllowedToTurnPlayer() === GameStateMachineInterface::X) {
                        if ($isFirstStep) {
                            $isFirstStep = false;
                            $point = FieldPoint::fromCellNumber($startPoint, $params->getFieldWidth());
                        } else {
                            $point = $randomDecisionMaker->getTurnPoint(
                                $game->getXPlayerField(),
                                $game->getOPlayerField(),
                                $game->getParameters()
                            );
                        }
                    } else {
                        $point = $randomDecisionMaker->getTurnPoint(
                            $game->getOPlayerField(),
                            $game->getXPlayerField(),
                            $game->getParameters()
                        );
                    }

                    try {
                        $game->turn($point);
                    } catch (\RuntimeException $e) {
                        $output->writeln($e->getMessage());

                        return 1;
                    }
                }

                if (!$game->isDraw()) {
                    for ($i = 0; $i < $game->getTurnCount(); $i++) {
                        $snapshot = $game->getTurnSnapshot($i);
                        if ($snapshot->getTurnAuthor() === $game->getWinner()) {
                            $incr = 1;
                        } else {
                            $incr = -1;
                        }

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

                        $hash = md5(implode('', $myField->getAsVector()) . implode('', $enemyField->getAsVector()));

                        if (!isset($myFields[$hash])) {
                            $myFields[$hash] = $myField;
                        }

                        if (!isset($enemyFields[$hash])) {
                            $enemyFields[$hash] = $enemyField;
                        }

                        if (!isset($outFields[$hash])) {
                            $outFields[$hash] = array_fill(0, $fieldCellCount, 0);
                        }

                        $outFields[$hash][$snapshot->getTurnCoordinate()->getCellIndex($params->getFieldWidth())] += $incr;
                    }
                }
            }
        }

        $count   = count($outFields);
        $content = sprintf("%d %d %d\n", $count, $fieldCellCount * 2, $fieldCellCount);

        foreach ($outFields as $hash => $out) {
            $max = max(max($out), abs(min($out)));

            if ($max == 0) {
                $max = 1;
            }

            $outN = array_map(
                function (int $n) use ($max) { return $n / $max; },
                $out
            );

            $content .= sprintf(
                "%s %s\n%s\n",
                $myFields[$hash]->__toString(),
                $enemyFields[$hash]->__toString(),
                implode(' ', $outN)
            );
        }

        file_put_contents('train_data.txt', $content);

        $output->writeln(sprintf('Generated %d turns', $count));

        return 0;
    }
}