<?php

namespace Playbloom\Payment\Tests\Units\Exception;

use Playbloom\Payment\Tests\Units\Test;
use Playbloom\Payment\Exception;

class OperationException extends Test
{
    /**
     * @dataProvider provideInstance
     */
    public function testInstance($exception, $message)
    {
        $this
            ->object($exception)
                ->isInstanceOf('Playbloom\Payment\Exception\OperationException')
            ->string($exception->getMessage())
                ->isIdenticalTo($message)
        ;
    }

    /**
     * OperationException Data provider
     *
     * Provide an instance to test the inheritance implementation
     * Overridde instruction:
     *  - respect the same providing format.
     *
     * @return array Colection of [OperationException $instance, string $message]
     */
    public function provideInstance()
    {
        $message = 'operation exception test message';
        $exception = $this->createInstance($message);

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
     * @param string $message
     *
     * @return OperationException The tested class subtype of OperationException
     */
    protected function createInstance($message = null)
    {
        $exceptionClass = $this->getTestedClassName();
        $message = null !== $message ? $message : 'card expired exception default test message';

        return new $exceptionClass($message);
    }
}
