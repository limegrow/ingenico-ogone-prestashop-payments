<?php

namespace IngenicoClient\PaymentMethod;

class Aurore extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'aurore';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Aurore';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'aurore.png';

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
    protected $brand = 'Aurore';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'FR' => [
            'popularity' => 100
        ],
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected $is_security_mandatory = true;

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;

    /**
     * Is support Two phase flow
     * @var bool
     */
    protected $two_phase_flow = false;
}
