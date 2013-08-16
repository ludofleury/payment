<?php

namespace Playbloom\Payment\Operation;

use Playbloom\Payment\Operation\OperationInterface;

/**
 * Card authorization operation interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface AuthorizationInterface extends OperationInterface
{
    /**
     * Get the authorization date
     *
     * @return DateTime
     */
    public function getIssuedAt();

    /**
     * Get the authorization expiration
     *
     * @return DateTime
     */
    public function getExpiresAt();

    /**
     * Check whether or not the authorization is expired
     *
     * @return boolean
     */
    public function isExpired();

    /**
     * Check whether or not the money has been captured
     *
     * @return boolean
     */
    public function isCaptured();

    /**
     * Get the capturable amount
     *
     * @return Money\Money
     */
    public function getCapturableAmount();

    /**
     * Get the captured amount
     *
     * @return Money\Money
     */
    public function getCapturedAmount();
}
