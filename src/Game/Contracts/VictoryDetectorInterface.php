<?php
namespace App\Game\Contracts;

interface VictoryDetectorInterface
{
    public function isVictory(FieldStateInterface $fieldState): bool;
}