<?php

namespace IngenicoClient\PaymentMethod;

class Airplus extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'airplus';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'AirPlus';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'airplus.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'card';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'CreditCard';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'AIRPLUS';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'AT' => [
            'popularity' => 100
        ],
        'BE' => [
            'popularity' => 80
        ],
        'FR' => [
            'popularity' => 100
        ],
        'DE' => [
            'popularity' => 100
        ],
        'IT' => [
            'popularity' => 100
        ],
        'LU' => [
            'popularity' => 100
        ],
        'NL' => [
            'popularity' => 40
        ],
        'PT' => [
            'popularity' => 100
        ],
        'ES' => [
            'popularity' => 100
        ],
        'CH' => [
            'popularity' => 100
        ],
        'GB' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected $is_security_mandatory = false;

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
