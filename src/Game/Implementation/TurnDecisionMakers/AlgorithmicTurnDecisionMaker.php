<?php

namespace App\Game\Implementation\TurnDecisionMakers;

use App\Game\Contracts\DangerousSituationDetectorInterface;
use App\Game\Contracts\FieldStateInterface;
use App\Game\Contracts\TurnDecisionMakerInterface;
use App\Game\ValueObject\FieldPoint;
use App\Game\Contracts\GameParametersInterface;

class AlgorithmicTurnDecisionMaker implements TurnDecisionMakerInterface
{
    /** @var DangerousSituationDetectorInterface */
    private $dangerDetector;

    public function __construct(DangerousSituationDetectorInterface $dangerDetector)
    {
        $this->dangerDetector = $dangerDetector;
    }

    public function getTurnPoint(
        FieldStateInterface $myField,
        FieldStateInterface $enemyField,
        GameParametersInterface $gameParameters
    ): FieldPoint {
        if ($this->dangerDetector->isDangerousSituation($enemyField, $myField)) {
            return $this->dangerDetector->getDangerousPoint();
        }

        if ($this->dangerDetector->isDangerousSituation($myField, $enemyField)) {
            return $this->dangerDetector->getDangerousPoint();
        }

        $vacantPlaces = array_intersect($myField->getVacantIndexes(), $enemyField->getVacantIndexes());

        return FieldPoint::fromCellNumber(
            $vacantPlaces[array_rand($vacantPlaces,1)],
            $gameParameters->getFieldWidth()
        );
    }
}