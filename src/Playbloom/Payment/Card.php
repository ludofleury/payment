<?php

/**
 * Symfony Licence for the card schemes
 *
 * Copyright (c) 2004-2013 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Playbloom\Payment;

use DateTime;
use InvalidArgumentException;

/**
 * Payment card
 *
 * Any kind of debit, credit, charge card
 *
 * @see http://en.wikipedia.org/wiki/Credit_card
 * @see http://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/credit_card_numbers.htm
 *
 * @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
class Card implements CardInterface
{
    /**
     * @var string $type The card type
     */
    private $type;

    /**
     * @var string $number ISO/IEC 7812 Primary Account Number (~16 digits)
     *
     * @see http://en.wikipedia.org/wiki/ISO/IEC_7812
     * @see http://en.wikipedia.org/wiki/List_of_Issuer_Identification_Numbers
     */
    private $number;

    /**
     * @var string $holderName The card holder name
     */
    private $holderName;

    /**
     * @var DateTime $expirationDate Expiration date
     */
    private $expirationDate;

    /**
     * @var int $securityCode Card Security Code (CSC)
     *
     * Also known as CSC, CVD, CVV, CVV2, CVVC, CVC, CVC2, V-code, CCV, CID...
     * Used in Card Not Present (CNP) transaction
     *
     * @see http://en.wikipedia.org/wiki/Card_Security_Code
     */
    private $securityCode;

    /**
     * Get the registered card schemes list
     *
     * Code extracted from the Symfony Validator CardScheme
     *
     * @return array
     */
    public static function getSchemes()
    {
        return [
            /**
             * American Express card numbers start with 34 or 37 and have 15 digits.
             */
            self::TYPE_AMEX => [
                '/^3[47][0-9]{13}$/'
            ],
            /**
             * China UnionPay cards start with 62 and have between 16 and 19 digits.
             * Please note that these cards do not follow Luhn Algorithm as a checksum.
             */
            self::TYPE_CUP => [
                '/^62[0-9]{14,17}$/'
            ],
            /**
             * Diners Club card numbers begin with 300 through 305, 36 or 38. All have 14 digits.
             * There are Diners Club cards that begin with 5 and have 16 digits.
             * These are a joint venture between Diners Club and MasterCard, and should be processed like a MasterCard.
             */
            self::TYPE_DINERS => [
                '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
            ],
            /**
             * Discover card numbers begin with 6011, 622126 through 622925, 644 through 649 or 65.
             * All have 16 digits
             */
            self::TYPE_DISCOVER => [
                '/^6011[0-9]{12}$/',
                '/^64[4-9][0-9]{13}$/',
                '/^65[0-9]{14}$/',
                '/^622(12[6-9]|1[3-9][0-9]|[2-8][0-9][0-9]|91[0-9]|92[0-5])[0-9]{10}$/'
            ],
            /**
             * InstaPayment cards begin with 637 through 639 and have 16 digits
             */
            self::TYPE_INSTAPAYMENT => [
                '/^63[7-9][0-9]{13}$/'
            ],
            /**
             * JCB cards beginning with 2131 or 1800 have 15 digits.
             * JCB cards beginning with 35 have 16 digits.
             */
            self::TYPE_JCB => [
                '/^(?:2131|1800|35[0-9]{3})[0-9]{11}$/'
            ],
            /**
             * Laser cards begin with either 6304, 6706, 6709 or 6771 and have between 16 and 19 digits
             */
            self::TYPE_LASER => [
                '/^(6304|670[69]|6771)[0-9]{12,15}$/'
            ],
            /**
             * Maestro cards begin with either 5018, 5020, 5038, 5893, 6304, 6759, 6761, 6762, 6763 or 0604
             * They have between 12 and 19 digits
             */
            self::TYPE_MAESTRO => [
                '/^(5018|5020|5038|6304|6759|6761|676[23]|0604)[0-9]{8,15}$/'
            ],
            /**
             * All MasterCard numbers start with the numbers 51 through 55. All have 16 digits.
             */
            self::TYPE_MASTERCARD => [
                '/^5[1-5][0-9]{14}$/'
            ],
            /**
             * All Visa card numbers start with a 4. New cards have 16 digits. Old cards have 13.
             */
            self::TYPE_VISA => [
                '/^4([0-9]{12}|[0-9]{15})$/'
            ],
        ];
    }

    /**
     * Guess a card type from a number
     *
     * @param string $number
     *
     * @return string|null
     */
    public static function guessType($number)
    {
        foreach (self::getSchemes() as $type => $schemes) {
            foreach ($schemes as $scheme) {
                if (preg_match($scheme, $number)) {
                    return $type;
                }
            }
        }

        return;
    }

    /**
     * Create a card expiration DateTime from a string
     *
     * A card is valid until the last day of the specified month
     *
     * @param string $string MM-YY or MM/YY formatted string
     *
     * @return DateTime
     */
    public static function createExpirationDateFromString($string)
    {
        if (!preg_match('/^(?:0[1-9]|1[0-2])(-|\/)[0-9]{2}$/', $string)) {
            throw new InvalidArgumentException(sprintf('The card expiration string "%s" is invalid', $string));
        }

        $date = DateTime::createFromFormat('!m#y', $string);
        $date
            ->setDate($date->format('Y'), $date->format('n'), $date->format('t'))
            ->setTime(23, 59, 59)
        ;

        return $date;
    }

    /**
     * Constructor
     *
     * @param string      $number
     * @param string      $holderName
     * @param string      $expirationDate
     * @param int|null    $securityCode
     * @param string|null $type
     */
    public function __construct($number, $holderName, $expirationDate, $securityCode = null, $type = null)
    {
        if (!preg_match('/^[0-9]{13,}$/', $number)) {
            throw new InvalidArgumentException('The card number is invalid');
        }

        if (!is_string($expirationDate)) {
            throw new InvalidArgumentException('The card expiration date is invalid');
        }

        if (null !== $securityCode && (!is_int($securityCode) || $securityCode < 0 || $securityCode > 9999 )) {
            throw new InvalidArgumentException('The card security code is invalid');
        }

        $this->number = $number;
        $this->holderName = $holderName;
        $this->expirationDate = self::createExpirationDateFromString($expirationDate);
        $this->securityCode = $securityCode;
        $this->type = (null !== $type) ? $type : self::guessType($number);
    }

    /**
     * Get the string representation
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s %s %s', $this->type, $this->number, $this->expirationDate->format('m-y'));
    }

    /**
     * Get the type
     *
     * @param boolean $asName True to get the type name
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Get holder name
     *
     * @return string|null $holderName
     */
    public function getHolderName()
    {
        return $this->holderName;
    }

    /**
     * Get expiration date
     *
     * @param boolean $formmatted True to return a formatted string
     *
     * @return DateTime|string $expirationDate
     */
    public function getExpirationDate($format = null)
    {
        return $format ? $this->expirationDate->format($format) : $this->expirationDate;
    }

    /**
     * Check whether or not the credit card is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        return new DateTime() > $this->expirationDate;
    }

    /**
     * Get securityCode
     *
     * @return int|null $securityCode
     */
    public function getSecurityCode()
    {
        return $this->securityCode;
    }
}
