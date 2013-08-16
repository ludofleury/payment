<?php

namespace Playbloom\Payment\Tests\Units\Operation;

use Playbloom\Payment\Tests\Units\Operation\AbstractOperation as OperationTest;
use Playbloom\Payment\Operation;
use Playbloom\Payment\Operation\OperationId as Id;
use Money\Money;
use Mock;

class Payment extends OperationTest
{
    /**
     * Data provider required by OperationTest
     */
    public function provideTestedInstance()
    {
        $toString = 'Payment "id" for "saving the world": 133742 EUR';
        $id = new Id('id');
        $label = 'saving the world';
        $amount = Money::EUR(133742);
        $card = new Mock\Playbloom\Payment\CardInterface();

        return [
            [
                new Operation\Payment($id, $label, $amount, $card),
                $toString,
                $id,
                $label,
                $amount,
                $card
            ]
        ];
    }

    public function testIsNotRefunded()
    {
        $payment = $this->createInstance();

        $this
            ->boolean($payment->isRefunded())
                ->isFalse()
        ;
    }

    public function testRefundUnrelatedOperation()
    {
        $payment = $this->createInstance(['label' => 'original']);
        $anotherPayment = $this->createInstance(['label' => 'another']);

        $refund = new Mock\Playbloom\Payment\Operation\RefundInterface;
        $refund->getMockController()->getPayment = $anotherPayment;
        $refund->getMockController()->getAmount = Money::EUR(1);
        $refund->getMockController()->__toString = 'Refund for another payment';

        $this
            ->exception(function () use ($payment, $refund) {
                $payment->refund($refund);
            })
                ->isInstanceOf('Playbloom\\Payment\\Exception\\OperationException')
                ->hasMessage(sprintf('Unable to refund [%s] with [Refund for another payment]: operations are not related', $payment->__toString()))
        ;
    }

    public function testRefundAlreadyRelatedOperation()
    {
        $payment = $this->createInstance(['label' => 'original']);

        $refund = new Mock\Playbloom\Payment\Operation\RefundInterface;
        $refund->getMockController()->getPayment = $payment;
        $refund->getMockController()->getAmount = Money::EUR(1);
        $refund->getMockController()->__toString = 'Refund for original payment';
        $payment->refund($refund);

        $this
            ->exception(function () use ($payment, $refund) {
                $payment->refund($refund);
            })
                ->isInstanceOf('Playbloom\\Payment\\Exception\\OperationException')
                ->hasMessage(sprintf('Unable to refund [%s] with [Refund for original payment]: operations are already related', $payment->__toString()))
        ;
    }

    public function testIsRefunded()
    {
        $payment = $this->createInstance(['amount' => Money::EUR(10000)]);

        $refund = new Mock\Playbloom\Payment\Operation\RefundInterface;
        $refund->getMockController()->getPayment = $payment;
        $refund->getMockController()->getAmount = Money::EUR(1);

        $payment->refund($refund);

        $this
            ->boolean($payment->isRefunded())
                ->isTrue()
        ;
    }

    public function testGetRefundableAmount()
    {
        $payment = $this->createInstance(['amount' => Money::EUR(10000)]);

        $refund = new Mock\Playbloom\Payment\Operation\RefundInterface;
        $refund->getMockController()->getPayment = $payment;
        $refund->getMockController()->getAmount = Money::EUR(1000);
        $payment->refund($refund);
        $this
            ->object($payment->getRefundableAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($payment->getRefundableAmount()->equals(Money::EUR(9000)))
                ->isTrue()
        ;

        $refund = new Mock\Playbloom\Payment\Operation\RefundInterface;
        $refund->getMockController()->getPayment = $payment;
        $refund->getMockController()->getAmount = Money::EUR(9000);
        $payment->refund($refund);
        $this
            ->object($payment->getRefundableAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($payment->getRefundableAmount()->equals(Money::EUR(0)))
                ->isTrue()
        ;

        $refund = new Mock\Playbloom\Payment\Operation\RefundInterface;
        $refund->getMockController()->getPayment = $payment;
        $refund->getMockController()->getAmount = Money::EUR(500);
        $payment->refund($refund);
        $this
            ->object($payment->getRefundableAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($payment->getRefundableAmount()->equals(Money::EUR(0)))
                ->isTrue()
        ;
    }

    public function testGetRefundedAmount()
    {
        $payment = $this->createInstance(['amount' => Money::EUR(10000)]);

        $refund = new Mock\Playbloom\Payment\Operation\RefundInterface;
        $refund->getMockController()->getPayment = $payment;
        $refund->getMockController()->getAmount = Money::EUR(2000);

        $payment->refund($refund);

        $this
            ->object($payment->getRefundableAmount())
                ->isInstanceOf('Money\\Money')
            ->boolean($payment->getRefundedAmount()->equals(Money::EUR(2000)))
                ->isTrue()
        ;
    }

    private function createInstance(array $arguments = [])
    {
        $id = isset($arguments['id']) ? $arguments['id'] : new Id('id');
        $label = isset($arguments['label']) ? $arguments['label'] : 'saving the world';
        $amount = isset($arguments['amount']) ? $arguments['amount'] : Money::EUR(133742);
        $card = isset($arguments['card']) ? $arguments['card'] : new Mock\Playbloom\Payment\CardInterface();

        return new Operation\Payment($id, $label, $amount, $card);
    }
}
