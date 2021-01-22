<?php

namespace IngenicoClient\PaymentMethod;

class Paysafecard extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'paysafecard';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Paysafecard';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'paysafecard.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'prepaid_vouchers';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'paysafecard';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'paysafecard';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'AT' => [
            'popularity' => 20
        ],
        'BE' => [
            'popularity' => 20
        ],
        'FR' => [
            'popularity' => 20
        ],
        'DE' => [
            'popularity' => 20
        ],
        'IT' => [
            'popularity' => 20
        ],
        'LU' => [
            'popularity' => 20
        ],
        'PT' => [
            'popularity' => 20
        ],
        'ES' => [
            'popularity' => 20
        ],
        'CH' => [
            'popularity' => 20
        ],
        'GB' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;

    /**
     * Transaction codes that indicate capturing.
     * @var array
     */
    protected $direct_sales_success_code = [9];

    /**
     * Transaction codes that indicate authorization.
     * @var array
     */
    protected $auth_mode_success_code = [];
}
