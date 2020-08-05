<?php
namespace App\Game\Implementation;

use App\Game\Contracts\DangerousSituationDetectorInterface;
use App\Game\Contracts\FieldStateInterface;
use App\Game\Contracts\GameParametersInterface;
use App\Game\Contracts\GameStateMachineInterface;
use App\Game\Contracts\TurnSnapshotInterface;
use App\Game\Contracts\VictoryDetectorInterface;
use App\Game\ValueObject\FieldPoint;
use App\Game\ValueObject\FieldState;
use App\Game\ValueObject\TurnSnapshot;

class GameStateMachine implements GameStateMachineInterface
{
    /**
     * States
     */
    private const STATE_NOT_STARTED = 0;
    private const STATE_GAME  = 1;
    private const STATE_X_WON = 2;
    private const STATE_O_WON = 3;
    private const STATE_DRAW  = 4;

    /** @var int */
    private $currentState = self::STATE_NOT_STARTED;

    /** @var GameParametersInterface */
    private $gameParams;

    /** @var string */
    private $currentPlayerType;

    /** @var TurnSnapshotInterface[] */
    private $turnHistory;

    /** @var FieldStateInterface */
    private $fieldX;

    /** @var FieldStateInterface */
    private $fieldO;

    /** @var VictoryDetectorInterface */
    private $victoryDetector;

    public function __construct(
        VictoryDetectorInterface $victoryDetector,
        GameParametersInterface $gameParameters
    ) {
        $this->victoryDetector = $victoryDetector;
        $this->gameParams = $gameParameters;
    }

    public function startGame(string $firstPlayerType): void
    {
        $this->currentPlayerType = $firstPlayerType;
        $this->turnHistory = [];
        $this->currentState = self::STATE_GAME;

        $params = $this->getParameters();
        $this->fieldX = new FieldState($params->getFieldWidth(), $params->getFieldHeight());
        $this->fieldO = new FieldState($params->getFieldWidth(), $params->getFieldHeight());
    }

    public function isGameFinished(): bool
    {
        return self::STATE_GAME !== $this->currentState;
    }

    public function getAllowedToTurnPlayer(): string
    {
        if ($this->isGameFinished()) {
            throw new \RuntimeException('Game is not active');
        }

        return $this->currentPlayerType;
    }

    public function getWinner(): string
    {
        if (self::STATE_X_WON === $this->currentState) {
            return GameStateMachineInterface::X;
        }

        if (self::STATE_O_WON === $this->currentState) {
            return GameStateMachineInterface::O;
        }

        throw new \RuntimeException('There is no winner');
    }

    public function isDraw(): bool
    {
        return self::STATE_DRAW === $this->currentState;
    }

    public function getTurnCount(): int
    {
        return count($this->turnHistory);
    }

    public function getTurnSnapshot(int $turnNumber): TurnSnapshotInterface
    {
        if (!array_key_exists($turnNumber, $this->turnHistory)) {
            throw new \RuntimeException('Invalid turn number: ' . $turnNumber);
        }

        return $this->turnHistory[$turnNumber];
    }

    public function turn(FieldPoint $point): TurnSnapshotInterface
    {
        if ($this->isGameFinished()) {
            throw new \RuntimeException('Game is not active');
        }

        $vacantPositions = array_intersect($this->fieldX->getVacantIndexes(), $this->fieldO->getVacantIndexes());
        if (!in_array($point->getCellIndex($this->gameParams->getFieldWidth()), $vacantPositions)) {
            throw new \RuntimeException(sprintf('Position %s is occupied', $point->__toString()));
        }

        $snapshot = new TurnSnapshot(clone $this->fieldX, clone $this->fieldO, $this->currentPlayerType, $point);
        $this->turnHistory[] = $snapshot;

        if ($this->currentPlayerType === GameStateMachineInterface::X) {
            $this->fieldX->setOccupied($point);

            $this->currentPlayerType = GameStateMachineInterface::O;
        } else {
            $this->fieldO->setOccupied($point);

            $this->currentPlayerType = GameStateMachineInterface::X;
        }

        if (empty(array_intersect($this->fieldX->getVacantIndexes(), $this->fieldO->getVacantIndexes()))) {
            $this->currentState = self::STATE_DRAW;
        }

        if ($this->victoryDetector->isVictory($this->fieldX)) {
            $this->currentState = self::STATE_X_WON;
        }

        if ($this->victoryDetector->isVictory($this->fieldO)) {
            $this->currentState = self::STATE_O_WON;
        }

        return $snapshot;
    }

    public function getXPlayerField(): FieldStateInterface
    {
        return $this->fieldX;
    }

    public function getOPlayerField(): FieldStateInterface
    {
        return $this->fieldO;
    }

    public function getParameters(): GameParametersInterface
    {
        return $this->gameParams;
    }
}