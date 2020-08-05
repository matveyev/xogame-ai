<?php

namespace App\Game\Implementation\StateAnalysis;

use App\Game\Contracts\FieldStateInterface;
use App\Game\ValueObject\FieldPoint;
use App\Game\ValueObject\Stripe;

class ContinuousStripeDetector
{
    /**
     * @param FieldStateInterface $field
     * @param bool $detectDefencePoints
     * @return Stripe[][]
     */
    public function getStripes(
        FieldStateInterface $field,
        bool $detectDefencePoints = true
    ): array {
        return [
            'horz'  => $this->getHorizontalStripes($field, $detectDefencePoints),
            'vert'  => $this->getVerticalStripes($field, $detectDefencePoints),
            'diagA' => $this->getDiagonalAStripes($field, $detectDefencePoints),
            'diagB' => $this->getDiagonalBStripes($field, $detectDefencePoints),
        ];
    }

    private function findStripesOnDirection(
        FieldStateInterface $fieldState,
        int $startX,
        int $startY,
        int $dX,
        int $dY,
        bool $detectDefencePoints
    ): array {
        /** @var Stripe[] $result */
        $result = [];
        $h = $fieldState->getHeight();
        $w = $fieldState->getWidth();

        $inStripe = false;
        $beginPoint = null;
        $len = 0;

        for (
            $x = $startX, $y = $startY;
            $y < $h && $y >= 0 && $x < $w && $x >= 0;
            $y += $dY, $x += $dX
        ) {
            $point = FieldPoint::fromCoords($x, $y);
            if ($fieldState->isOccupied($point)) {
                if (!$inStripe) {
                   $inStripe = true;
                   $beginPoint = $point;
                   $len = 0;
                }
                $len++;
            } else {
                if ($inStripe) {
                    $inStripe = false;

                    $result[] = new Stripe(
                        $beginPoint,
                        FieldPoint::fromCoords($x - $dX, $y - $dY),
                        $len
                    );
                }
            }
        }

        if ($inStripe) {
            $x += -$dX;
            $y += -$dY;

            $result[] = new Stripe(
                $beginPoint,
                FieldPoint::fromCoords($x, $y),
                $len
            );
        }

        if ($detectDefencePoints) {
            foreach ($result as $stripe) {
                if ($point = $this->translatePoint($fieldState, $stripe->getStart(), -$dX, -$dY)) {
                    $stripe->addDefencePoint($point);
                }
                if ($point = $this->translatePoint($fieldState, $stripe->getEnd(), $dX, $dY)) {
                    $stripe->addDefencePoint($point);
                }
            }
        }

        return $result;
    }

    private function translatePoint(FieldStateInterface $field, FieldPoint $point, int $dX, int $dY): ?FieldPoint
    {
        $newX = $point->getX() + $dX;

        if ($newX < 0 || $newX >= $field->getWidth()) {
            return null;
        }

        $newY = $point->getY() + $dY;

        if ($newY < 0 || $newY >= $field->getHeight()) {
            return null;
        }

        return FieldPoint::fromCoords($newX, $newY);
    }

    /**
     * @param FieldStateInterface $field
     * @param bool $detectDefencePoints
     * @return Stripe[]
     */
    private function getHorizontalStripes(FieldStateInterface $field, bool $detectDefencePoints): array
    {
        $dirResult = [];

        for ($y = 0; $y < $field->getHeight(); $y++) {
            $dirResult = array_merge(
                $dirResult,
                $this->findStripesOnDirection(
                    $field,
                    0,
                    $y,
                    1,
                    0,
                    $detectDefencePoints
                )
            );
        }

        return $dirResult;
    }

    /**
     * @param FieldStateInterface $field
     * @param bool $detectDefencePoints
     * @return array
     */
    private function getVerticalStripes(FieldStateInterface $field, bool $detectDefencePoints): array
    {
        $dirResult = [];

        for ($x = 0; $x < $field->getWidth(); $x++) {
            $dirResult = array_merge(
                $dirResult,
                $this->findStripesOnDirection(
                    $field,
                    $x,
                    0,
                    0,
                    1,
                    $detectDefencePoints
                )
            );
        }

        return $dirResult;
    }

    /**
     * @param FieldStateInterface $field
     * @param bool $detectDefencePoints
     * @return array
     */
    private function getDiagonalAStripes(FieldStateInterface $field, bool $detectDefencePoints): array
    {
        $dirResult = [];

        for ($y = 0; $y < $field->getHeight(); $y++) {
            $dirResult = array_merge(
                $dirResult,
                $this->findStripesOnDirection(
                    $field,
                    0,
                    $y,
                    1,
                    1,
                    $detectDefencePoints
                )
            );
        }

        for ($x = 1; $x < $field->getWidth(); $x++) {
            $dirResult = array_merge(
                $dirResult,
                $this->findStripesOnDirection(
                    $field,
                    $x,
                    0,
                    1,
                    1,
                    $detectDefencePoints
                )
            );
        }

        return $dirResult;
    }

    /**
     * @param FieldStateInterface $field
     * @param bool $detectDefencePoints
     * @return array
     */
    private function getDiagonalBStripes(FieldStateInterface $field, bool $detectDefencePoints): array
    {
        $dirResult = [];

        for ($y = 0; $y < $field->getHeight(); $y++) {
            $dirResult = array_merge(
                $dirResult,
                $this->findStripesOnDirection(
                    $field,
                    $field->getWidth() - 1,
                    $y,
                    -1,
                    1,
                    $detectDefencePoints
                )
            );
        }

        for ($x = 0; $x < $field->getWidth() - 1; $x++) {
            $dirResult = array_merge(
                $dirResult,
                $this->findStripesOnDirection(
                    $field,
                    $x,
                    0,
                    -1,
                    1,
                    $detectDefencePoints
                )
            );
        }

        return $dirResult;
    }
}