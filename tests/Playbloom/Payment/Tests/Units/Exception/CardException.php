<?php

namespace Playbloom\Payment\Tests\Units\Exception;

use Playbloom\Payment\Tests\Units\Test;
use Playbloom\Payment\Exception;
use Mock;

class CardException extends Test
{
    /**
     * @dataProvider provideInstance
     */
    public function testInstance($exception, $message)
    {
        $this
            ->object($exception)
                ->isInstanceOf('Playbloom\Payment\Exception\CardException')
            ->string($exception->getMessage())
                ->isIdenticalTo($message)
        ;
    }

    public function testGetCard()
    {
        $card = new Mock\Playbloom\Payment\CardInterface;
        $exception = $this->createInstance($card);

        $this
            ->object($exception->getCard())
                ->isIdenticalTo($card)
        ;
    }

    /**
     * CardException Data provider
     *
     * Provide an instance to test the inheritance implementation
     * Overridde instruction:
     *  - respect the same providing format.
     *
     * @return array Collection of [CardException $instance, string $message]
     */
    public function provideInstance()
    {
        $message = 'card expired exception test message';
        $exception = $this->createInstance(null, $message);

        return [
            [
                $exception,
                $message
            ]
        ];
    }

    /**
     * Helper to create an instance
     *
     * @param CardInterface $card
     * @param string        $message
     *
     * @return CardException The tested class subtype of CardException
     */
    protected function createInstance($card = null, $message = null)
    {
        $exceptionClass = $this->getTestedClassName();
        $card = null !== $card ? $card : new Mock\Playbloom\Payment\CardInterface();
        $message = null !== $message ? $message : 'card expired exception default test message';

        return new $exceptionClass($card, $message);
    }
}
