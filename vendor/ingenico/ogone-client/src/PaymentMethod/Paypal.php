<?php

namespace IngenicoClient\PaymentMethod;

class Paypal extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'pay_pal';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'PayPal';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'paypal.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'e_wallet';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'PAYPAL';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'PAYPAL';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'AT' => [
            'popularity' => 40
        ],
        'BE' => [
            'popularity' => 40
        ],
        'FR' => [
            'popularity' => 40
        ],
        'DE' => [
            'popularity' => 80
        ],
        'IT' => [
            'popularity' => 60
        ],
        'LU' => [
            'popularity' => 40
        ],
        'NL' => [
            'popularity' => 40
        ],
        'PT' => [
            'popularity' => 60
        ],
        'ES' => [
            'popularity' => 40
        ],
        'CH' => [
            'popularity' => 40
        ],
        'GB' => [
            'popularity' => 40
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
    protected $auth_mode_success_code = [5];
}
