<?php
namespace App\Game\Contracts;

use App\Game\ValueObject\FieldPoint;
use App\Game\Contracts\GameParametersInterface;

interface TurnDecisionMakerInterface
{
    public function getTurnPoint(
        FieldStateInterface $myField,
        FieldStateInterface $enemyField,
        GameParametersInterface $gameParameters
    ): FieldPoint;
}