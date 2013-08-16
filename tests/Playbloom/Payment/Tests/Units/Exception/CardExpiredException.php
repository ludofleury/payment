<?php

namespace Playbloom\Payment\Tests\Units\Exception;

use Playbloom\Payment\Tests\Units\Test;
use Playbloom\Payment\Exception;
use Mock;

class CardExpiredException extends CardException
{
    public function testGetDefaultMessage()
    {
        $card = new Mock\Playbloom\Payment\CardInterface;
        $card->getMockController()->__toString = 'test';

        $exception = new Exception\CardExpiredException($card);

        $this
            ->string($exception->getMessage())
                ->isEqualTo('The card "test" has expired')
        ;
    }
}
