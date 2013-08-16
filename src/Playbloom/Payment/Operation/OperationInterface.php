<?php

namespace Playbloom\Payment\Operation;

/**
 * Card operation interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface OperationInterface
{
    /**
     * Get the unique transaction identifier
     *
     * @return Playbloom\Payment\Operation\OperationId
     */
    public function getId();

    /**
     * Get the string representation
     *
     * @return string
     */
    public function __toString();

    /**
     * Get the related Card
     *
     * @return Playbloom\Payment\CardInterface
     */
    public function getCard();

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Get the amount
     *
     * @return Money\Money
     */
    public function getAmount();
}
