<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2005 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Ondrej Jombik <nepto@pobox.sk>                              |
// |         Philippe Jausions <Philippe.Jausions@11abacus.com>           |
// +----------------------------------------------------------------------+
//
// $Id$

class Validate_Finance_CreditCard
{
    /**
     * Validates a number according to Luhn check algorithm
     *
     * This function checks given number according Luhn check
     * algorithm. It is published on several places, also here:
     *
     * @link http://www.webopedia.com/TERM/L/Luhn_formula.html
     * @link http://www.merriampark.com/anatomycc.htm
     * @link http://hysteria.sk/prielom/prielom-12.html#3 (Slovak language)
     * @link http://www.speech.cs.cmu.edu/~sburke/pub/luhn_lib.html (Perl lib)
     *
     * @param  string  $number to check
     * @return bool    TRUE if number is valid, FALSE otherwise
     * @access public
     * @static
     * @author Ondrej Jombik <nepto@pobox.sk>
     */
    function Luhn($number)
    {
        $len_number = strlen($number);
        $sum = 0;
        for ($k = $len_number % 2; $k < $len_number; $k += 2) {
            if ((intval($number{$k}) * 2) > 9) {
                $sum += (intval($number{$k}) * 2) - 9;
            } else {
                $sum += intval($number{$k}) * 2;
            }
        }
        for ($k = ($len_number % 2) ^ 1; $k < $len_number; $k += 2) {
            $sum += intval($number{$k});
        }
        return ($sum % 10) ? false : true;
    }


    /**
     * Validates a credit card number
     *
     * If a type is passed, the card will be checked against it.
     * This method only checks the number locally. No banks or payment
     * gateways are involved.
     * This method doesn't guarantee that the card is legitimate. It merely
     * checks the card number passes a mathematical algorithm.
     *
     * @param string  $creditCard number (spaces and dashes tolerated)
     * @param string  $cardType type/brand of card (case insensitive)
     *               "MasterCard", "Visa", "AMEX", "AmericanExpress",
     *               "American Express", "Diners", "DinersClub", "Diners Club",
     *               "CarteBlanche", "Carte Blanche", "Discover", "JCB",
     *               "EnRoute".
     * @return bool   TRUE if number is valid, FALSE otherwise
     * @access public
     * @static
     * @see Luhn()
     * @author Ondrej Jombik <nepto@pobox.sk>
     * @author Philippe Jausions <Philippe.Jausions@11abacus.com>
     */
    function isValid($creditCard, $cardType = null)
    {
        $creditCard = str_replace(array('-', ' '), '', $creditCard);
        if (($len_number = strlen($creditCard)) < 13
            || !is_numeric($creditCard)) {
            return false;
        }

        // Only apply the Luhn algorithm for cards other than enRoute
        // So check if we have a enRoute card now
        if (strlen($creditCard) != 15
            || (substr($creditCard, 0, 4) != '2014'
                && substr($creditCard, 0, 4) != '2149')) {

            if (!Validate_Finance_CreditCard::Luhn($creditCard)) {
                return false;
            }
        }

        if (!is_null($cardType)) {
            return Validate_Finance_CreditCard::isType($creditCard, $cardType);
        }

        return true;
    }


    /**
     * Validates the credit card number against a type
     *
     * This method only checks for the type marker. It doesn't
     * validate the card number.
     *
     * @param string  $creditCard number (spaces and dashes tolerated)
     * @param string  $cardType type/brand of card (case insensitive)
     *               "MasterCard", "Visa", "AMEX", "AmericanExpress",
     *               "American Express", "Diners", "DinersClub", "Diners Club",
     *               "CarteBlanche", "Carte Blanche", "Discover", "JCB",
     *               "EnRoute".
     * @return bool   TRUE is type matches, FALSE otherwise
     * @access public
     * @static
     * @author Philippe Jausions <Philippe.Jausions@11abacus.com>
     * @link http://www.beachnet.com/~hstiles/cardtype.html
     */
    function isType($creditCard, $cardType) {

        switch (strtoupper($cardType)) {
            case 'MASTERCARD':
                $regex = '/^5[1-5]\d{14}$/';
                break;
            case 'VISA':
                $regex = '/^4(\d{12}|\d{15})$/';
                break;
            case 'AMEX':
            case 'AMERICANEXPRESS':
            case 'AMERICAN EXPRESS':
                $regex = '/^3[47]\d{13}$/';
                break;
            case 'DINERS':
            case 'DINERSCLUB':
            case 'DINERS CLUB':
            case 'CARTEBLANCHE':
            case 'CARTE BLANCHE':
                $regex = '/^3(0[0-5]\d{11}|[68]\d{12})$/';
                break;
            case 'DISCOVER':
                $regex = '/^6011\d{12}$/';
                break;
            case 'JCB':
                $regex = '/^(3\d{15}|(2131|1800)\d{11})$/';
                break;
            case 'ENROUTE':
                $regex = '/^2(014|149)\d{11}$/';
                break;
            default:
                return false;
        }

        $creditCard = str_replace(array('-', ' '), '', $creditCard);
        return (bool)preg_match($regex, $creditCard);
    }
}
?>