<?php
namespace App\Game\Implementation\TurnDecisionMakers;

use App\Game\Contracts\FieldStateInterface;
use App\Game\Contracts\TurnDecisionMakerInterface;
use App\Game\ValueObject\FieldPoint;
use App\Game\Contracts\GameParametersInterface;

class RandomDecisionMaker implements TurnDecisionMakerInterface
{
    public function getTurnPoint(
        FieldStateInterface $myField,
        FieldStateInterface $enemyField,
        GameParametersInterface $gameParameters
    ): FieldPoint {
        $vacantPlaces = array_intersect($myField->getVacantIndexes(), $enemyField->getVacantIndexes());

        return FieldPoint::fromCellNumber(
            $vacantPlaces[array_rand($vacantPlaces,1)],
            $gameParameters->getFieldWidth()
        );
    }
}