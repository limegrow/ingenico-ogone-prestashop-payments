<?php

namespace IngenicoClient;

use InvalidArgumentException;

/**
 * Class Order
 * @method mixed getOrderId()
 * @method float getAmount()
 * @method mixed getCurrency()
 * @method $this setPayId($value)
 * @method mixed getPayId()
 * @method $this setStatus($value)
 * @method mixed getStatus()
 * @method $this setCreatedAt($value)
 * @method mixed getCreatedAt()
 * @method $this setHttpAccept($value)
 * @method $this setHttpUserAgent($value)
 * @method $this setTotalCaptured($value)
 * @method float getTotalCaptured()
 * @method $this setTotalRefunded($value)
 * @method float getTotalRefunded()
 * @method $this setTotalCancelled($value)
 * @method float getTotalCancelled()
 * @method $this setCustomerId($value)
 * @method mixed getCustomerId()
 * @method $this setCustomerIp($value)
 * @method mixed getCustomerIp()
 * @method $this setCustomerDob($value)
 * @method mixed getCustomerDob()
 * @method $this setCustomerCivility($value)
 * @method mixed getCustomerCivility()
 * @method $this setCustomerGender($value)
 * @method mixed getCustomerGender()
 * @method $this setCustomerRegistrationNumber($value)
 * @method mixed getCustomerRegistrationNumber()
 * @method $this setIsShippingSame($value)
 * @method bool getIsShippingSame()
 * @method $this setBillingCustomerTitle($value)
 * @method mixed getBillingCustomerTitle()
 * @method $this setBillingCountryCode($value)
 * @method $this setBillingCountry($value)
 * @method mixed getBillingCountry()
 * @method $this setBillingAddress1($value)
 * @method mixed getBillingAddress1()
 * @method $this setBillingAddress2($value)
 * @method mixed getBillingAddress2()
 * @method $this setBillingAddress3($value)
 * @method mixed getBillingAddress3()
 * @method $this setBillingCity($value)
 * @method mixed getBillingCity()
 * @method $this setBillingState($value)
 * @method mixed getBillingState()
 * @method $this setBillingPostcode($value)
 * @method mixed getBillingPostcode()
 * @method $this setBillingPhone($value)
 * @method mixed getBillingPhone()
 * @method $this setBillingFax($value)
 * @method mixed getBillingFax()
 * @method $this setBillingEmail($value)
 * @method mixed getBillingEmail()
 * @method $this setBillingFirstName($value)
 * @method mixed getBillingFirstName()
 * @method $this setBillingLastName($value)
 * @method mixed getBillingLastName()
 * @method $this setBillingStreetNumber($value)
 * @method mixed getBillingStreetNumber()
 * @method $this setShippingCustomerTitle($value)
 * @method mixed getShippingCustomerTitle()
 * @method $this setShippingCountryCode($value)
 * @method $this setShippingCountry($value)
 * @method mixed getShippingCountry()
 * @method $this setShippingAddress1($value)
 * @method mixed getShippingAddress1()
 * @method $this setShippingAddress2($value)
 * @method mixed getShippingAddress2()
 * @method $this setShippingAddress3($value)
 * @method mixed getShippingAddress3()
 * @method $this setShippingCity($value)
 * @method mixed getShippingCity()
 * @method $this setShippingState($value)
 * @method mixed getShippingState()
 * @method $this setShippingPostcode($value)
 * @method mixed getShippingPostcode()
 * @method $this setShippingPhone($value)
 * @method mixed getShippingPhone()
 * @method $this setShippingFax($value)
 * @method mixed getShippingFax()
 * @method $this setShippingEmail($value)
 * @method mixed getShippingEmail()
 * @method $this setShippingFirstName($value)
 * @method mixed getShippingFirstName()
 * @method $this setShippingLastName($value)
 * @method mixed getShippingLastName()
 * @method $this setShippingStreetNumber($value)
 * @method mixed getShippingStreetNumber()
 * @method $this setShippingMethod($value)
 * @method mixed getShippingMethod()
 * @method $this setShippingAmount($value)
 * @method mixed getShippingAmount()
 * @method $this setShippingTaxAmount($value)
 * @method mixed getShippingTaxAmount()
 * @method $this setShippingTaxCode($value)
 * @method mixed getShippingTaxCode()
 * @method $this setShippingDateTime($value)
 * @method mixed getShippingDateTime()
 * @method $this setRefCustomerref($value)
 * @method mixed getRefCustomerref()
 * @method $this setCompanyName($value)
 * @method mixed getCompanyName()
 * @method $this setCompanyVat($value)
 * @method mixed getCompanyVat()
 * @method $this setCheckoutType($value)
 * @method mixed getCheckoutType()
 * @package IngenicoClient
 */
class Order extends Data
{
    /**
     * Default Locale
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * Allowed currencies
     * @var array
     */
    public $allowedCurrencies = [
        'AED',
        'ANG',
        'ARS',
        'AUD',
        'AWG',
        'BGN',
        'BRL',
        'BYR',
        'CAD',
        'CHF',
        'CNY',
        'CZK',
        'DKK',
        'EEK',
        'EGP',
        'EUR',
        'GBP',
        'GEL',
        'HKD',
        'HRK',
        'HUF',
        'ILS',
        'ISK',
        'JPY',
        'KRW',
        'LTL',
        'LVL',
        'MAD',
        'MXN',
        'MYR',
        'NOK',
        'NZD',
        'PLN',
        'RON',
        'RUB',
        'SEK',
        'SGD',
        'SKK',
        'THB',
        'TRY',
        'UAH',
        'USD',
        'XAF',
        'XOF',
        'XPF',
        'ZAR'
    ];

    /**
     * Allowed Languages
     * @var array
     */
    public $allowedLanguages = [
        'en_US' => 'English',
        'cs_CZ' => 'Czech',
        'de_DE' => 'German',
        'dk_DK' => 'Danish',
        'el_GR' => 'Greek',
        'es_ES' => 'Spanish',
        'fr_FR' => 'French',
        'it_IT' => 'Italian',
        'ja_JP' => 'Japanese',
        'nl_BE' => 'Flemish',
        'nl_NL' => 'Dutch',
        'no_NO' => 'Norwegian',
        'pl_PL' => 'Polish',
        'pt_PT' => 'Portuguese',
        'ru_RU' => 'Russian',
        'se_SE' => 'Swedish',
        'sk_SK' => 'Slovak',
        'tr_TR' => 'Turkish',
    ];

    /**
     * Order constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * Get Order ID
     *
     * @param $orderId
     * @return Order
     */
    public function setOrderId($orderId)
    {
        if (strlen($orderId) > 40) {
            throw new InvalidArgumentException("Orderid cannot be longer than 40 characters");
        }
        if (preg_match('/[^a-zA-Z0-9_-]/', $orderId)) {
            throw new InvalidArgumentException("Order id cannot contain special characters");
        }

        return $this->setData('order_id', $orderId);
    }

    /**
     * Set Amount
     *
     * @param $amount
     * @return Order
     */
    public function setAmount($amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("Amount must be a positive number or 0");
        }

        if (($amount * 100) >= 1.0E+15) {
            throw new InvalidArgumentException("Amount is too high");
        }

        return $this->setData('amount', $amount);
    }

    /**
     * Get Amount In Cents.
     *
     * @return int
     */
    public function getAmountInCents()
    {
        if (!$amount = $this->getAmount()) {
            return 0;
        }

        return (int) bcmul(100, $amount);
    }

    /**
     * Set Locale.
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        if (!array_key_exists($locale, $this->allowedLanguages)) {
            throw new InvalidArgumentException('Invalid language ISO code');
        }

        return $this;
    }

    /**
     * Get Locale.
     *
     * @return string
     * @SuppressWarnings("Duplicates")
     */
    public function getLocale()
    {
        if (!$this->hasData('locale')) {
            $this->setData('locale', self::DEFAULT_LOCALE);
        }

        // Force default locale if locale isn't supported
        if (!array_key_exists($this->getData('locale'), $this->allowedLanguages)) {
            $this->setData('locale', self::DEFAULT_LOCALE);
        }

        return $this->getData('locale');
    }

    /**
     * Get Available amount for Refund.
     *
     * @return float
     */
    public function getAvailableAmountForRefund()
    {
        return (float) bcsub($this->getAmount(), $this->getTotalRefunded(), 2);
    }

    /**
     * Get Available amount for Capture.
     *
     * @return float
     */
    public function getAvailableAmountForCapture()
    {
        return (float) bcsub($this->getAmount(), $this->getTotalCaptured(), 2);
    }

    /**
     * Get Available amount for Cancel.
     *
     * @return float
     */
    public function getAvailableAmountForCancel()
    {
        return (float) bcsub($this->getAmount(), $this->getTotalCancelled(), 2);
    }

    /**
     * Set Currency.
     *
     * @param $currency
     * @return Order
     */
    public function setCurrency($currency)
    {
        if (!in_array(strtoupper($currency), $this->allowedCurrencies)) {
            throw new InvalidArgumentException("Unknown currency");
        }

        return $this->setData('currency', $currency);
    }

    /**
     * Alias for getCustomerId()
     * @deprecated
     * @return $this
     */
    public function getUserId()
    {
        return $this->getCustomerId();
    }

    /**
     * Alias for setCustomerId()
     * @deprecated
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        return $this->setCustomerId($userId);
    }

    /**
     * Get Billing Full name.
     *
     * @return string
     */
    public function getBillingFullName()
    {
        return join(' ', [$this->getBillingFirstName(), $this->getBillingLastName()]);
    }

    /**
     * Get Billing Full address.
     *
     * @return array
     */
    public function getBillingAddress()
    {
        return array_filter([$this->getBillingAddress1(), $this->getBillingAddress2(), $this->getBillingAddress3()], 'strlen');
    }

    /**
     * Get Shipping Full name.
     *
     * @return string
     */
    public function getShippingFullName()
    {
        return join(' ', [$this->getShippingFirstName(), $this->getShippingLastName()]);
    }

    /**
     * Get Shipping Full address.
     *
     * @return array
     */
    public function getShippingAddress()
    {
        return array_filter([$this->getShippingAddress1(), $this->getShippingAddress2(), $this->getShippingAddress3()], 'strlen');
    }

    /**
     * Get Billing Country Code.
     *
     * @return mixed
     */
    public function getBillingCountryCode()
    {
        if (!$this->hasData('billing_country_code')) {
            // For backward compatibility
            $this->setData('billing_country_code', $this->getBillingCountry());
        }

        return $this->getData('billing_country_code');
    }

    /**
     * Get Shipping Country Code.
     *
     * @return mixed
     */
    public function getShippingCountryCode()
    {
        if (!$this->hasData('shipping_country_code')) {
            // For backward compatibility
            $this->setData('shipping_country_code', $this->getShippingCountry());
        }

        return $this->getData('shipping_country_code');
    }

    /**
     * Get Browser's HTTP_ACCEPT value.
     *
     * @return string
     * @SuppressWarnings("Duplicates")
     */
    public function getHttpAccept()
    {
        if (!$this->hasData('http_accept')) {
            $this->setData('http_accept', isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null);
        }

        $value = $this->getData('http_accept');

        // Workaround: Ingenico doesn't accept values like "application/json, text/javascript, */*; q=0.01"
        // Ingenico returns HTML with "Page not found" text
        if (mb_stripos($value, 'application/json', 0, 'UTF-8') !== false ||
            mb_stripos($value, 'text/javascript', 0, 'UTF-8') !== false
        ) {
            $value = '*/*';
        }

        return $value;
    }

    /**
     * Get Browser's HTTP_USER_AGENT value.
     *
     * @return string
     * @SuppressWarnings("Duplicates")
     */
    public function getHttpUserAgent()
    {
        if (!$this->hasData('http_user_agent')) {
            $this->setData('http_user_agent', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
        }

        return $this->getData('http_user_agent');
    }

    /**
     * Set Order Items
     *
     * @param array $items
     * @return $this
     */
    public function setItems(array $items = [])
    {
        return $this->setData('items', $items);
    }

    /**
     * Get Order Items
     *
     * @return array
     */
    public function getItems()
    {
        $result = [];
        $items = $this->getData('items');

        if ($items) {
            foreach ($items as $item) {
                $result[] = new OrderItem($item);
            }
        }

        return $result;
    }

    /**
     * Returns array with cancel, accept,
     * exception and back url.
     *
     * @param mixed $orderId
     * @param string|null $paymentMode
     * @return ReturnUrl
     */
    public function getReturnUrls($orderId, $paymentMode = null)
    {
        // @todo move from IngenicoCoreLibrary:requestReturnUrls()
    }
}
