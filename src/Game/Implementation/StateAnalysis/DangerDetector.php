<?php

namespace App\Game\Implementation\StateAnalysis;

use App\Game\Contracts\DangerousSituationDetectorInterface;
use App\Game\Contracts\FieldStateInterface;
use App\Game\Contracts\GameParametersInterface;
use App\Game\Implementation\StateAnalysis\ContinuousStripeDetector;
use App\Game\ValueObject\FieldPoint;

class DangerDetector implements DangerousSituationDetectorInterface
{
    /**
     * @var ContinuousStripeDetector
     */
    private $stripeDetector;

    /**
     * @var FieldPoint
     */
    private $dangerousPoint;

    /**
     * @var GameParametersInterface
     */
    private $gameParameters;

    public function __construct(GameParametersInterface $gameParameters)
    {
        $this->gameParameters = $gameParameters;
        $this->stripeDetector = new ContinuousStripeDetector();
    }

    /**
     * @param FieldStateInterface $defendantField
     * @param FieldStateInterface $enemyField
     * @return bool
     */
    public function isDangerousSituation(
        FieldStateInterface $defendantField,
        FieldStateInterface $enemyField
    ): bool {
        $stripes = $this->stripeDetector->getStripes($enemyField, true);

        $dangerousStripeLength = $this->gameParameters->getWinningStripeLength() - 1;

        foreach ($stripes as $direction => $stripesPerDirection) {
            foreach ($stripesPerDirection as $index => $stripe) {
                $dangerousPoint = null;

                if (!empty($stripe->getDefencePoints()) && isset($stripesPerDirection[$index + 1])) {
                    foreach ($stripe->getDefencePoints() as $point1) {
                        foreach ($stripesPerDirection[$index + 1]->getDefencePoints() as $point2) {
                            if (
                                $point1->getX() == $point2->getX() &&
                                $point1->getY() == $point2->getY()
                            ) {
                                $dangerousPoint = $point1;
                                break 2;
                            }
                        }
                    }
                }

                if (!is_null($dangerousPoint)) {
                    $len = $stripe->getLength() + $stripesPerDirection[$index + 1]->getLength();
                    if ($len >= $dangerousStripeLength && !$defendantField->isOccupied($dangerousPoint)) {
                        $this->dangerousPoint = $dangerousPoint;
                        return true;
                    }
                } else {
                    if ($stripe->getLength() >= $dangerousStripeLength) {
                        foreach ($stripe->getDefencePoints() as $point) {
                            if (!$defendantField->isOccupied($point)) {
                                $this->dangerousPoint = $point;
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getDangerousPoint(): FieldPoint
    {
        return $this->dangerousPoint;
    }
}