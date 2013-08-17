<?php

namespace Playbloom\Payment\Tests\Units\Operation;

use Playbloom\Payment\Tests\Units\Operation\AbstractOperation as OperationTest;
use Playbloom\Payment\Operation;
use Playbloom\Payment\Operation\OperationId as Id;
use Money\Money;
use DateTime;
use DateInterval;
use Mock;

class Authorization extends OperationTest
{
    /**
     * Data provider required by OperationTest
     */
    public function provideTestedInstance()
    {
        $toString = 'Authorization "id" for "saving the world": 133742 EUR';
        $id = new Id('id');
        $label = 'saving the world';
        $amount = Money::EUR(133742);
        $card = new Mock\Playbloom\Payment\CardInterface();
        $ttl = DateInterval::createFromDateString('+7 days');

        return [
            [
                new Operation\Authorization($id, $label, $amount, $card, $ttl),
                $toString,
                $id,
                $label,
                $amount,
                $card
            ]
        ];
    }

    public function testGetIssuedAt()
    {
        $before = new DateTime();
        $authorization = $this->createInstance();
        $after = new DateTime();

        $this
            ->dateTime($authorization->getIssuedAt())
            ->boolean($authorization->getIssuedAt() == $before)
                ->isTrue()
            ->boolean($authorization->getIssuedAt() == $after)
                ->isTrue()
        ;
    }

    public function testExpiresAt()
    {
        $ttl = DateInterval::createFromDateString('+7 days');
        $authorization = $this->createInstance(['ttl' => $ttl]);

        $this
            ->dateTime($authorization->getExpiresAt())
                ->hasYear($authorization->getIssuedAt()->format('Y'))
            ->boolean($authorization->getExpiresAt() == $authorization->getIssuedAt()->add($ttl))
                ->isTrue()
            ->integer($authorization->getExpiresAt()->getTimestamp())
                ->isIdenticalTo($authorization->getIssuedAt()->getTimestamp() + 604800)
        ;
    }

    public function testIsExpired()
    {
        $ttl = DateInterval::createFromDateString('+1 second');
        $authorization = $this->createInstance(['ttl' => $ttl]);
        sleep(2);

         $this
            ->boolean($authorization->isExpired())
                ->isTrue()
        ;
    }

    public function testIsNotExpired()
    {
        $ttl = DateInterval::createFromDateString('+1 second');
        $authorization = $this->createInstance(['ttl' => $ttl]);

         $this
            ->boolean($authorization->isExpired())
                ->isFalse()
        ;
    }

    public function testIsNotCaptured()
    {
        $authorization = $this->createInstance();
        $this
            ->boolean($authorization->isCaptured())
                ->isFalse()
        ;
    }

    public function testCaptureUnrelatedOperation()
    {
        $authorization = $this->createInstance(['label' => 'original']);
        $anotherAuthorization = $this->createInstance(['label' => 'another']);

        $capture = new Mock\Playbloom\Payment\Operation\CaptureInterface;
        $capture->getMockController()->getAuthorization = $anotherAuthorization;
        $capture->getMockController()->getAmount = Money::EUR(1);
        $capture->getMockController()->__toString = 'Capture for another authorization';

        $this
            ->exception(function () use ($authorization, $capture) {
                $authorization->capture($capture);
            })
                ->isInstanceOf('Playbloom\\Payment\\Exception\\OperationException')
                ->hasMessage(sprintf('Unable to capture [%s] with [Capture for another authorization]: operations are not related', $authorization->__toString()))
        ;
    }

    public function testCaptureAlreadyRelatedOperation()
    {
        $authorization = $this->createInstance(['label' => 'original']);

        $capture = new Mock\Playbloom\Payment\Operation\CaptureInterface;
        $capture->getMockController()->getAuthorization = $authorization;
        $capture->getMockController()->getAmount = Money::EUR(1);
        $capture->getMockController()->__toString = 'Capture for original authorization';
        $authorization->capture($capture);

        $this
            ->exception(function () use ($authorization, $capture) {
                $authorization->capture($capture);
            })
                ->isInstanceOf('Playbloom\\Payment\\Exception\\OperationException')
                ->hasMessage(sprintf('Unable to capture [%s] with [Capture for original authorization]: operations are already related', $authorization->__toString()))
        ;
    }

    public function testIsCaptured()
    {
        $authorization = $this->createInstance(['amount' => Money::EUR(10000)]);

        $capture = new Mock\Playbloom\Payment\Operation\CaptureInterface;
        $capture->getMockController()->getAuthorization = $authorization;
        $capture->getMockController()->getAmount = Money::EUR(1);

        $authorization->capture($capture);

        $this
            ->boolean($authorization->isCaptured())
                ->isTrue()
        ;
    }

    public function testGetCapturableAmount()
    {
        $authorization = $this->createInstance(['amount' => Money::EUR(10000)]);

        $capture = new Mock\Playbloom\Payment\Operation\CaptureInterface;
        $capture->getMockController()->getAuthorization = $authorization;
        $capture->getMockController()->getAmount = Money::EUR(1000);
        $authorization->capture($capture);
        $this
            ->object($authorization->getCapturableAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($authorization->getCapturableAmount()->equals(Money::EUR(9000)))
                ->isTrue()
        ;

        $capture = new Mock\Playbloom\Payment\Operation\CaptureInterface;
        $capture->getMockController()->getAuthorization = $authorization;
        $capture->getMockController()->getAmount = Money::EUR(9000);
        $authorization->capture($capture);
        $this
            ->object($authorization->getCapturableAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($authorization->getCapturableAmount()->equals(Money::EUR(0)))
                ->isTrue()
        ;

        $capture = new Mock\Playbloom\Payment\Operation\CaptureInterface;
        $capture->getMockController()->getAuthorization = $authorization;
        $capture->getMockController()->getAmount = Money::EUR(500);
        $authorization->capture($capture);
        $this
            ->object($authorization->getCapturableAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($authorization->getCapturableAmount()->equals(Money::EUR(0)))
                ->isTrue()
        ;
    }

    public function testGetCapturedAmount()
    {
        $authorization = $this->createInstance(['amount' => Money::EUR(10000)]);

        $capture = new Mock\Playbloom\Payment\Operation\CaptureInterface;
        $capture->getMockController()->getAuthorization = $authorization;
        $capture->getMockController()->getAmount = Money::EUR(2000);

        $authorization->capture($capture);

        $this
            ->object($authorization->getCapturedAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($authorization->getCapturedAmount()->equals(Money::EUR(2000)))
                ->isTrue()
        ;
    }

    private function createInstance(array $arguments = [])
    {
        $id = isset($arguments['id']) ? $arguments['id'] : new Id('id');
        $label = isset($arguments['label']) ? $arguments['label'] : 'saving the world';
        $amount = isset($arguments['amount']) ? $arguments['amount'] : Money::EUR(133742);
        $card = isset($arguments['card']) ? $arguments['card'] : new Mock\Playbloom\Payment\CardInterface();
        $ttl = isset($arguments['ttl']) ? $arguments['ttl'] : DateInterval::createFromDateString('+7 days');


        return new Operation\Authorization($id, $label, $amount, $card, $ttl);
    }
}
