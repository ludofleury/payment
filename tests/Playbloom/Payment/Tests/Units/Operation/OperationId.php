<?php

namespace Playbloom\Payment\Tests\Units\Operation;

use Playbloom\Payment\Tests\Units\Test;
use Playbloom\Payment\Operation;
use Mock;

class OperationId extends Test
{
    /**
     * @dataProvider provideValidValues
     */
    public function testConstructValid($value)
    {
        $id = new Operation\OperationId($value);

        $this
            ->string($id->__toString())
                ->isIdenticalTo((string) $value)
            ->variable($id->getValue())
                ->isIdenticalTo($value)
        ;
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testConstructInvalid($value)
    {
        $this
            ->exception(function () use ($value) {
                new Operation\OperationId($value);
            })
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage(sprintf('Invalid "%s" type', gettype($value)))
        ;
    }

    public function provideValidValues()
    {
        return [
            ['string'],
            [1],
            [true],
            [false],
            [1.1]
        ];
    }

    public function provideInvalidValues()
    {
        return [
            [new Mock\Some()],
            [null],
            [array()],
        ];
    }
}
