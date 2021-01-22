<?php

namespace IngenicoClient\PaymentMethod;

class Ing extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'ing';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'ING Home\'Pay';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'ing.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'ING HomePay';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'ING HomePay';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'BE' => [
            'popularity' => 40
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
