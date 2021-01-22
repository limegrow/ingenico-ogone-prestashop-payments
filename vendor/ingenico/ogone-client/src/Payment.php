<?php

namespace IngenicoClient;

/**
 * Class Payment
 * @method string getNcStatus()
 * @method string getNcError()
 * @method string getNcErrorPlus()
 * @method string getOrderId()
 * @method string getPayId()
 * @method string getPayIdSub()
 * @method string getAmount()
 * @method string getCurrency()
 * @method string getHtmlAnswer()
 * @method string getAlias()
 * @method string getCardNo()
 * @method string getCn()
 * @method string getBrand()
 * @method string getBin()
 * @method string getPm()
 * @method string getEd()
 * @method bool hasAlias()
 * @package IngenicoClient
 */
class Payment extends Data
{
    /**
     * Payment Info Fields
     */
    const FIELD_STATUS = 'STATUS';
    const FIELD_NC_STATUS = 'NCSTATUS';
    const FIELD_NC_ERROR = 'NCERROR';
    const FIELD_NC_ERROR_PLUS = 'NCERRORPLUS';
    const FIELD_ORDER_ID = 'ORDERID';
    const FIELD_PAY_ID = 'PAYID';
    const FIELD_PAY_ID_SUB = 'PAYIDSUB';
    const FIELD_AMOUNT = 'AMOUNT';
    const FIELD_CURRENCY = 'CURRENCY';
    const FIELD_HTML_ANSWER = 'HTML_ANSWER';
    const FIELD_ALIAS = 'ALIAS';
    const FIELD_CARD_NO = 'CARDNO';
    const FIELD_BRAND = 'BRAND';
    const FIELD_BIN = 'BIN';
    const FIELD_PM = 'PM';
    const FIELD_ED = 'ED';

    const FIELD_AAVADDRESS = 'AAVADDRESS';
    const FIELD_AAVCHECK = 'AAVCHECK';
    const FIELD_AAVMAIL = 'AAVMAIL';
    const FIELD_AAVNAME = 'AAVNAME';
    const FIELD_AAVPHONE = 'AAVPHONE';
    const FIELD_AAVZIP = 'AAVZIP';

    const FIELD_ACCEPTANCE = 'ACCEPTANCE';
    const FIELD_BIC = 'BIC';
    const FIELD_CCCTY = 'CCCTY';
    const FIELD_CN = 'CN';
    const FIELD_COLLECTOR_BIC = 'COLLECTOR_BIC';
    const FIELD_COLLECTOR_IBAN = 'COLLECTOR_IBAN';

    const FIELD_COMPLUS = 'COMPLUS';
    const FIELD_CREATION_STATUS = 'CREATION_STATUS';
    const FIELD_CREDITDEBIT = 'CREDITDEBIT';
    const FIELD_CVCCHECK = 'CVCCHECK';
    const FIELD_DCC_COMMPERCENTAGE = 'DCC_COMMPERCENTAGE';
    const FIELD_DCC_CONVAMOUNT = 'DCC_CONVAMOUNT';
    const FIELD_DCC_CONVCCY = 'DCC_CONVCCY';
    const FIELD_DCC_EXCHRATE = 'DCC_EXCHRATE';
    const FIELD_DCC_EXCHRATESOURCE = 'DCC_EXCHRATESOURCE';
    const FIELD_DCC_EXCHRATETS = 'DCC_EXCHRATETS';
    const FIELD_DCC_INDICATOR = 'DCC_INDICATOR';
    const FIELD_DCC_MARGINPERCENTAGE = 'DCC_MARGINPERCENTAGE';
    const FIELD_DCC_VALIDHOURS = 'DCC_VALIDHOURS';

    const FIELD_DEVICEID = 'DEVICEID';
    const FIELD_DIGESTCARDNO = 'DIGESTCARDNO';
    const FIELD_ECI = 'ECI';
    const FIELD_EMAIL = 'EMAIL';
    const FIELD_ENCCARDNO = 'ENCCARDNO';
    const FIELD_FXAMOUNT = 'FXAMOUNT';
    const FIELD_FXCURRENCY = 'FXCURRENCY';

    const FIELD_IP = 'IP';
    const FIELD_IPCTY = 'IPCTY';
    const FIELD_MANDATEID = 'MANDATEID';
    const FIELD_MOBILEMODE = 'MOBILEMODE';

    const FIELD_NBREMAILUSAGE = 'NBREMAILUSAGE';
    const FIELD_NBRIPUSAGE = 'NBRIPUSAGE';
    const FIELD_NBRIPUSAGE_ALLTX = 'NBRIPUSAGE_ALLTX';
    const FIELD_NBRUSAGE = 'NBRUSAGE';

    const FIELD_PAYMENT_REFERENCE = 'PAYMENT_REFERENCE';
    const FIELD_SCO_CATEGORY = 'SCO_CATEGORY';
    const FIELD_SCORING = 'SCORING';
    const FIELD_SEQUENCETYPE = 'SEQUENCETYPE';
    const FIELD_SIGNDATE = 'SIGNDATE';
    const FIELD_SUBBRAND = 'SUBBRAND';
    const FIELD_SUBSCRIPTION_ID = 'SUBSCRIPTION_ID';
    const FIELD_TICKET = 'TICKET';
    const FIELD_TRXDATE = 'TRXDATE';
    const FIELD_VC = 'VC';

    /**
     * Payment constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = $class->getConstants();

        foreach ($data as $key => $value) {
            $keyName = array_search(strtoupper($key), $constants);
            $this->setData(strtolower(str_replace('FIELD_', '', $keyName)), $value);
        }

        // Workaround for order_id, pay_id, pay_sub_id
        foreach (['order_id', 'pay_id', 'pay_id_sub'] as $key) {
            if (isset($data[$key])) {{
                $keyName = array_search(strtoupper(str_replace('_', '', $key)), $constants);
                $this->setData(strtolower(str_replace('FIELD_', '', $keyName)), $data[$key]);
            }}
        }
    }

    /**
     * Check is transaction was successful
     * @return bool
     */
    public function isTransactionSuccessful()
    {
        return $this->getNcError() === '0' || empty($this->getNcError());
    }

    /**
     * Check is payment was successful
     * @return bool
     */
    public function isPaymentSuccessful()
    {
        return in_array($this->getPaymentStatus(), [
            IngenicoCoreLibrary::STATUS_PENDING,
            IngenicoCoreLibrary::STATUS_AUTHORIZED,
            IngenicoCoreLibrary::STATUS_CAPTURED,
            IngenicoCoreLibrary::STATUS_CAPTURE_PROCESSING,
        ]);
    }

    /**
     * Check if payment was cancelled
     * @return bool
     */
    public function isPaymentCancelled()
    {
        return $this->getPaymentStatus() === IngenicoCoreLibrary::STATUS_CANCELLED;
    }

    /**
     * Get Status
     * @return int|false
     */
    public function getStatus()
    {
        if (!$this->hasData(strtolower(self::FIELD_STATUS))) {
            //return false;
        }

        return (int) $this->getData(strtolower(self::FIELD_STATUS));
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
     * Pseudo for getNCError()
     * @return string
     */
    public function getErrorCode()
    {
        return $this->getNcError();
    }

    /**
     * Pseudo for getNCErrorPlus()
     * @return string
     */
    public function getErrorMessage()
    {
        $message = $this->getNcErrorPlus();
        if (empty($message)) {
            $message = IngenicoCoreLibrary::getErrorDescription($this->getErrorCode());
        }

        return $message;
    }

    /**
     * Get HTML Code that use for 3DS
     * @return string
     */
    public function getSecurityHTML()
    {
        return base64_decode($this->getHtmlAnswer());
    }

    /**
     * Set Payment Status (string).
     * @see IngenicoCoreLibrary::STATUS_PENDING
     * @see IngenicoCoreLibrary::STATUS_AUTHORIZED
     * @see IngenicoCoreLibrary::STATUS_CAPTURED
     * @see IngenicoCoreLibrary::STATUS_CANCELLED
     * @see IngenicoCoreLibrary::STATUS_REFUNDED
     * @see IngenicoCoreLibrary::STATUS_ERROR
     * @see IngenicoCoreLibrary::STATUS_UNKNOWN
     * @param string $paymentStatus
     * @return $this
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->setData('payment_status', $paymentStatus);

        return $this;
    }

    /**
     * Get Payment Status (string).
     * @return string
     */
    public function getPaymentStatus()
    {
        if ($this->hasData('payment_status')) {
            return $this->getData('payment_status');
        }

        $paymentStatus = IngenicoCoreLibrary::getStatusByCode($this->getStatus());
        $this->setData('payment_status', $paymentStatus);

        return $paymentStatus;
    }
}
