<?php

namespace Ogone;

use InvalidArgumentException;

class Validator
{
    /**
     * Types
     */
    const TYPE_ALPHANUMERIC = 'AN';
    const TYPE_NUMERIC = 'N';
    const TYPE_EMAIL = 'EMAIL';

    /**
     * Allowed Languages
     * @var array
     */
    protected $allowedLanguages = [
        'en_US',
        'cs_CZ',
        'de_DE',
        'dk_DK',
        'el_GR',
        'es_ES',
        'fr_FR',
        'it_IT',
        'ja_JP',
        'nl_BE',
        'nl_NL',
        'no_NO',
        'pl_PL',
        'pt_PT',
        'ru_RU',
        'se_SE',
        'sk_SK',
        'tr_TR',
    ];

    /**
     * Allowed Currencies.
     *
     * @var array
     */
    protected $allowedCurrencies = [
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
     * Fields
     * @var array
     */
    protected static $fields = [
        // 2.1 Standard Ingenico ePayments fields
        'pspid' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 30,
        ],
        'orderid' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 40,
        ],
        'amount' => [
            'format' => self::TYPE_NUMERIC,
            // Any size
        ],
        'currency' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 3,
        ],
        'language' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 5,
        ],
        'operation' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 3,
            // @todo null was added as exception for Bancontact, Aurore
            'values' => ['RES', 'SAL', 'REN', 'DEL', 'DES', 'SAS', 'RFD', 'RFS', null]
        ],

        // 2.2 Invoicing and delivery data
        // https://epayments-support.ingenico.com/en/integration/all-sales-channels/integrate-with-directlink-server-to-server/guide
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/klarna
        'owneraddress' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 50,
            // @todo (Klarna accepts max. 35)
        ],
        'ownerzip' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
        ],
        'email' => [
            'format' => self::TYPE_EMAIL,
            'size' => 50,
        ],
        'ownertown' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 25,
        ],
        'ownertelno' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 20,
            // @todo (AfterPay accepts max. 10)
        ],
        'cuid' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 50,
        ],
        'ecom_consumer_gender' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 1,
            'vales' => ['m', 'f', 'M', 'F']
        ],
        'ecom_billto_postal_city' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 25,
        ],
        'ecom_billto_postal_countrycode' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 2,
        ],
        'ecom_billto_postal_county' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 25,
        ],
        'ecom_billto_postal_name_first' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_billto_postal_name_last' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_billto_postal_postalcode' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
        ],
        'ecom_billto_postal_street_line1' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_billto_postal_street_line2' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_billto_postal_street_number' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
        ],
        'ecom_shipto_company' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 50,
        ],
        'ecom_shipto_dob' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
            //@todo (format dd/MM/yyyy)
        ],
        'ecom_shipto_online_email' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 50,
        ],
        'ecom_shipto_postal_city' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 40,
            // @todo Afterpay: 25
        ],
        'ecom_shipto_postal_countrycode' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 2,
        ],
        'ecom_shipto_county' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 25,
        ],
        'ecom_shipto_postal_name_first' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_shipto_postal_name_last' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_shipto_postal_name_prefix' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
        ],
        'ecom_shipto_postal_postalcode' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
        ],
        'ecom_shipto_postal_state' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 2,
        ],
        'ecom_shipto_postal_street_line1' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_shipto_postal_street_line2' => [
            'format' => self::TYPE_ALPHANUMERIC,
            //'size' => 35,
        ],
        'ecom_shipto_postal_street_number' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
        ],
        'ecom_shipto_telecom_fax_number' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 20,
        ],
        'ecom_shipto_telecom_phone_number' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 20,
        ],
        'ownercty' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 2,
        ],
        'ordershipmeth' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 25,
        ],
        'ordershipcost' => [
            'format' => self::TYPE_NUMERIC,
        ],
        'ordershiptaxcode' => [
            // @todo Numberic? Alphanumberic?
        ],

        // 2.2 Invoicing and delivery data
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/afterpay
        'civility' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 10,
        ],
        'datein' => [
            // @todo Format: mm/dd/yyyy hh:mm:ss
        ],
        'ordershiptax' => [
            'format' => self::TYPE_NUMERIC,
        ],
        // Extra parameters for B2B
        'ref_customerref' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 20,
        ],
        'ecom_shipto_tva' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 20,
        ],
        'costcenter' => [
            'format' => self::TYPE_ALPHANUMERIC,
            'size' => 20,
        ],

        // 2.3 Order details
        // Optional integration data: Order data ("ITEM" parameters).
        // https://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/additional-data/order-data
        'itemattributes*' => [
            'size' => 50,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemcategory*' => [
            'size' => 50,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemcomments*' => [
            'size' => 255,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemdesc*' => [
            'size' => 16,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemdiscount*' => [
            'size' => 10,
            'format' => self::TYPE_NUMERIC
        ],
        'itemid*' => [
            'size' => 15,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemname*' => [
            'size' => 40,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemprice*' => [
            'size' => 50,
            'format' => self::TYPE_NUMERIC
        ],
        'itemquant*' => [
            'size' => 50,
            'format' => self::TYPE_NUMERIC
        ],
        'itemquantorig*' => [
            'size' => 50,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemunitofmeasure*' => [
            'size' => 50,
            'format' => self::TYPE_ALPHANUMERIC
        ],
        'itemvat*' => [
            'size' => 50,
            'format' => self::TYPE_NUMERIC
        ],
        'itemvatcode*' => [
            'size' => 50,
            'format' => self::TYPE_NUMERIC
        ],
        'itemweight*' => [
            'size' => 10,
            'format' => self::TYPE_NUMERIC
        ],
        'taxincluded*' => [
            'size' => 1,
            //'format' => self::TYPE_NUMERIC,
            'format' => self::TYPE_ALPHANUMERIC,
            'values' => [0, 1, '']
        ],
    ];



    /**
     * Field Name
     * @var string
     */
    private $fieldName;

    /**
     * Field Value
     * @var string
     */
    private $fieldValue;

    /**
     * Options of Validation
     * @var array
     */
    private $validationOptions = [];

    /**
     * Validate constructor.
     *
     * @param $key
     * @param $value
     * @param array $options
     */
    public function __construct($key, $value, array $options = [])
    {
        // Check if a string ends with a number
        // Check parameters is like ITEMID*
        $last = mb_substr($key, -1, 1, 'UTF-8');
        if (is_numeric($last) && in_array(rtrim($key, $last) . '*', array_keys(self::$fields))) {
            $key = rtrim($key, $last);
        }


        $this->fieldName = mb_strtolower($key, 'UTF-8');
        $this->fieldValue = $value;
        $this->validationOptions = $options;
    }

    /**
     * Validate
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate()
    {
        if (isset(self::$fields[$this->fieldName]) && is_array(self::$fields[$this->fieldName])) {
            $rules = self::$fields[$this->fieldName];

            // Validate format
            if (isset($rules['format'])) {
                // Validate numeric
                if ($rules['format'] === self::TYPE_NUMERIC &&
                    !is_numeric($this->fieldValue)
                ) {
                    throw new InvalidArgumentException(sprintf('%s is not valid format of value %s',
                        $this->fieldName,
                        $this->fieldValue
                    ));
                }

                // Validate alphanumeric
                // @todo Verify with ctype_print($this->fieldValue)
                if ($rules['format'] === self::TYPE_ALPHANUMERIC &&
                    !is_string((string) $this->fieldValue)
                ) {
                    throw new InvalidArgumentException(sprintf('%s is not valid format of value %s',
                        $this->fieldName,
                        $this->fieldValue
                    ));
                }

                // Validate E-mail
                if ($rules['format'] === self::TYPE_EMAIL &&
                    !filter_var($this->fieldValue, FILTER_VALIDATE_EMAIL)
                ) {
                    throw new InvalidArgumentException(sprintf('%s is not valid email of value %s',
                        $this->fieldName,
                        $this->fieldValue
                    ));
                }
            }

            // Validate size
            if (isset($rules['size']) && mb_strlen($this->fieldValue, 'UTF-8') > $rules['size']) {
                throw new InvalidArgumentException(sprintf('%s is too long', $this->fieldName));
            }

            // Validate possible values
            if (isset($rules['values']) && !in_array($this->fieldValue, $rules['values'])) {
                throw new InvalidArgumentException(sprintf('%s is not valid value %s', $this->fieldName, $this->fieldValue));
            }

            // Validate Language
            if ($this->fieldName === 'language' && !in_array($this->fieldValue, $this->allowedLanguages)) {
                throw new InvalidArgumentException('Invalid language ISO code');
            }

            // Validate Currency
            if ($this->fieldName === 'currency' && !in_array($this->fieldValue, $this->allowedCurrencies)) {
                throw new InvalidArgumentException('Unknown currency');
            }
        }

        return true;
    }
}

