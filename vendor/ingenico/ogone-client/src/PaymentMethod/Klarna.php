<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\OrderField;

/**
 * Class Klarna
 * @deprecated Use new integration instead of
 */
class Klarna extends PaymentMethod
{
    const CODE = 'klarna';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Klarna';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'klarna.svg';

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
        'SE' => 'Open Invoice SE',
        'FI' => 'Open Invoice FI',
        'DK' => 'Open Invoice DK',
        'NO' => 'Open Invoice NO',
        'DE' => 'Open Invoice DE',
        'NL' => 'Open Invoice NL'
    ];

    /**
     * Different Brand values per different countries
     * @var array
     */
    protected $brand_per_country = [
        'SE' => 'Open Invoice SE',
        'FI' => 'Open Invoice FI',
        'DK' => 'Open Invoice DK',
        'NO' => 'Open Invoice NO',
        'DE' => 'Open Invoice DE',
        'NL' => 'Open Invoice NL'
    ];

    /**
     * Common fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected $common_fields = [
        OrderField::BILLING_COUNTRY => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
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
        OrderField::SHIPPING_COMPANY => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 50
        ],
        OrderField::SHIPPING_CUSTOMER_TITLE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        OrderField::SHIPPING_FAX => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 20
        ],
        OrderField::SHIPPING_METHOD => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
        OrderField::SHIPPING_AMOUNT => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_NUMBERIC,
            'length' => 10
        ],
        OrderField::SHIPPING_TAX_CODE => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_NUMBERIC,
            'length' => 10
        ],
    ];

    /**
     * Additional fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected $additional_fields = [
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
        OrderField::CUSTOMER_REG_NUMBER => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 50
        ],
        OrderField::CUSTOMER_CIVILITY => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_RADIO,
            'length' => 1,
            'values' => ['M' => 'Male', 'F' => 'Female']
        ],
    ];
}
