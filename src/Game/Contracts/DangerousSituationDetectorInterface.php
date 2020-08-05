<?php
namespace App\Game\Contracts;

use App\Game\ValueObject\FieldPoint;

interface DangerousSituationDetectorInterface
{
    public function isDangerousSituation(
        FieldStateInterface $defendantField,
        FieldStateInterface $enemyField
    ): bool;

    public function getDangerousPoint(): FieldPoint;
}