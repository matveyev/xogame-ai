<?php
namespace App\Game\ValueObject;

use App\Game\Contracts\GameParametersInterface;

final class GameParameters5x5 implements GameParametersInterface
{
    public function getFieldWidth(): int
    {
        return 5;
    }

    public function getFieldHeight(): int
    {
        return 5;
    }

    public function getWinningStripeLength(): int
    {
        return 5;
    }
}