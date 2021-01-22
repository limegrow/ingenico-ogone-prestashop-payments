<?php

namespace IngenicoClient;

use Ogone\AbstractPaymentRequest;
use Ogone\Ecommerce\EcommercePaymentRequest;

interface HostedCheckoutInterface
{
    /**
     * Get Hosted Checkout parameters to generate the payment form.
     *
     * @deprecated Use IngenicoCoreLibrary::getHostedCheckoutPaymentRequest() instead of
     * @param $orderId
     * @param Alias $alias
     * @return Data
     */
    public function initiateRedirectPayment($orderId, Alias $alias);

    /**
     * Get Hosted Checkout Payment Request
     *
     * @param \IngenicoClient\Order $order
     * @param \IngenicoClient\Alias $alias
     * @return EcommercePaymentRequest
     * @throws \Exception
     */
    public function getHostedCheckoutPaymentRequest(Order $order, Alias $alias);

    /**
     * Copy Order data to Payment request
     *
     * @param AbstractPaymentRequest $request
     * @param Order $order
     * @return AbstractPaymentRequest
     */
    public static function copyOrderDataToPaymentRequest(AbstractPaymentRequest $request, Order $order);

    /**
     * Copy Browser data (from Cookies) to Order
     *
     * There are follow cookies:
     * 'browserColorDepth',
     * 'browserJavaEnabled',
     * 'browserLanguage',
     * 'browserScreenHeight',
     * 'browserScreenWidth',
     * 'browserTimeZone'
     *
     * @param AbstractPaymentRequest $request
     * @param Order $order
     * @return AbstractPaymentRequest
     */
    public static function copyBrowserDataToPaymentRequest(AbstractPaymentRequest $request, Order $order);
}
