<?php

namespace IngenicoClient\PaymentMethod;

class DirectEbankingAT extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'direct_ebankingat';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Sofort Überweisung (AT)';

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
    protected $pm = 'DirectEbankingAT';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'DirectEbankingAT';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'AT' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
