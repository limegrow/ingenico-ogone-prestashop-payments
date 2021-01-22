<?php

namespace IngenicoClient\PaymentMethod;

class Giropay extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'giropay';

    /**
     * ID Code
     * @var string
     */
    protected $id = 'giropay';

    /**
     * Name
     * @var string
     */
    protected $name = 'Giropay';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'giropay.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'giropay';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'giropay';

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
