<?php
namespace App\Game\Implementation\StateAnalysis;

use App\Game\Contracts\FieldStateInterface;
use App\Game\Contracts\GameParametersInterface;
use App\Game\Contracts\VictoryDetectorInterface;
use App\Game\Implementation\StateAnalysis\ContinuousStripeDetector;
use App\Game\ValueObject\Stripe;

class VictoryDetector implements VictoryDetectorInterface
{
    /** @var ContinuousStripeDetector  */
    private $stripeDetector;

    /** @var GameParametersInterface */
    private $gameParameters;

    public function __construct(GameParametersInterface $gameParameters)
    {
        $this->gameParameters = $gameParameters;
        $this->stripeDetector = new ContinuousStripeDetector();
    }

    public function isVictory(FieldStateInterface $fieldState): bool
    {
        $stripes = $this->stripeDetector->getStripes($fieldState, false);

        foreach ($stripes as $direction => $stripesPerDirection) {
            /** @var Stripe $stripe */
            foreach ($stripesPerDirection as $stripe) {
                if ($stripe->getLength() >= $this->gameParameters->getWinningStripeLength()) {
                    return true;
                }
            }
        }

        return false;
    }
}