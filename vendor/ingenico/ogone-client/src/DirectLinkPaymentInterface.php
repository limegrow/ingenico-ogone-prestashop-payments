<?php

namespace IngenicoClient;

use Ogone\DirectLink\DirectLinkPaymentRequest;

interface DirectLinkPaymentInterface
{
    /**
     * Create Direct Link payment request.
     *
     * Returns Payment info with transactions results.
     *
     * @param $orderId
     * @param Alias $alias
     *
     * @return Payment
     */
    public function executePayment($orderId, Alias $alias);

    /**
     * @param Order $order
     * @param Alias $alias
     * @param Data|array $additional
     * @return DirectLinkPaymentRequest
     */
    public function getDirectLinkPaymentRequest(Order $order, Alias $alias, $additional = []);
}
