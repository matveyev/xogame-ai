<?php
namespace App\Game\ValueObject;

final class FieldPoint
{
    /**
     * @var int
     */
    private $x;

    /**
     * @var int
     */
    private $y;

    private function __construct()
    {
    }

    public static function fromCoords(int $x, int $y): self
    {
        $point = new static();
        $point->x = $x;
        $point->y = $y;

        return $point;
    }

    public static function fromCellNumber(int $cellNumber, int $fieldWidth): self
    {
        $y = (int)floor($cellNumber / $fieldWidth);
        $x = $cellNumber - $fieldWidth * $y;

        return self::fromCoords($x, $y);
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    public function getCellIndex(int $fieldWidth): int
    {
        return $this->x + $this->y * $fieldWidth;
    }

    public function __toString()
    {
        return sprintf('(%d, %d)', $this->x, $this->y);
    }

    public function equals(self $point): bool
    {
        return ($this->x === $point->x) && ($this->y === $point->y);
    }
}