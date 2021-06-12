<?php

namespace IngenicoClient\PaymentMethod;

class InterSolve extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'intersolve';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'InterSolve';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'intersolve.png';

    /**
     * Category
     * @var string
     */
    protected $category = 'prepaid_vouchers';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'Intersolve';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'Intersolve';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'NL' => [
            'popularity' => 20
        ],
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;
}
