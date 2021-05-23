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
use Psr\Log\LoggerInterface;
use RuntimeException;
use BadMethodCallException;
use Ogone\ShaComposer\ShaComposer;

/**
 * Class AbstractRequest
 * @method $this setEmail($value)
 * @method mixed getEmail()
 * @method $this setOrderid($value)
 * @method mixed getOrderid()
 * @method $this setCom($value)
 * @method mixed getCom()
 * @method $this setAmount($value)
 * @method mixed getAmount()
 * @method $this setCurrency($value)
 * @method mixed getCurrency()
 * @method $this setOwneraddress($value)
 * @method mixed getOwneraddress()
 * @method $this setOwnerzip($value)
 * @method mixed getOwnerzip()
 * @method $this setOwnertown($value)
 * @method mixed getOwnertown()
 * @method $this setOwnercountry($value)
 * @method mixed getOwnercountry()
 * @method $this setOwnercty($value)
 * @method mixed getOwnercty()
 * @method $this setOwnertelno($value)
 * @method mixed getOwnertelno()
 * @method $this setComplus($value)
 * @method mixed getComplus()
 * @method $this setTp($value)
 * @method mixed getTp()
 * @method $this setCuid($value)
 * @method mixed getCuid()
 * @method $this setCivility($value)
 * @method mixed getCivility()
 * @method $this setRemoteAddr($value)
 * @method mixed getRemoteAddr()
 * @method $this setOrdershipmeth($value)
 * @method mixed getOrdershipmeth()
 * @method $this setOrdershipcost($value)
 * @method mixed getOrdershipcost()
 * @method $this setOrdershiptax($value)
 * @method mixed getOrdershiptax()
 * @method $this setOrdershiptaxcode($value)
 * @method mixed getOrdershiptaxcode()
 * @method $this setDatein($value)
 * @method mixed getDatein()
 * @method $this setRefCustomerref($value)
 * @method mixed getRefCustomerref()
 * @method $this setAddrmatch($value)
 * @method mixed getAddrmatch()
 * @method $this setEcomBilltoPostalCity($value)
 * @method mixed getEcomBilltoPostalCity()
 * @method $this setEcomBilltoPostalCounty($value)
 * @method mixed getEcomBilltoPostalCounty()
 * @method $this setEcomBilltoPostalCountrycode($value)
 * @method mixed getEcomBilltoPostalCountrycode()
 * @method $this setEcomBilltoPostalNameFirst($value)
 * @method mixed getEcomBilltoPostalNameFirst()
 * @method $this setEcomBilltoPostalNameLast($value)
 * @method mixed getEcomBilltoPostalNameLast()
 * @method $this setEcomBilltoPostalPostalcode($value)
 * @method mixed getEcomBilltoPostalPostalcode()
 * @method $this setEcomBilltoPostalStreetLine1($value)
 * @method mixed getEcomBilltoPostalStreetLine1()
 * @method $this setEcomBilltoPostalStreetLine2($value)
 * @method mixed getEcomBilltoPostalStreetLine2()
 * @method $this setEcomBilltoPostalStreetLine3($value)
 * @method mixed getEcomBilltoPostalStreetLine3()
 * @method $this setEcomBilltoPostalStreetNumber($value)
 * @method mixed getEcomBilltoPostalStreetNumber()
 * @method $this setEcomBilltoTelecomPhoneNumber($value)
 * @method mixed getEcomBilltoTelecomPhoneNumber()
 * @method $this setEcomBilltoTelecomMobileNumber($value)
 * @method mixed getEcomBilltoTelecomMobileNumber()
 * @method $this setEcomBilltoCompany($value)
 * @method mixed getEcomBilltoCompany()
 * @method $this setEcomShiptoPostalCity($value)
 * @method mixed getEcomShiptoPostalCity()
 * @method $this setEcomShiptoPostalCounty($value)
 * @method mixed getEcomShiptoPostalCounty()
 * @method $this setEcomShiptoPostalCountrycode($value)
 * @method mixed getEcomShiptoPostalCountrycode()
 * @method $this setEcomShiptoPostalNameFirst($value)
 * @method mixed getEcomShiptoPostalNameFirst()
 * @method $this setEcomShiptoPostalNameLast($value)
 * @method mixed getEcomShiptoPostalNameLast()
 * @method $this setEcomShiptoPostalPostalcode($value)
 * @method mixed getEcomShiptoPostalPostalcode()
 * @method $this setEcomShiptoPostalStreetLine1($value)
 * @method mixed getEcomShiptoPostalStreetLine1()
 * @method $this setEcomShiptoPostalStreetLine2($value)
 * @method mixed getEcomShiptoPostalStreetLine2()
 * @method $this setEcomShiptoPostalStreetLine3($value)
 * @method mixed getEcomShiptoPostalStreetLine3()
 * @method $this setEcomShiptoPostalStreetNumber($value)
 * @method mixed getEcomShiptoPostalStreetNumber()
 * @method $this setEcomShiptoDob($value)
 * @method mixed getEcomShiptoDob()
 * @method $this setEcomConsumerGender($value)
 * @method mixed getEcomConsumerGender()
 * @method $this setEcomBilltoPostalNamePrefix($value)
 * @method mixed getEcomBilltoPostalNamePrefix()
 * @method $this setEcomShipitoPostalNamePrefix($value)
 * @method mixed getEcomShipitoPostalNamePrefix()
 * @method $this setEcomShiptoTva($value)
 * @method mixed getEcomShiptoTva()
 * @method $this setEcomShiptoCompany($value)
 * @method mixed getEcomShiptoCompany()
 * @method $this setEcomShiptoTelecomFaxNumber($value)
 * @method mixed getEcomShiptoTelecomFaxNumber()
 * @method $this setEcomShiptoTelecomPhoneNumber($value)
 * @method mixed getEcomShiptoTelecomPhoneNumber()
 * @method $this setEcomShiptoTelecomMobileNumber($value)
 * @method mixed getEcomShiptoTelecomMobileNumber()
 * @method $this setEcomShiptoOnlineEmail($value)
 * @method mixed getEcomShiptoOnlineEmail()
 * @method $this setRefCustomerid($value)
 * @method mixed getRefCustomerid()
 * @method $this setCostcenter($value)
 * @method mixed getCostcenter()
 * @method $this setDevice($value)
 * @method mixed getDevice()
 * @method $this setEcomShipmethoddetails($value)
 * @method mixed getEcomShipmethoddetails()
 * @method $this setEcomEstimateddeliverydate($value)
 * @method mixed getEcomEstimateddeliverydate()
 * @method $this setEcomShipmethodspeed($value)
 * @method mixed getEcomShipmethodspeed()
 *
 * @package Ogone
 */
abstract class AbstractRequest implements Request
{
    const WIN3DS_MAIN = 'MAINW';
    const WIN3DS_POPUP = 'POPUP';
    const WIN3DS_POPIX = 'POPIX';

    /** @var ShaComposer */
    protected $shaComposer;

    protected $ogoneUri;

    protected $parameters = array();

    /** @var LoggerInterface|null */
    protected $logger;



    protected $ogoneFields = array(
        'orig', 'shoppingcartextensionid', 'pspid', 'orderid', 'com', 'amount', 'currency', 'language', 'cn', 'email',
        'cardno', 'cvc', 'ed', 'ownerzip', 'owneraddress', 'ownercty', 'ownertown', 'ownertelno',
        'homeurl', 'catalogurl', 'accepturl', 'declineurl', 'exceptionurl', 'cancelurl', 'backurl',
        'complus', 'paramplus', 'pm', 'brand', 'title', 'bgcolor', 'txtcolor', 'tblbgcolor',
        'tbltxtcolor', 'buttonbgcolor', 'buttontxtcolor', 'logo', 'fonttype', 'tp', 'paramvar',
        'alias', 'aliasoperation', 'aliasusage', 'aliaspersistedafteruse', 'device', 'pmlisttype',
        'ecom_payment_card_verification', 'operation', 'withroot', 'remote_addr', 'rtimeout',
        'pmlist', 'exclpmlist', 'creditdebit', 'userid',
        // DirectLink with 3-D Secure: Extra request parameters.
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/directlink-3-d/3-d-transaction-flow-via-directlink#extrarequestparameters
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/directlink-3-d/version%202#3dtransactionflowviadirectlink
        // https://payment-services.ingenico.com/ogone/support/~/media/kdb/integration%20guides/directlink%203ds%20v2/odl3dsnew%20parameters%20name%2021en.ashx?la=en
        'flag3d', 'http_accept', 'http_user_agent', 'win3ds',
        // MPI 2.0 (3DS V.2)
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/directlink-3-d/3-d%20secure%20v2
        'browseracceptheader', 'browsercolordepth', 'browserjavaenabled', 'browserlanguage', 'browserscreenheight',
        'browserscreenwidth', 'browsertimezone', 'browseruseragent',
        // Optional integration data: Delivery and Invoicing data.
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/additional-data/delivery-and-invoicing-data
        // Klarna
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/klarna
        // After pay
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/afterpay
        'addrmatch', 'civility', 'cuid', 'ecom_billto_postal_city', 'ecom_billto_postal_countrycode',
        'ecom_billto_postal_name_first', 'ecom_billto_postal_name_last', 'ecom_billto_postal_postalcode',
        'ecom_billto_postal_street_line1', 'ecom_billto_postal_street_line2', 'ecom_billto_postal_street_line3',
        'ecom_billto_postal_street_number', 'ecom_shipto_dob',
        'ecom_shipto_online_email', 'ecom_shipto_postal_city', 'ecom_shipto_postal_countrycode',
        'ecom_shipto_postal_name_first', 'ecom_shipto_postal_name_last', 'ecom_shipto_postal_name_prefix',
        'ecom_shipto_postal_postalcode', 'ecom_shipto_postal_state',
        'ecom_shipto_postal_street_line1', 'ecom_shipto_postal_street_line2','ecom_shipto_postal_street_line3',
        'ecom_shipto_postal_street_number', 'ecom_consumer_gender',
        'ecom_billto_postal_name_prefix', 'ecom_shipto_postal_name_prefix',
        'ecom_billto_postal_state', 'ecom_shipto_postal_state',
        'ecom_billto_tva', 'ecom_shipto_tva',
        'ecom_billto_company', 'ecom_shipto_company',
        'ecom_billto_telecom_fax_number', 'ecom_shipto_telecom_fax_number',
        'ecom_billto_telecom_phone_number', 'ecom_shipto_telecom_phone_number',
        // Klarna
        'ecom_billto_postal_county','ecom_shipto_postal_county',
        'ecom_shipto_county',
        'ordershipcost', 'ordershiptax', 'ordershipmeth', 'ordershiptaxcode',
        'datein', 'ref_customerref', 'costcenter', 'ref_customerid',
        // Optional integration data: Order data ("ITEM" parameters).
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/additional-data/order-data
        'itemattributes*', 'itemcategory*', 'itemcomments*', 'itemdesc*', 'itemdiscount*',
        'itemid*', 'itemname*', 'itemprice*', 'itemquant*', 'itemquantorig*',
        'itemunitofmeasure*', 'itemvat*', 'itemvatcode*', 'itemweight*', 'taxincluded*',
        // Optional integration data: Travel data.
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/additional-data/travel-data
        'datatype', 'aiairname', 'aitinum', 'aitidate', 'aiconjti', 'aipasname',
        'aiextrapasname*', 'aichdet', 'aiairtax', 'aivatamnt', 'aivatappl', 'aitypch',
        'aieycd', 'aiirst', 'aiorcity*', 'aiorcityl*', 'aidestcity*', 'aidestcityl*',
        'aistopov*', 'aicarrier*', 'aibookind*', 'aiflnum*', 'aifldate*', 'aiclass*',
        // Subscription Manager.
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/subscription-manager/via-e-commerce-and-directlink#input
        'subscription_id', 'sub_amount', 'sub_com', 'sub_orderid', 'sub_period_unit',
        'sub_period_number', 'sub_period_moment', 'sub_startdate', 'sub_enddate',
        'sub_status', 'sub_comment',
        // Limonetik
        // @see https://epayments-support.ingenico.com/en/payment-methods/alternative-payment-methods/limonetik
        'ecom_billto_postal_name_prefix', 'ecom_billto_telecom_phone_number',
        'ecom_billto_telecom_mobile_number', 'ecom_shipto_telecom_mobile_number', 'ecom_shipmethod',
        'ecom_shipmethoddetails', 'ecom_estimateddeliverydate', 'ecom_shipmethodspeed'
    );

    /**
     * Sets Logger.
     *
     * @param LoggerInterface|null $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /** @return string */
    public function getShaSign()
    {
        return $this->shaComposer->compose($this->toArray());
    }

    /** @return string */
    public function getOgoneUri()
    {
        return $this->ogoneUri;
    }

    /**
     * Ogone uri to send the customer to.
     * Usually PaymentRequest::TEST or PaymentRequest::PRODUCTION
     *
     * @param string $ogoneUri
     * @return $this
     */
    public function setOgoneUri($ogoneUri)
    {
        $this->validateOgoneUri($ogoneUri);
        $this->ogoneUri = $ogoneUri;

        return $this;
    }

    /**
     * Set Shopping Cart Extension Id
     * @param $id
     * @return $this
     */
    public function setShoppingCartExtensionId($id)
    {
        $this->parameters['shoppingcartextensionid'] = $id;

        return $this;
    }

    /**
     * Get Shopping Cart Extension Id
     * @return mixed
     */
    public function getShoppingCartExtensionId()
    {
        return isset($this->parameters['shoppingcartextensionid']) ?: $this->parameters['shoppingcartextensionid'];
    }

    /**
     * Set Orig (Plugin version number)
     * @param $id
     * @return $this
     */
    public function setOrig($id)
    {
        $this->parameters['orig'] = $id;

        return $this;
    }

    /**
     * Get Orig (Plugin version number)
     * @return mixed
     */
    public function getOrig()
    {
        return isset($this->parameters['orig']) ?: $this->parameters['orig'];
    }

    /**
     * @param $pspid
     * @return $this
     */
    public function setPspid($pspid)
    {
        if (strlen($pspid) > 30) {
            throw new InvalidArgumentException('PSPId is too long');
        }
        $this->parameters['pspid'] = $pspid;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSecure()
    {
        return $this->setWin3DS(self::WIN3DS_MAIN);
    }

    /**
     * ISO code eg nl_BE.
     *
     * @param $language
     * @return $this
     */
    public function setLanguage($language)
    {
        // Workaround for Denmark browsers
        if (strtolower($language) === 'da_dk') {
            $language = 'dk_DK';
        }

        $this->parameters['language'] = $language;

        return $this;
    }

    /**
     * Alias for setCn
     *
     * @param $customername
     * @return $this
     */
    public function setCustomername($customername)
    {
        $this->setCn($customername);

        return $this;
    }

    /**
     * @param $cn
     * @return $this
     */
    public function setCn($cn)
    {
        $this->parameters['cn'] = str_replace(array("'", '"'), '', $cn); // replace quotes

        return $this;
    }

    /**
     * @param $homeurl
     * @return $this
     */
    public function setHomeurl($homeurl)
    {
        if (!empty($homeurl)) {
            $this->validateUri($homeurl);
        }
        $this->parameters['homeurl'] = $homeurl;

        return $this;
    }

    /**
     * @param $accepturl
     * @return $this
     */
    public function setAccepturl($accepturl)
    {
        $this->validateUri($accepturl);
        $this->parameters['accepturl'] = $accepturl;

        return $this;
    }

    /**
     * @param $declineurl
     * @return $this
     */
    public function setDeclineurl($declineurl)
    {
        $this->validateUri($declineurl);
        $this->parameters['declineurl'] = $declineurl;

        return $this;
    }

    /**
     * @param $exceptionurl
     * @return $this
     */
    public function setExceptionurl($exceptionurl)
    {
        $this->validateUri($exceptionurl);
        $this->parameters['exceptionurl'] = $exceptionurl;

        return $this;
    }

    /**
     * @param $cancelurl
     * @return $this
     */
    public function setCancelurl($cancelurl)
    {
        $this->validateUri($cancelurl);
        $this->parameters['cancelurl'] = $cancelurl;

        return $this;
    }

    /**
     * @param $backurl
     * @return $this
     */
    public function setBackurl($backurl)
    {
        $this->validateUri($backurl);
        $this->parameters['backurl'] = $backurl;

        return $this;
    }

    /**
     * Alias for setParamplus
     *
     * @param array $feedbackParams
     * @return AbstractRequest
     */
    public function setFeedbackParams(array $feedbackParams)
    {
        return $this->setParamplus($feedbackParams);
    }

    /**
     * @param array $paramplus
     * @return $this
     */
    public function setParamplus(array $paramplus)
    {
        $this->parameters['paramplus'] = http_build_query($paramplus);

        return $this;
    }

    /**
     * Set Flag3D
     * Instructs system to perform 3-D Secure identification if necessary.
     *
     * @param $flag
     * @return $this
     */
    public function setFlag3D($flag)
    {
        $this->validateYesNo($flag);
        $this->parameters['flag3d'] = $flag;

        return $this;
    }

    /**
     * Set HTTP Accept
     * The Accept request header field in the cardholder browser, used to specify certain media types which are acceptable for the response.
     * This value is used by the issuer to check if the cardholder browser is compatible with the issuer identification system.
     *
     * @param $http_accept
     * @return $this
     */
    public function setHttpAccept($http_accept)
    {
        $this->parameters['http_accept'] = $http_accept;

        return $this;
    }

    /**
     * Set HTTP User Agent
     * The User-Agent request-header field in the cardholder browser, containing information about the user agent originating the request.
     * This value is used by the issuer to check if the cardholder browser is compatible with the issuer identification system.
     *
     * @param $http_user_agent
     * @return $this
     */
    public function setHttpUserAgent($http_user_agent)
    {
        $this->parameters['http_user_agent'] = $http_user_agent;

        return $this;
    }

    /**
     * Set WIN3DS Value
     * Way to show the identification page to the customer.
     *
     * @param $win3ds
     * @return $this
     */
    public function setWin3DS($win3ds)
    {
        $this->validateWin3DS($win3ds);
        $this->parameters['win3ds'] = $win3ds;

        return $this;
    }

    public function validate()
    {
        foreach ($this->getRequiredFields() as $field) {
            if (empty($this->parameters[$field])) {
                throw new RuntimeException("$field can not be empty");
            }
        }

        if ($this->logger) {
            $this->logger->debug(sprintf('Request %s', get_class($this)), $this->parameters);
        }

        // Validate fields
        foreach ($this->getData() as $key => $value) {
            $this->validateField($key, $value);
        }
    }

    protected function validateUri($uri)
    {
        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Uri is not valid');
        }
        if (strlen($uri) > 200) {
            throw new InvalidArgumentException('Uri is too long');
        }
    }

    protected function validateOgoneUri($uri)
    {
        $this->validateUri($uri);
    }

    /**
     * Validate Y/N Values.
     *
     * @deprecated Use validateField() instead of
     * @param $value
     */
    protected function validateYesNo($value)
    {
        if (!in_array(strtoupper($value), ['Y', 'N'])) {
            throw new InvalidArgumentException("Value should be 'Y' or 'N'.");
        }
    }

    /**
     * Validate Win3DS.
     *
     * @deprecated Use validateField() instead of
     * @param $win3ds
     */
    protected function validateWin3DS($win3ds)
    {
        if (!in_array(strtoupper($win3ds), [self::WIN3DS_MAIN, self::WIN3DS_POPUP, self::WIN3DS_POPIX])) {
            throw new InvalidArgumentException('Win3DS is not valid');
        }
    }

    /**
     * Allows setting ogone parameters that don't have a setter -- usually only
     * the unimportant ones like bgcolor, which you'd call with setBgcolor().
     *
     * @param $method
     * @param $args
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $field = strtolower($this->_underscore(substr($method,3)));
                if (array_key_exists($field, $this->parameters)) {
                    return $this->parameters[$field];
                }
                break;
            case 'set' :
                $field = strtolower($this->_underscore(substr($method,3)));
                if (in_array($field, $this->ogoneFields)) {
                    $this->parameters[$field] = $args[0];
                    return $this;
                }
                break;
            case 'uns' :
                $key = $this->_underscore(substr($method,3));
                $this->unsData($key);
                return $this;
            case 'has' :
                $field = strtolower($this->_underscore(substr($method,3)));
                return array_key_exists($field, $this->parameters);
        }

        throw new BadMethodCallException("Unknown method $method");
    }

    /**
     * Check is data exists
     * @param $key
     * @return bool
     */
    public function hasData($key)
    {
        return isset($this->parameters[$key]);
    }

    /**
     * Get Data
     * @param mixed $key
     * @return array|mixed
     */
    public function getData($key = null)
    {
        if (!$key) {
            return $this->parameters;
        }

        return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
    }

    /**
     * Set Data
     * @param $key
     * @param null $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $key1 => $value1) {
                if (is_scalar($key1)) {
                    $this->setData($key1, $value1);
                }
            }
        } elseif (is_scalar($key)) {
            $this->parameters[$key] = $value;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid type for index %s', var_export($key, true)));
        }

        return $this;
    }

    /**
     * Unset Data
     * @param $key
     * @return $this
     */
    public function unsData($key)
    {
        if ($this->hasData($key)) {
            unset($this->parameters[$key]);
        }

        return $this;
    }

    /**
     * To Array
     * @return array
     */
    public function toArray()
    {
        $this->validate();

        $params = array_filter($this->parameters, 'strlen');
        $params = array_change_key_case($params, CASE_UPPER);

        foreach ($params as $key => $value) {
            // Convert to text field
            if (is_object($value) && method_exists($value, '__toString')) {
                $params[$key] = $value->__toString();
            }
        }

        return $params;
    }

    /**
     * Validate Field
     * @param string $key
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function validateField($key, $value)
    {
        $validator = new Validator($key, $value, [
            'data' => $this->parameters
        ]);

        $validator->validate();
    }

    /**
     * Converts field names for setters and getters
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        $result = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $name));
        return $result;
    }
}
