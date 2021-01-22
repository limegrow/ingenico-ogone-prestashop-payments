<?php

namespace IngenicoClient\PaymentMethod;

class Kbc extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'kbc';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'KBC';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'kbc.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'KBC Online';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'KBC Online';

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
