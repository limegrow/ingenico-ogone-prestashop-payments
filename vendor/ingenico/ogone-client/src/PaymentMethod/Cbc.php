<?php

namespace IngenicoClient\PaymentMethod;

class Cbc extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'cbc';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'CBC';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'cbc.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'CBC Online';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'CBC Online';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'BE' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
