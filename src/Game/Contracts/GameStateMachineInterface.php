<?php

namespace App\Game\Contracts;

use App\Game\ValueObject\FieldPoint;

interface GameStateMachineInterface
{
    public const X = 'X';
    public const O = 'O';

    public function startGame(string $firstPlayerType): void;

    public function isGameFinished(): bool;

    public function getAllowedToTurnPlayer(): string;

    public function getWinner(): string;

    public function isDraw(): bool;

    public function getTurnCount(): int;

    public function getTurnSnapshot(int $turnNumber): TurnSnapshotInterface;

    public function turn(FieldPoint $point): TurnSnapshotInterface;

    public function getXPlayerField(): FieldStateInterface;

    public function getOPlayerField(): FieldStateInterface;

    public function getParameters(): GameParametersInterface;
}