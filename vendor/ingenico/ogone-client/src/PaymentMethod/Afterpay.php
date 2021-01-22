<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\OrderField;

class Afterpay extends PaymentMethod
{
    const CODE = 'afterpay';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Afterpay';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'afterpay.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'open_invoice';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = '';

    /**
     * Brand
     * @var string
     */
    protected $brand = '';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'DE' => [
            'popularity' => 80
        ],
        'NL' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;

    /**
     * Defines if this payment method requires order line items to be sent with the request
     * @var bool
     */
    protected $order_line_items_required = true;

    /**
     * Defines if this payment method requires additional data to be sent with the request.
     * @var bool
     */
    protected $additional_data_required = true;

    /**
     * Different PM values per different countries
     * @var array
     */
    protected $pm_per_country = [
        'DE' => 'Open Invoice DE',
        'NL' => 'Open Invoice NL'
    ];

    /**
     * Different Brand values per different countries
     * @var array
     */
    protected $brand_per_country = [
        'DE' => 'Open Invoice DE',
        'NL' => 'Open Invoice NL'
    ];

    /**
     * Common fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected $common_fields = [
        OrderField::BILLING_COUNTRY_CODE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 2
        ],
        OrderField::BILLING_ADDRESS1 => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::BILLING_ADDRESS2 => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::BILLING_CITY => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
        OrderField::BILLING_STATE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 2
        ],
        OrderField::BILLING_POSTCODE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        OrderField::BILLING_PHONE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 20
        ],
        OrderField::BILLING_EMAIL => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 50
        ],
        OrderField::BILLING_FIRST_NAME => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::BILLING_LAST_NAME => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::BILLING_STREET_NUMBER => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        OrderField::SHIPPING_COUNTRY => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
        OrderField::SHIPPING_COUNTRY_CODE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 2
        ],
        OrderField::SHIPPING_ADDRESS1 => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_ADDRESS2 => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_CITY => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
        OrderField::SHIPPING_STATE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 2
        ],
        OrderField::SHIPPING_POSTCODE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        OrderField::SHIPPING_PHONE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 20
        ],
        OrderField::SHIPPING_EMAIL => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 50
        ],
        OrderField::SHIPPING_FIRST_NAME => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_LAST_NAME => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_STREET_NUMBER => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        OrderField::SHIPPING_CUSTOMER_TITLE => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        // Extra fields
        OrderField::SHIPPING_METHOD => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
        OrderField::SHIPPING_AMOUNT => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_NUMBERIC,
            'length' => 10
        ],
        OrderField::SHIPPING_TAX_AMOUNT => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_NUMBERIC,
            'length' => 10
        ],
        OrderField::SHIPPING_TAX_CODE => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_NUMBERIC,
            'length' => 10
        ],
        OrderField::SHIPPING_DATE_TIME => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_DATE,
        ],
    ];

    /**
     * Additional fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected $additional_fields = [
        PaymentMethod::CHECKOUT_B2C => [
            OrderField::CUSTOMER_DOB => [
                'required' => true,
                'field_type' => PaymentMethod::TYPE_DATE,
                'length' => 10,
            ],
            OrderField::CUSTOMER_GENDER => [
                'required' => true,
                'field_type' => PaymentMethod::TYPE_RADIO,
                'length' => 1,
                'values' => ['M' => 'Male', 'F' => 'Female']
            ],
        ],
        PaymentMethod::CHECKOUT_B2B => [
            OrderField::COMPANY_NUMBER => [
                'required' => true,
                'field_type' => PaymentMethod::TYPE_TEXT,
                'length' => 20,
            ],
            OrderField::COMPANY_NAME => [
                'required' => true,
                'field_type' => PaymentMethod::TYPE_TEXT,
                'length' => 50,
            ],
            OrderField::COMPANY_VAT => [
                'required' => true,
                'field_type' => PaymentMethod::TYPE_TEXT,
                'length' => 20,
            ],
            OrderField::CUSTOMER_ID => [
                'required' => false,
                'field_type' => PaymentMethod::TYPE_TEXT,
                'length' => 17,
            ],
            OrderField::COST_CENTER => [
                'required' => false,
                'field_type' => PaymentMethod::TYPE_TEXT,
                'length' => 20,
            ],
        ]
    ];

    /**
     * Set Additional Fields
     * @param $checkout_type
     * @param array $fields
     * @return $this
     */
    public function setAdditionalFields($checkout_type, array $fields = [])
    {
        $this->additional_fields[$checkout_type] = $fields;

        return $this;
    }

    /**
     * Get Additional Fields
     * @param string $checkout_type
     * @return array
     */
    public function getAdditionalFields($checkout_type)
    {
        return isset($this->additional_fields[$checkout_type]) ? $this->additional_fields[$checkout_type] : [];
    }
}
