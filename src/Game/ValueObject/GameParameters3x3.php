<?php
namespace App\Game\ValueObject;

use App\Game\Contracts\GameParametersInterface;

final class GameParameters3x3 implements GameParametersInterface
{
    public function getFieldWidth(): int
    {
        return 3;
    }

    public function getFieldHeight(): int
    {
        return 3;
    }

    public function getWinningStripeLength(): int
    {
        return 3;
    }
}