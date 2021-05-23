<?php

namespace IngenicoClient\PaymentMethod\Abstracts;

use IngenicoClient\OrderField;
use IngenicoClient\PaymentMethod\PaymentMethod;

abstract class Oney extends PaymentMethod
{
    /**
     * Common fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected $common_fields = [
        OrderField::BILLING_CUSTOMER_TITLE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_RADIO,
            'length' => 10,
            'values' => ['Mr' => 'Mr', 'Mrs' => 'Mrs', 'Miss' => 'Miss']
        ],
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
        OrderField::SHIPPING_CUSTOMER_TITLE => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_RADIO,
            'length' => 10,
            'values' => ['Mr' => 'Mr', 'Mrs' => 'Mrs', 'Miss' => 'Miss']
        ],
        OrderField::SHIPPING_COUNTRY => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
        OrderField::SHIPPING_COUNTRY_CODE => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 2
        ],
        OrderField::SHIPPING_ADDRESS1 => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_ADDRESS2 => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_CITY => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 25
        ],
        OrderField::SHIPPING_POSTCODE => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        OrderField::SHIPPING_PHONE => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 20
        ],
        OrderField::SHIPPING_EMAIL => [
            'required' => true,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 50
        ],
        OrderField::SHIPPING_FIRST_NAME => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_LAST_NAME => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 35
        ],
        OrderField::SHIPPING_STREET_NUMBER => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 10
        ],
        OrderField::SHIPPING_COMPANY => [
            'required' => false,
            'field_type' => PaymentMethod::TYPE_TEXT,
            'length' => 50
        ],
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
    protected $additional_fields = [];
}
