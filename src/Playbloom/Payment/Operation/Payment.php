<?php

namespace Playbloom\Payment\Operation;

use Playbloom\Payment\Operation\PaymentInterface;
use Playbloom\Payment\Operation\RefundInterface;
use Playbloom\Payment\Operation\OperationId;
use Playbloom\Payment\CardInterface;
use Playbloom\Payment\Exception\OperationException;
use Money\Money;

/**
 * Card payment operation
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Payment implements PaymentInterface
{
    /**
     * OperationId
     *
     * @var Playbloom\Payment\OperationId
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var Playbloom\Payment\CardInterface
     */
    private $card;

    /**
     * @var Money\Money
     */
    private $amount;

    /**
     * @var Playbloom\Payment\Operation\RefundInterface[]
     */
    public $refunds;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $label
     * @param Playbloom\Payment\CardInterface $card
     * @param Money\Money $amount
     */
    public function __construct(OperationId $id, $label, Money $amount, CardInterface $card)
    {
        $this->id = $id;
        $this->label = $label;
        $this->amount = $amount;
        $this->card = $card;
        $this->refunds = [];
    }

    /**
     * Get the string representation
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Payment "%s" for "%s": %s %s',
            $this->id,
            $this->label,
            $this->getAmount()->getAmount(),
            $this->getAmount()->getCurrency()
        );
    }

    /**
     * Get the unique transaction identifier
     *
     * @return Playbloom\Payment\OperationId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the related Card
     *
     * @return Playbloom\Payment\CardInterface
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the amount
     *
     * @return Money\Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Check whether or not the Payment has been refunded
     *
     * @return boolean
     */
    public function isRefunded()
    {
        return $this->getRefundedAmount()->isPositive();
    }

    /**
     * Get the refundable money
     *
     * @return Money\Money
     */
    public function getRefundableAmount()
    {
        $amount = $this->getAmount()->subtract($this->getRefundedAmount());

        return $amount->isNegative() ? new Money(0, $this->amount->getCurrency()) : $amount;
    }

    /**
     * Get the refunded money
     *
     * @return Money\Money
     */
    public function getRefundedAmount()
    {
        $refunded = new Money(0, $this->amount->getCurrency());

        foreach ($this->refunds as $refund) {
            $refunded = $refunded->add($refund->getAmount());
        }

        return $refunded;
    }

    /**
     * Refund
     *
     * @param RefundInterface $refund
     *
     * @throws Playbloom\Payment\Exception\OperationException
     *
     * @return static
     */
    public function refund(RefundInterface $refund)
    {
        if ($refund->getPayment() !== $this) {
            throw new OperationException(sprintf('Unable to refund [%s] with [%s]: operations are not related', $this, $refund));
        }

        $hash = spl_object_hash($refund);
        if (isset($this->refunds[$hash])) {
            throw new OperationException(sprintf('Unable to refund [%s] with [%s]: operations are already related', $this, $refund));
        }

        $this->refunds[$hash] = $refund;

        return $this;
    }
}
