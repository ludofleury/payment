<?php

namespace Playbloom\Payment\Operation;

use Playbloom\Payment\Operation\OperationInterface;

/**
 * Card payment operation interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface PaymentInterface extends OperationInterface
{
    /**
     * Check whether or not the Payment has been refunded
     *
     * @return boolean
     */
    public function isRefunded();

    /**
     * Get the refundable amount
     *
     * @return Money\Money
     */
    public function getRefundableAmount();

    /**
     * Get the refunded amount
     *
     * @return Money\Money
     */
    public function getRefundedAmount();
}
