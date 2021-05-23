<?php

namespace IngenicoClient\PaymentMethod;

class SofortUberweisung extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'sofort_uberweisung';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Sofort Überweisung';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'sofort_uberweisung.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'DirectEbanking';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'Sofort Uberweisung';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'DE' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
