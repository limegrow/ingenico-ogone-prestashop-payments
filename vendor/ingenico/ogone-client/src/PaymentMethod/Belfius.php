<?php

namespace IngenicoClient\PaymentMethod;

class Belfius extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'belfius';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Belfius';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'belfius.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'DEXIA NetBanking';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'DEXIA NetBanking';

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

    /**
     * Transaction codes that indicate capturing.
     * @var array
     */
    protected $direct_sales_success_code = [41];

    /**
     * Transaction codes that indicate authorization.
     * @var array
     */
    protected $auth_mode_success_code = [];
}
