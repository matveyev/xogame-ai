<?php
namespace App\Game\ValueObject;

use App\Game\Contracts\FieldStateInterface;
use App\Game\Contracts\TurnSnapshotInterface;

final class TurnSnapshot implements TurnSnapshotInterface
{
    /** @var FieldStateInterface  */
    private $fieldX;

    /** @var FieldStateInterface  */
    private $fieldO;

    /** @var string  */
    private $turnAuthor;

    /** @var FieldPoint  */
    private $turnCoordinate;

    public function __construct(
        FieldStateInterface $fieldX,
        FieldStateInterface $fieldO,
        string $turnAuthor,
        FieldPoint $turnCoordinate
    ) {
        $this->fieldX = $fieldX;
        $this->fieldO = $fieldO;
        $this->turnAuthor = $turnAuthor;
        $this->turnCoordinate = $turnCoordinate;
    }

    public function getXPlayerFieldState(): FieldStateInterface
    {
        return $this->fieldX;
    }

    public function getOPlayerFieldState(): FieldStateInterface
    {
        return $this->fieldO;
    }

    public function getTurnAuthor(): string
    {
        return $this->turnAuthor;
    }

    public function getTurnCoordinate(): FieldPoint
    {
        return $this->turnCoordinate;
    }
}