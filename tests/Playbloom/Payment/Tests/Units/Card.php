<?php

namespace Playbloom\Payment\Tests\Units;

use Playbloom\Payment\Tests\Units\Test;
use Playbloom\Payment;
use DateTime;

class Card extends Test
{
    public function testGetSchemes()
    {
        $this
            ->array(Payment\Card::getSchemes())
                ->isEqualTo(
                    [
                        'amex' => ['/^3[47][0-9]{13}$/'],
                        'cup' => ['/^62[0-9]{14,17}$/'],
                        'diners' => ['/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', ],
                        'discover' => [
                            '/^6011[0-9]{12}$/',
                            '/^64[4-9][0-9]{13}$/',
                            '/^65[0-9]{14}$/',
                            '/^622(12[6-9]|1[3-9][0-9]|[2-8][0-9][0-9]|91[0-9]|92[0-5])[0-9]{10}$/'
                        ],
                        'instapay' => ['/^63[7-9][0-9]{13}$/'],
                        'jcb' => ['/^(?:2131|1800|35[0-9]{3})[0-9]{11}$/'],
                        'laser' => ['/^(6304|670[69]|6771)[0-9]{12,15}$/'],
                        'maestro' => ['/^(5018|5020|5038|6304|6759|6761|676[23]|0604)[0-9]{8,15}$/'],
                        'mastercard' => ['/^5[1-5][0-9]{14}$/'],
                        'visa' => ['/^4([0-9]{12}|[0-9]{15})$/'],
                    ]
                )
        ;
    }

    /**
     * @dataProvider provideSchemes
     */
    public function testGuessType($type, $number)
    {
        $this
            ->variable(Payment\Card::guessType($number))
                ->isIdenticalTo($type)
        ;
    }

    /**
     * @dataProvider provideExpirationDate
     */
    public function testCreateExpirationDateFromString(DateTime $datetime, $string)
    {
        $this
            ->object(Payment\Card::createExpirationDateFromString($string))
                ->isEqualTo($datetime)
        ;
    }

    /**
     * @dataProvider provideInvalidExpirationDate
     */
    public function testCreateExpirationDateFromStringException($invalid)
    {
        $this
            ->exception(
                function () use ($invalid) {
                    Payment\Card::createExpirationDateFromString($invalid);
                }
            )
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage(sprintf('The card expiration string "%s" is invalid', $invalid))
        ;
    }

    /**
     * @dataProvider provideSchemes
     */
    public function testConstructGuessType($type, $scheme)
    {
        $card = new Payment\Card($scheme, 'Ludovic Fleury', '01-14', null, null);
        $this
            ->string($card->getNumber())
                ->isIdenticalTo($scheme)
            ->string($card->getHolderName())
                ->isIdenticalTo('Ludovic Fleury')
            ->object($card->getExpirationDate())
                ->isEqualTo(new DateTime('2014-01-31 23:59:59'))
            ->variable($card->getSecurityCode())
                ->isNull()
            ->variable($card->getType())
                ->isIdenticalTo($type)
        ;
    }

    public function testConstructWrongNumberException()
    {
        $this
            ->exception(
                function () {
                    new Payment\Card('a', 'Ludovic Fleury', '01-13', null, null);
                }
            )
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage('The card number is invalid')
        ;
    }

    public function testConstructExpirationDateNotStringException()
    {
        $this
            ->exception(
                function () {
                    new Payment\Card('378282246310005', 'Ludovic Fleury', array('01-13'), '123', null);
                }
            )
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage('The card expiration date is invalid')
        ;
    }

    public function testConstructExpirationDateWrongFormatException()
    {
        $this
            ->exception(
                function () {
                    new Payment\Card('378282246310005', 'Ludovic Fleury', '13-13', 123, null);
                }
            )
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage('The card expiration string "13-13" is invalid')
        ;
    }

    public function testConstructSecurityCode()
    {
        $card = new Payment\Card('378282246310005', 'Ludovic Fleury', '01-14', 123, null);
        $this
            ->string($card->getNumber())
                ->isIdenticalTo('378282246310005')
            ->string($card->getHolderName())
                ->isIdenticalTo('Ludovic Fleury')
            ->object($card->getExpirationDate())
                ->isEqualTo(new DateTime('2014-01-31 23:59:59'))
            ->integer($card->getSecurityCode())
                ->isIdenticalTo(123)
            ->variable($card->getType())
                ->isIdenticalTo('amex')
        ;
    }

    public function testConstructSecurityCodeNotIntegerException()
    {
        $this
            ->exception(
                function () {
                    new Payment\Card('378282246310005', 'Ludovic Fleury', '01-14', '123', null);
                }
            )
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage('The card security code is invalid')
        ;
    }

    public function testConstructSecurityCodeNegativeIntegerException()
    {
        $this
            ->exception(
                function () {
                    new Payment\Card('378282246310005', 'Ludovic Fleury', '01-14', -1, null);
                }
            )
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage('The card security code is invalid')
        ;
    }

    public function testConstructSecurityCodeMaximumIntegerException()
    {
        $this
            ->exception(
                function () {
                    new Payment\Card('378282246310005', 'Ludovic Fleury', '01-14', 10000, null);
                }
            )
                ->isInstanceOf('\\InvalidArgumentException')
                ->hasMessage('The card security code is invalid')
        ;
    }

    public function testConstructForcedType()
    {
        $card = new Payment\Card('378282246310005', 'Ludovic Fleury', '01-14', null, 'custom');
        $this
            ->string($card->getNumber())
                ->isIdenticalTo('378282246310005')
            ->string($card->getType())
                ->isIdenticalTo('custom')
        ;
    }

    public function testToString()
    {
        $card = new Payment\Card('378282246310005', 'Ludovic Fleury', '01-14', 123, null);
        $this
            ->string($card->__toString())
                ->isIdenticalTo('amex 378282246310005 01-14')
        ;
    }

    public function testIsExpired()
    {
        $before = (new DateTime('-1 month'))->format('m-y');
        $card = new Payment\Card('378282246310005', 'Ludovic Fleury', $before, null, 'custom');
        $this
            ->boolean($card->isExpired())
                ->isTrue()
        ;
    }

    public function testIsNotExpired()
    {
        $after = (new DateTime('+1 month'))->format('m-y');
        $card = new Payment\Card('378282246310005', 'Ludovic Fleury', $after, null, 'custom');
        $this
            ->boolean($card->isExpired())
                ->isFalse()
        ;
    }

    public function provideSchemes()
    {
        return [
            ['amex', '378282246310005'],
            ['amex', '371449635398431'],
            ['amex', '378734493671000'],
            ['amex', '347298508610146'],
            ['cup', '6228888888888888'],
            ['cup', '62288888888888888'],
            ['cup', '622888888888888888'],
            ['cup', '6228888888888888888'],
            ['diners', '30569309025904'],
            ['diners', '36088894118515'],
            ['diners', '38520000023237'],
            ['discover', '6011111111111117'],
            ['discover', '6011000990139424'],
            ['instapay', '6372476031350068'],
            ['instapay', '6385537775789749'],
            ['instapay', '6393440808445746'],
            ['jcb', '3530111333300000'],
            ['jcb', '3566002020360505'],
            ['jcb', '213112345678901'],
            ['jcb', '180012345678901'],
            ['laser', '6304678107004080'],
            ['laser', '6706440607428128629'],
            ['laser', '6771656738314582216'],
            ['maestro', '6759744069209'],
            ['maestro', '5020507657408074712'],
            ['maestro', '6759744069209'],
            ['maestro', '6759744069209'],
            ['mastercard', '5555555555554444'],
            ['mastercard', '5105105105105100'],
            ['visa', '4111111111111111'],
            ['visa', '4012888888881881'],
            ['visa', '4222222222222'],
            [null, '42424242424242424242'],
            [null, '357298508610146'],
            [null, '31569309025904'],
            [null, '37088894118515'],
            [null, '6313440808445746'],
            [null, '622888888888888'],
            [null, '62288888888888888888'],
            [null, '00000000000000000000'],
        ];
    }

    public function provideExpirationDate()
    {
        return [
            [new DateTime('2013-01-31 23:59:59'), '01-13'],
            [new DateTime('2013-02-28 23:59:59'), '02-13'],
            [new DateTime('2012-02-29 23:59:59'), '02-12'], // leap year
            [new DateTime('2013-01-31 23:59:59'), '01/13'],
            [new DateTime('2013-02-28 23:59:59'), '02/13'],
            [new DateTime('2012-02-29 23:59:59'), '02/12'], // leap year
        ];
    }

    public function provideInvalidExpirationDate()
    {
        return [
            [null],
            ['13-13'],
            ['001-13'],
            ['1-1'],
            ['01-1'],
            ['1-01'],
            ['01-2012'],
            ['2012-01'],
            ['13/13'],
            ['001/13'],
            ['1/1'],
            ['01/1'],
            ['1/01'],
            ['01/2012'],
            ['2012/01'],
            [1112],
            ['0101'],
            ['01\\01'],
            ['01,01'],
            ['01,01'],
            ['01_01'],
            ['az-az']
        ];
    }
}
