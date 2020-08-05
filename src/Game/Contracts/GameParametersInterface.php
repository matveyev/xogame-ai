<?php
namespace App\Game\Contracts;

interface GameParametersInterface
{
    public function getFieldWidth(): int;

    public function getFieldHeight(): int;

    public function getWinningStripeLength(): int;
}