<?php
/*
 * This file is part of the Marlon Ogone package.
 *
 * (c) Marlon BVBA <info@marlon.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ogone;

use InvalidArgumentException;

abstract class AbstractPaymentResponse extends AbstractResponse implements PaymentResponse
{
    /**
     * Response Fields
     */
    const FIELD_STATUS = 'STATUS';
    const FIELD_NCSTATUS = 'NCSTATUS';
    const FIELD_NCERROR = 'NCERROR';
    const FIELD_NCERRORPLUS = 'NCERRORPLUS';
    const FIELD_ORDERID = 'ORDERID';
    const FIELD_PAYID = 'PAYID';
    const FIELD_PAYIDSUB = 'PAYIDSUB';
    const FIELD_AMOUNT = 'AMOUNT';
    const FIELD_CURRENCY = 'CURRENCY';
    const FIELD_HTML_ANSWER = 'HTML_ANSWER';
    const FIELD_ALIAS = 'ALIAS';
    const FIELD_CARDNO = 'CARDNO';
    const FIELD_BRAND = 'BRAND';
    const FIELD_BIN = 'BIN';
    const FIELD_PM = 'PM';
    const FIELD_ED = 'ED';

    /**
     * Get Amount
     * @return int Amount in cents
     */
    public function getAmount()
    {
        if (!$this->hasParam(self::FIELD_AMOUNT)) {
            throw new InvalidArgumentException('Parameter AMOUNT does not exist');
        }

        $value = trim($this->getParam(self::FIELD_AMOUNT));

        $withoutDecimals = '#^\d*$#';
        $oneDecimal = '#^\d*\.\d$#';
        $twoDecimals = '#^\d*\.\d\d$#';

        if (preg_match($withoutDecimals, $value)) {
            return (int) ($value.'00');
        }

        if (preg_match($oneDecimal, $value)) {
            return (int) (str_replace('.', '', $value).'0');
        }

        if (preg_match($twoDecimals, $value)) {
            return (int) (str_replace('.', '', $value));
        }

        throw new \InvalidArgumentException("Not a valid currency amount");
    }

    /**
     * Check is payment was successful
     * @deprecated
     * @return bool
     */
    public function isSuccessful()
    {
        return in_array($this->getStatus(), array(
            PaymentResponse::STATUS_AUTHORISED,
            PaymentResponse::STATUS_PAYMENT_REQUESTED,
            PaymentResponse::STATUS_PAYMENT_BY_MERCHANT
        ));
    }

    /**
     * Check is transaction was successful
     * @return bool
     */
    public function isTransactionSuccessful()
    {
        return $this->getErrorCode() === '0';
    }

    /**
     * Check is 3DS required
     * @return bool
     */
    public function isSecurityCheckRequired()
    {
        return $this->getStatus() === 46;
    }

    /**
     * Get Status
     * @return int
     */
    public function getStatus()
    {
        return (int) $this->getParam(self::FIELD_STATUS);
    }

    /**
     * Get Order ID
     * @return string
     */
    public function getOrderId()
    {
        return $this->getParam(self::FIELD_ORDERID);
    }

    /**
     * Get PayID
     * @return string
     */
    public function getPayID()
    {
        return $this->getParam(self::FIELD_PAYID);
    }

    /**
     * Get PayID Sub
     * @return string
     */
    public function getPayIDSub()
    {
        return $this->getParam(self::FIELD_PAYIDSUB);
    }

    /**
     * Get NC Status Code
     * @return string
     */
    public function getNcStatus()
    {
        return $this->getParam(self::FIELD_NCSTATUS);
    }

    /**
     * Get Error Code
     * @return string
     */
    public function getErrorCode()
    {
        return $this->getParam(self::FIELD_NCERROR);
    }

    /**
     * Get Error Message
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->hasParam(self::FIELD_NCERRORPLUS) ? $this->getParam(self::FIELD_NCERRORPLUS) : '';
    }

    /**
     * Get Currency
     * @return string
     */
    public function getCurrency()
    {
        return $this->getParam(self::FIELD_CURRENCY);
    }

    /**
     * Get HTML Code that use for 3DS
     * @return string
     */
    public function getSecurityHTML()
    {
        return base64_decode($this->getParam(self::FIELD_HTML_ANSWER));
    }

    /**
     * Get Alias
     * @return string
     */
    public function getAlias()
    {
        return $this->getParam(self::FIELD_ALIAS);
    }

    /**
     * Get Masked Card Number
     * @return string
     */
    public function getCardno()
    {
        return $this->getParam(self::FIELD_CARDNO);
    }

    /**
     * Get Bin of Card number
     * @return string
     */
    public function getBin()
    {
        // "Redirect" method don't returns BIN parameter
        return $this->hasParam(self::FIELD_BIN) ? $this->getParam(self::FIELD_BIN) : null;
    }

    /**
     * Get Brand
     * @return string
     */
    public function getBrand()
    {
        return $this->getParam(self::FIELD_BRAND);
    }

    /**
     * Get Payment Method
     * @return string
     */
    public function getPm()
    {
        return $this->getParam(self::FIELD_PM);
    }

    /**
     * Get Expire Date
     * @return string
     */
    public function getEd()
    {
        return $this->getParam(self::FIELD_ED);
    }
}
