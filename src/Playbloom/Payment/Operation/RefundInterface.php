<?php

namespace Playbloom\Payment\Operation;

use Playbloom\Payment\Operation\CreditInterface;

/**
 * Card refund operation interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface RefundInterface extends CreditInterface
{
    /**
     * Get the refunded payment
     *
     * @return Playbloom\Payment\Operation\PaymentInterface
     */
    public function getPayment();
}
