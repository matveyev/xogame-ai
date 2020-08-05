<?php

namespace App\Game\Implementation\TurnDecisionMakers;

use App\Game\Contracts\FieldStateInterface;
use App\Game\Contracts\GameParametersInterface;
use App\Game\Contracts\TurnDecisionMakerInterface;
use App\Game\Implementation\GameStateMachine;
use App\Game\ValueObject\FieldPoint;

class AiTurnDecisionMaker implements TurnDecisionMakerInterface
{
    /** @var resource */
    private $ann;

    public function __construct(string $filename)
    {
        $this->ann = \fann_create_from_file($filename);
    }

    public function getTurnPoint(
        FieldStateInterface $myField,
        FieldStateInterface $enemyField,
        GameParametersInterface $gameParameters
    ): FieldPoint {
        $input = array_merge($myField->getAsVector(), $enemyField->getAsVector());

        $output = \fann_run($this->ann, $input);

        $vacantPlaces = array_intersect($myField->getVacantIndexes(), $enemyField->getVacantIndexes());

        $maxIndex = reset($vacantPlaces);
        $max = $output[$maxIndex];

        foreach ($vacantPlaces as $index) {
            if ($output[$index] > $max) {
                $max = $output[$index];
                $maxIndex = $index;
            }
        }

        return FieldPoint::fromCellNumber($maxIndex, $gameParameters->getFieldWidth());
    }

    public function estimateTurnQuality(
        FieldStateInterface $myField,
        FieldStateInterface $enemyField,
        FieldPoint $point
    ): float {
        $input = array_merge($myField->getAsVector(), $enemyField->getAsVector());

        $output = \fann_run($this->ann, $input);

        return $output[$point->getCellIndex($myField->getWidth())];
    }

    public function __destruct()
    {
        \fann_destroy($this->ann);
    }
}