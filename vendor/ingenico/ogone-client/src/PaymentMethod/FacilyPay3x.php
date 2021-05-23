<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\PaymentMethod\Abstracts\Oney;

class FacilyPay3x extends Oney implements PaymentMethodInterface
{
    const CODE = 'facilypay3x';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'FacilyPay 3x';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'oney.png';

    /**
     * Category
     * @var string
     */
    protected $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'FACILYPAY3X';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'FACILYPAY3X';

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
     * Is support Three phase flow.
     * 3-step payment (waiting+authorisation+debit)
     * @var bool
     */
    protected $three_phase_flow = true;

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;

    /**
     * Defines if this payment method requires additional data to be sent with the request.
     * @var bool
     */
    protected $additional_data_required = true;

    /**
     * Defines if this payment method requires order line items to be sent with the request
     * @var bool
     */
    protected $order_line_items_required = true;
}
