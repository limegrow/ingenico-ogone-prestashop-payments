<?php

namespace IngenicoClient\PaymentMethod;

class Bancontact extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'bancontact';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Bancontact';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'bancontact.svg';

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
    // @todo Bancontact returns BRAND="Bancontact/Mister Cash". Expects: "BCMC"
    protected $brand = 'BCMC';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'BE' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected $is_security_mandatory = true;

    /**
     * Is support Two phase flow
     * @var bool
     */
    protected $two_phase_flow = false;
}
