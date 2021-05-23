<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\PaymentMethod\Abstracts\Klarna as KlarnaAbstract;

class KlarnaDirectDebit extends KlarnaAbstract
{
    const CODE = 'klarna_directdebit';

    /**
     * ID Code
     * @var string
     */
    protected $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected $name = 'Klarna Direct Debit';

    /**
     * Logo
     * @var string
     */
    protected $logo = 'https://x.klarnacdn.net/payment-method/assets/badges/generic/klarna.svg';

    /**
     * Category
     * @var string
     */
    protected $category = 'klarna';

    /**
     * Payment Method
     * @var string
     */
    protected $pm = 'KLARNA_DIRECT_DEBIT';

    /**
     * Brand
     * @var string
     */
    protected $brand = 'KLARNA_DIRECT_DEBIT';

    /**
     * Countries
     * @var array
     */
    protected $countries = [
        'AT' => [
            'popularity' => 100
        ],
        'DE' => [
            'popularity' => 100
        ],
        'NL' => [
            'popularity' => 100
        ],
        'SE' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected $is_redirect_only = true;

    /**
     * Defines if this payment method requires order line items to be sent with the request
     * @var bool
     */
    protected $order_line_items_required = true;

    /**
     * Defines if this payment method requires additional data to be sent with the request.
     * @var bool
     */
    protected $additional_data_required = true;

    /**
     * Defines if this payment method should be hidden from the checkout or listing
     * @var bool
     */
    protected $is_hidden = true;
}
