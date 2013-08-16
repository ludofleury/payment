<?php

namespace Playbloom\Payment\Tests\Units\Operation;

use Playbloom\Payment\Tests\Units\Test;

abstract class AbstractOperation extends Test
{
    abstract public function provideTestedInstance();

    /**
     * @dataProvider provideTestedInstance
     */
    public function testConstruct($object, $toString, $id, $label, $amount, $card)
    {
        $this
            ->object($object)
                ->isInstanceOf('Playbloom\\Payment\\Operation\\OperationInterface')
            ->string($object->__toString())
                ->isIdenticalTo($toString)
            ->object($object->getId())
                ->isInstanceOf('Playbloom\\Payment\\Operation\\OperationId')
                ->isIdenticalTo($id)
            ->string($object->getLabel())
                ->isIdenticalTo($label)
            ->object($object->getAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($object->getAmount()->equals($amount))
                ->isTrue()
            ->object($object->getCard())
                ->isInstanceOf('Playbloom\\Payment\\CardInterface')
                ->isIdenticalTo($card)
        ;
    }
}
