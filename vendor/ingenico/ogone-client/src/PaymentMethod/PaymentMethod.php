<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\Exception;

/**
 * Class PaymentMethod
 * @method mixed getIFrameUrl()
 * @method $this setIFrameUrl($url)
 * @method bool getOrderLineItemsRequired();
 * @method $this setOrderLineItemsRequired(bool $value);
 * @method bool getAdditionalDataRequired();
 * @method $this setAdditionalDataRequired(bool $value);
 * @method array getCommonFields();
 * @method $this setCommonFields(array $value);
 *
 * @package IngenicoClient\PaymentMethod
 */
class PaymentMethod implements \ArrayAccess, PaymentMethodInterface
{
    /**
     * Checkout Types
     */
    const CHECKOUT_B2C = 'b2c';
    const CHECKOUT_B2B = 'b2b';

    /**
     * Customer Field Types
     */
    const TYPE_TEXT = 'text';
    const TYPE_RADIO = 'radio';
    const TYPE_NUMBERIC = 'number';
    const TYPE_DATE = 'date';

    /**
     * ID Code
     * @var string
     */
    protected $id;

    /**
     * Name
     * @var string
     */
    protected $name;

    /**
     * Logo
     * @var string
     */
    protected $logo;

    /**
     * Category
     * @var string
     */
    protected $category;

    /**
     * Category Name
     * @var string
     */
    protected $category_name;

    /**
     * Payment Method
     * @var string
     */
    protected $pm;

    /**
     * Brand
     * @var string
     */
    protected $brand;

    /**
     * Countries
     * @var array
     */
    protected $countries;

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected $is_security_mandatory = false;

    /**
     * Credit Debit Flag (C or D)
     * @var string
     */
    protected $credit_debit;

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = false;

    /**
     * Defines if this payment method requires order line items to be sent with the request
     * @var bool
     */
    protected $order_line_items_required = false;

    /**
     * Is support Two phase flow
     * @var bool
     */
    protected $two_phase_flow = true;

    /**
     * Defines if this payment method requires additional data to be sent with the request.
     * Like OpenInvoice/Klarna/Afterpay
     * @var bool
     */
    protected $additional_data_required = false;

    /**
     * Defines if this payment method should be hidden from the checkout or listing
     * @var bool
     */
    protected $is_hidden = false;

    /**
     * Transaction codes that indicate capturing.
     * @var array
     */
    protected $direct_sales_success_code = [9];

    /**
     * Transaction codes that indicate authorization.
     * @var array
     */
    protected $auth_mode_success_code = [5];

    /**
     * Different PM values per different countries
     * @var array
     */
    protected $pm_per_country = [];

    /**
     * Different Brand values per different countries
     * @var array
     */
    protected $brand_per_country = [];

    /**
     * Common fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected $common_fields = [];

    /**
     * Additional fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected $additional_fields = [
        'b2c' => [],
        'b2b' => []
    ];

    /**
     * Missing Fields
     * @var array
     */
    private $missing_fields = [];

    /**
     * PaymentMethod constructor.
     * @param array|null $data
     */
    public function __construct($data = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Get ID
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Category
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set Category Name
     * @param string $categoryName
     * @return $this
     */
    public function setCategoryName($categoryName)
    {
        $this->category_name = $categoryName;

        return $this;
    }

    /**
     * Get Category Name
     * @return string
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * Get PM
     * @return string
     */
    public function getPM()
    {
        return $this->pm;
    }

    /**
     * Set PM
     * @param string $pm
     * @return $this
     */
    public function setPM($pm)
    {
        $this->pm = $pm;

        return $this;
    }

    /**
     * Get Brand
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set Brand
     * @param string $brand
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Set PM for Country
     * @param string $country
     * @param string $pm
     * @return $this
     */
    public function setPMByCountry($country, $pm)
    {
        $this->pm_per_country[$country] = $pm;

        return $this;
    }

    /**
     * Get PM by Country Code
     * @param string $country
     * @return string
     */
    public function getPMByCountry($country)
    {
        if (array_key_exists($country, $this->pm_per_country)) {
            return $this->pm_per_country[$country];
        }

        return null;
    }

    /**
     * Set Brand for Country
     * @param string $country
     * @param string $brand
     * @return $this
     */
    public function setBrandByCountry($country, $brand)
    {
        $this->brand_per_country[$country] = $brand;

        return $this;
    }

    /**
     * Get Brand by Country Code
     * @param string $country
     * @return string
     */
    public function getBrandByCountry($country)
    {
        if (array_key_exists($country, $this->brand_per_country)) {
            return $this->brand_per_country[$country];
        }

        return null;
    }

    /**
     * Set Additional Fields
     * @param $checkout_type
     * @param array $fields
     * @return $this
     */
    public function setAdditionalFields($checkout_type, array $fields = [])
    {
        $this->additional_fields = $fields;

        return $this;
    }

    /**
     * Get Additional Fields
     * @param string $checkout_type
     * @return array
     */
    public function getAdditionalFields($checkout_type)
    {
        return $this->additional_fields;
    }

    /**
     * Get Expected Fields
     * @param $checkout_type
     * @return array
     */
    public function getExpectedFields($checkout_type)
    {
        return array_merge($this->getCommonFields(), $this->getAdditionalFields($checkout_type));
    }

    /**
     * Set Missing Fields
     * @param array $fields
     */
    public function setMissingFields(array $fields)
    {
        $this->missing_fields = $fields;
    }

    /**
     * Get Missing Fields
     * @return array
     */
    public function getMissingFields()
    {
        return $this->missing_fields;
    }

    /**
     * Get Countries
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Is Security Mandatory
     * @return bool
     */
    public function isSecurityMandatory()
    {
        return $this->is_security_mandatory;
    }

    /**
     * Get Credit Debit Flag
     * @return string
     */
    public function getCreditDebit()
    {
        return $this->credit_debit;
    }

    /**
     * Is support Redirect only
     * @return bool
     */
    public function isRedirectOnly()
    {
        return $this->is_redirect_only;
    }

    /**
     * Is Hidden
     * @return bool
     */
    public function isHidden()
    {
        return $this->is_hidden;
    }

    /**
     * Returns codes that indicate capturing.
     * @return array
     */
    public function getDirectSalesSuccessCode()
    {
        return $this->direct_sales_success_code;
    }

    /**
     * Returns codes that indicate authorization.
     * @return array
     */
    public function getAuthModeSuccessCode()
    {
        return $this->auth_mode_success_code;
    }

    /**
     * Is support Two Phase Flow
     * @return bool
     */
    public function isTwoPhaseFlow()
    {
        return $this->two_phase_flow;
    }

    /**
     * Get Logo
     * @return string
     */
    public function getEmbeddedLogo()
    {
        if (filter_var($this->logo, FILTER_VALIDATE_URL) !== false) {
            return $this->logo;
        }

        $file = realpath(__DIR__ . '/../../assets/images/payment_logos/' . $this->logo);
        if (file_exists($file)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mime = mime_content_type($file);

            if ('svg' === $extension) {
                $mime = 'image/svg+xml';
            }

            if (strpos($mime, 'image') !== false) {
                $contents = file_get_contents($file);
                return sprintf('data:%s;base64,%s', $mime, base64_encode($contents));
            }
        }

        return '';
    }

    /**
     * Get object data by key with calling getter method
     *
     * @param string $key
     * @param mixed $args
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null)
    {
        $method = 'get' . $this->camelize($key);
        return $this->$method($args);
    }

    /**
     * Get data
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($method, 3));
                return property_exists($this, $key) ? $this->$key : null;
            case 'set':
                $key = $this->underscore(substr($method, 3));
                $this->$key = isset($args[0]) ? $args[0] : null;
                return $this;
            case 'uns':
                $key = $this->underscore(substr($method, 3));
                unset($this->$key);
                return $this;
            case 'has':
                $key = $this->underscore(substr($method, 3));
                return property_exists($this, $key);
        }

        throw new Exception(sprintf('Invalid method %s::%s', get_class($this), $method));
    }

    /**
     * Implementation of \ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Implementation of \ArrayAccess::offsetExists()
     *
     * @param string $offset
     * @return bool
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * Implementation of \ArrayAccess::offsetUnset()
     *
     * @param string $offset
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            unset($this->$offset);
        }
    }

    /**
     * Implementation of \ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }

        return null;
    }

    /**
     * Converts field names for setters and getters
     *
     * @param string $name
     * @return string
     */
    protected function underscore($name)
    {
        return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
    }

    /**
     * Camelize string
     * Example: super_string to superString
     *
     * @param $name
     * @return string
     */
    protected function camelize($name)
    {
        return $this->ucWords($name, '');
    }

    /**
     * Tiny function to enhance functionality of ucwords
     *
     * Will capitalize first letters and convert separators if needed
     *
     * @param string $str
     * @param string $destSep
     * @param string $srcSep
     * @return string
     */
    protected function ucWords($str, $destSep = '_', $srcSep = '_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }
}
