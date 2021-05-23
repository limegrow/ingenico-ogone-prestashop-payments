<?php

namespace IngenicoClient\PaymentMethod;

class CarteBancaire extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'cb';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Carte Bancaire';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'carte_bancaire.svg';

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
    protected $brand = 'CB';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'FR' => [
            'popularity' => 20
        ],
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected $is_security_mandatory = true;
}
