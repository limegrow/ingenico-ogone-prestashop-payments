<?php

namespace IngenicoClient\PaymentMethod;

class DirectEbankingDE extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'direct_ebankingde';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Sofort Überweisung (DE)';

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
    protected $pm = 'DirectEbankingDE';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'DirectEbankingDE';

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
