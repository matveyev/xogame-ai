<?php
namespace App\Game\ValueObject;

use App\Game\Contracts\FieldStateInterface;
use App\Game\ValueObject\FieldPoint;

final class FieldState implements FieldStateInterface
{
    /**
     * @var int[]
     */
    private $data;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    public function __construct(int $width, int $height)
    {
        $this->data = array_fill(0, $height * $width, 0);
        $this->width = $width;
        $this->height = $height;
    }

    public function isOccupied(FieldPoint $point): bool
    {
        return 1 === $this->data[$this->getSanitizedFieldIndex($point)];
    }

    public function setOccupied(FieldPoint $point, bool $isOccupied = true): void
    {
        $this->data[$this->getSanitizedFieldIndex($point)] = $isOccupied ? 1 : 0;
    }

    public function getAsVector(): array
    {
        return $this->data;
    }

    /**
     * @param FieldPoint $point
     * @return int
     */
    private function getSanitizedFieldIndex(FieldPoint $point): int
    {
        $index = $point->getCellIndex($this->width);

        if (!array_key_exists($index, $this->data)) {
            throw new \RuntimeException(sprintf('Field index out of bounds: %d, %s ', $index, $point->__toString()));
        }

        return $index;
    }

    /**
     * @return int[]
     */
    public function getVacantIndexes(): array
    {
        $result = [];
        foreach ($this->data as $index => $value) {
            if (0 === $value) {
                $result[] = $index;
            }
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    public function __clone()
    {
        $copy = new self($this->width, $this->height);
        $copy->data = array_merge([], $this->data);
        return $copy;
    }

    public function __toString()
    {
        return implode(' ', $this->data);
    }
}