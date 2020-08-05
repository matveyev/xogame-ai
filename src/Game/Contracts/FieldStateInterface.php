<?php
namespace App\Game\Contracts;

use App\Game\ValueObject\FieldPoint;

interface FieldStateInterface
{
    public function isOccupied(FieldPoint $point): bool;

    public function setOccupied(FieldPoint $point, bool $isOccupied = true): void;

    public function getAsVector(): array;

    public function getVacantIndexes(): array;

    public function getWidth(): int;

    public function getHeight(): int;
}