<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\PaymentMethod;

/**
 * Class OrderField
 * @method mixed getFieldName()
 * @method $this setFieldName($value)
 * @method mixed getFieldType()
 * @method $this setFieldType($value)
 * @method mixed getLabel()
 * @method $this setLabel($value)
 * @method bool getRequired()
 * @method $this setRequired(bool $value)
 * @method mixed getLength()
 * @method $this setLength($value)
 * @method mixed getValues()
 * @method $this setValues($value)
 * @method mixed getValue()
 * @method $this setValue($value)
 * @method mixed getValidationMessage()
 * @method $this setValidationMessage($value)
 * @method bool getIsValid()
 * @method $this setIsValid($value)
 *
 * @package IngenicoClient
 */
class OrderField extends Data
{
    /**
     * Order Fields
     */
    const CHECKOUT_TYPE = 'checkout_type';
    const ITEMS = 'items';
    const LOCALE = 'locale';
    const ORDER_ID = 'order_id';
    const PAY_ID = 'pay_id';
    const AMOUNT = 'amount';
    const TOTAL_CAPTURED = 'total_captured';
    const TOTAL_REFUNDED = 'total_refunded';
    const TOTAL_CANCELLED = 'total_cancelled';
    const CURRENCY = 'currency';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const HTTP_ACCEPT = 'http_accept';
    const HTTP_USER_AGENT = 'http_user_agent';

    const IS_SHIPPING_SAME = 'is_shipping_same';

    /**
     * Billing Address Fields
     */
    const BILLING_COUNTRY = 'billing_country';
    const BILLING_COUNTRY_CODE = 'billing_country_code';
    const BILLING_ADDRESS1 = 'billing_address1';
    const BILLING_ADDRESS2 = 'billing_address2';
    const BILLING_ADDRESS3 = 'billing_address3';
    const BILLING_CITY = 'billing_city';
    const BILLING_STATE = 'billing_state';
    const BILLING_POSTCODE = 'billing_postcode';
    const BILLING_PHONE = 'billing_phone';
    const BILLING_FAX = 'billing_fax';
    const BILLING_EMAIL = 'billing_email';
    const BILLING_FIRST_NAME = 'billing_first_name';
    const BILLING_LAST_NAME = 'billing_last_name';
    const BILLING_STREET_NUMBER = 'billing_street_number';
    const BILLING_CUSTOMER_TITLE = 'billing_customer_title';

    /**
     * Shipping Address Fields
     */
    const SHIPPING_COUNTRY = 'shipping_country';
    const SHIPPING_COUNTRY_CODE = 'shipping_country_code';
    const SHIPPING_ADDRESS1 = 'shipping_address1';
    const SHIPPING_ADDRESS2 = 'shipping_address2';
    const SHIPPING_ADDRESS3 = 'shipping_address3';
    const SHIPPING_CITY = 'shipping_city';
    const SHIPPING_STATE = 'shipping_state';
    const SHIPPING_POSTCODE = 'shipping_postcode';
    const SHIPPING_PHONE = 'shipping_phone';
    const SHIPPING_FAX = 'shipping_fax';
    const SHIPPING_EMAIL = 'shipping_email';
    const SHIPPING_FIRST_NAME = 'shipping_first_name';
    const SHIPPING_LAST_NAME = 'shipping_last_name';
    const SHIPPING_STREET_NUMBER = 'shipping_street_number';
    const SHIPPING_CUSTOMER_TITLE = 'shipping_customer_title';

    /**
     * Shipping Info Fields
     */
    const SHIPPING_METHOD = 'shipping_method';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    const SHIPPING_TAX_CODE = 'shipping_tax_code';
    const SHIPPING_DATE_TIME = 'shipping_date_time';
    const SHIPPING_COMPANY = 'shipping_company';

    /**
     * Customer Fields
     */
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_IP = 'customer_ip';
    const CUSTOMER_DOB = 'customer_dob';
    const CUSTOMER_GENDER = 'customer_gender';
    const CUSTOMER_REG_NUMBER = 'customer_registration_number';
    const CUSTOMER_CIVILITY = 'customer_civility';

    /**
     * B2B Fields
     */
    const COMPANY_NUMBER = 'company_number';
    const COMPANY_NAME = 'company_name';
    const COMPANY_VAT = 'company_vat';
    const COST_CENTER = 'cost_center';
    const REF_CUSTOMERREF = 'ref_customerref';

    /**
     * OrderField constructor.
     *
     * @param string $name
     * @param array $options
     */
    public function __construct($name, array $options = [])
    {
        // Set defaults
        $this->setData([
            'field_name' => null,
            'field_type' => null,
            'required' => false,
            'length' => null,
            'values' => null
        ]);

        // Add data
        $this->setFieldName($name);
        $this->setData($options);
    }

    /**
     * Validate value
     *
     * @param mixed $value Optional
     * @return bool
     * @throws Exception
     */
    public function validate($value = null)
    {
        if (!$value) {
            $value = $this->getValue();
        }

        if ($this->getRequired() && empty($value)) {
            throw new Exception('Value is required');
        }

        if ($this->getLength() && mb_strlen($value, 'UTF-8') > $this->getLength()) {
            throw new Exception(sprintf('Value must not exceed %s characters', $this->getLength()));
        }

        // Date can be string like Y-m-d or timestamp
        if ($this->getFieldType() === 'date' && ! empty($value) && (!@strtotime($value) && !is_numeric($value))) {
            throw new Exception('Date is invalid');
        }

        if ($this->getFieldType() === 'number' && !is_numeric($value)) {
            throw new Exception('Value is not numeric');
        }

        if ($this->getFieldType() === PaymentMethod::TYPE_RADIO) {
            // Radio field has [$key => $value] items
            if ($this->getValues() &&
                !in_array($value, array_keys($this->getValues()))
            ) {
                // Pass the validation if have empty value and NOT required
                if (!(empty($value) && !$this->getRequired())) {
                    throw new Exception('Invalid value');
                }
            }
        } else {
            if ($this->getValues() && !in_array($value, $this->getValues())) {
                // Pass the validation if have empty value and NOT required
                if (!(empty($value) && !$this->getRequired())) {
                    throw new Exception('Invalid value');
                }
            }
        }

        return true;
    }
}
