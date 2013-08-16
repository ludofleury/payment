<?php

namespace Playbloom\Payment\Exception;

use Playbloom\Payment\CardInterface;
use Exception;

class CardException extends Exception
{
    private $card;

    public function __construct(CardInterface $card, $message)
    {
        $this->card = $card;
        parent::__construct($message);
    }

    public function getCard()
    {
        return $this->card;
    }
}
