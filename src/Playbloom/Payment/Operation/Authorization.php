<?php

namespace Playbloom\Payment\Operation;

use Playbloom\Payment\Operation\AuthorizationInterface;
use Playbloom\Payment\Operation\CaptureInterface;
use Playbloom\Payment\Operation\OperationId;
use Playbloom\Payment\CardInterface;
use Playbloom\Payment\Exception\OperationException;
use Money\Money;
use DateTime;
use DateInterval;

/**
 * Card payment operation
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Authorization implements AuthorizationInterface
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
     * @var DateTime
     */
    private $issuedAt;

    /**
     * @var DateInterval
     */
    private $ttl;

    /**
     * @var Playbloom\Payment\Operation\CaptureInterface[]
     */
    public $captures;

    /**
     * Constructor
     *
     * @param string $id
     * @param string $label
     * @param Playbloom\Payment\CardInterface $card
     * @param Money $amount
     * @param DateInterval $ttl
     */
    public function __construct(OperationId $id, $label, Money $amount, CardInterface $card, DateInterval $ttl)
    {
        $this->id = $id;
        $this->label = $label;
        $this->amount = $amount;
        $this->card = $card;
        $this->ttl = $ttl;
        $this->issuedAt = new DateTime();
        $this->captures = [];
    }

    /**
     * Get the unique transaction identifier
     *
     * @return Playbloom\Payment\Operation\OperationId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the string representation
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'Authorization "%s" for "%s": %s %s',
            $this->id,
            $this->label,
            $this->getAmount()->getAmount(),
            $this->getAmount()->getCurrency()
        );
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
     * Get the authorization date
     *
     * @return DateTime
     */
    public function getIssuedAt()
    {
        return clone $this->issuedAt;
    }

    /**
     * Get the authorization expiration date
     *
     * @return DateTime
     */
    public function getExpiresAt()
    {
        $expiresAt = clone $this->issuedAt;

        return $expiresAt->add($this->ttl);
    }

    /**
     * Check whether or not the authorization is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->getExpiresAt() < new DateTime();
    }

    /**
     * Check whether or not the money has been captured
     *
     * @return boolean
     */
    public function isCaptured()
    {
        return $this->getCapturedAmount()->isPositive();
    }

    /**
     * Get the capturable amount
     *
     * @return Money\Money
     */
    public function getCapturableAmount()
    {
        $amount = $this->getAmount()->subtract($this->getCapturedAmount());

        return $amount->isNegative() ? new Money(0, $this->amount->getCurrency()) : $amount;
    }

    /**
     * Get the captured amount
     *
     * @return Money\Money
     */
    public function getCapturedAmount()
    {
        $captured = new Money(0, $this->amount->getCurrency());

        foreach ($this->captures as $capture) {
            $captured = $captured->add($capture->getAmount());
        }

        return $captured;
    }

    /**
     * Capture
     *
     * @param  Playbloom\Payment\Provider\Operation\CaptureInterface $capture
     *
     * @return Money\Money
     */
    public function capture(CaptureInterface $capture)
    {
        if ($capture->getAuthorization() !== $this) {
            throw new OperationException(sprintf('Unable to capture [%s] with [%s]: operations are not related', $this, $capture));
        }

        $hash = spl_object_hash($capture);
        if (isset($this->captures[$hash])) {
            throw new OperationException(sprintf('Unable to capture [%s] with [%s]: operations are already related', $this, $capture));
        }

        $this->captures[$hash] = $capture;

        return $this;
    }
}
