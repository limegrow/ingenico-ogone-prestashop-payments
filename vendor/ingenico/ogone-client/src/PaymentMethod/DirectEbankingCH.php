<?php

namespace IngenicoClient\PaymentMethod;

class DirectEbankingCH extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'direct_ebankingch';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Sofort Überweisung (CH)';

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
    protected $pm = 'DirectEbankingCH';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'DirectEbankingCH';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'CH' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
