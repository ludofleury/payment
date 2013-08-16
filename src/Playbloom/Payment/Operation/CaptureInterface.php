<?php

namespace Playbloom\Payment\Operation;

/**
 * Card capture operation interface
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface CaptureInterface extends PaymentInterface
{
    /**
     * Get the captured authorization
     *
     * @return Playbloom\Payment\Operation\AuthorizationInterface
     */
    public function getAuthorization();
}
