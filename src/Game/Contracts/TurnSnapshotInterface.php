<?php

namespace App\Game\Contracts;

use App\Game\Contracts\FieldStateInterface;
use App\Game\ValueObject\FieldPoint;

interface TurnSnapshotInterface
{
    public function getXPlayerFieldState(): FieldStateInterface;

    public function getOPlayerFieldState(): FieldStateInterface;

    public function getTurnAuthor(): string;

    public function getTurnCoordinate(): FieldPoint;
}