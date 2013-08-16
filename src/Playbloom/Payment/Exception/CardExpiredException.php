<?php

namespace Playbloom\Payment\Exception;

use Playbloom\Payment\Exception\CardException;
use Playbloom\Payment\CardInterface;

class CardExpiredException extends CardException
{
    public function __construct(CardInterface $card, $message = null)
    {
        parent::__construct(
            $card,
            $message ?: sprintf('The card "%s" has expired', $card)
        );
    }
}
