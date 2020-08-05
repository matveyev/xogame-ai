<?php
namespace App\Game\ValueObject;

final class Stripe
{
    /** @var int  */
    private $length;

    /** @var FieldPoint  */
    private $start;

    /** @var FieldPoint  */
    private $end;

    /** @var FieldPoint[] */
    private $defencePoints;

    /**
     * @param FieldPoint $start
     * @param FieldPoint $end
     * @param int $length
     * @param FieldPoint[] $defencePoints
     */
    public function __construct(
        FieldPoint $start,
        FieldPoint $end,
        int $length,
        array $defencePoints = []
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->length = $length;
        $this->defencePoints = $defencePoints;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return FieldPoint
     */
    public function getStart(): FieldPoint
    {
        return $this->start;
    }

    /**
     * @return FieldPoint
     */
    public function getEnd(): FieldPoint
    {
        return $this->end;
    }

    /**
     * @return FieldPoint[]
     */
    public function getDefencePoints(): array
    {
        return $this->defencePoints;
    }

    /**
     * @param FieldPoint $point
     */
    public function addDefencePoint(FieldPoint $point): void
    {
        $this->defencePoints[] = $point;
    }

    public function __toString()
    {
        $str = sprintf('<%s=%d=%s>', $this->start->__toString(), $this->length, $this->end->__toString());

        if (!empty($this->defencePoints)) {
            $str .= ' [' . implode(', ', $this->defencePoints) . ']';
        }

        return $str;
    }
}