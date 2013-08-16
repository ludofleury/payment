<?php

namespace Playbloom\Payment;

/**
 * Payment card interface
 *
 * Any kind of debit, credit, charge card
 *
* @author Ludovic Fleury <ludo.fleury@gmail.com>
 */
interface CardInterface
{
    const TYPE_AMEX = 'amex';             // American Express
    const TYPE_CUP = 'cup';               // China UnionPay
    const TYPE_DINERS = 'diners';         // Diners Club
    const TYPE_DISCOVER = 'discover';     // Discover International
    const TYPE_INSTAPAYMENT = 'instapay'; // Instapayment
    const TYPE_JCB = 'jcb';               // Japan Credit Bureau
    const TYPE_LASER = 'laser';           // Laser
    const TYPE_MAESTRO = 'maestro';       // Maestro
    const TYPE_MASTERCARD = 'mastercard'; // Mastercard
    const TYPE_VISA = 'visa';             // Visa

    /**
     * Get the string representation
     *
     * @return string
     */
    public function __toString();

    /**
     * Return the ISO/IEC 7812 Primary Account Number (PAN) ~16 digits
     *
     * The "*" should be used as a characters remplacement
     * in a non PCI-DSS compliant environment
     *
     * @see http://en.wikipedia.org/wiki/ISO/IEC_7812
     * @see http://en.wikipedia.org/wiki/List_of_Issuer_Identification_Numbers
     */
    public function getNumber();

    /**
     * Return the card holder name
     *
     * @return string
     */
    public function getHolderName();

    /**
     * Return the expiration date
     *
     * @return DateTime|string The string format should be MM-YY
     */
    public function getExpirationDate();

    /**
     * Check whether or not the card is expired
     *
     * @return boolean
     */
    public function isExpired();

    /**
     * Return the Card Security Code (CSC)
     *
     * Also known as CSC, CVD, CVV, CVV2, CVVC, CVC, CVC2, V-code, CCV, CID...
     * Required in Card Not Present (CNP | MO/TO) transaction
     * The "*" should be used as a characters remplacement
     * in a non PCI-DSS compliant environment
     *
     * @see http://en.wikipedia.org/wiki/Card_Security_Code
     *
     * @return int
     */
    public function getSecurityCode();
}
