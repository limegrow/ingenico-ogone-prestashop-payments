<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\PaymentMethodInterface;

interface OpenInvoiceInterface
{
    /**
     * Get Missing or Invalid Order's fields.
     *
     * @param mixed $orderId Order Id
     * @param PaymentMethod\PaymentMethod $paymentMethod PaymentMethod Instance
     * @param array $fields Order fields
     * @return array
     */
    public function getMissingOrderFields($orderId, PaymentMethodInterface $paymentMethod, array $fields = []);

    /**
     * Validate OpenInvoice Additional Fields on Checkout Session
     *
     * @param $orderId
     * @param PaymentMethodInterface $paymentMethod
     * @return array
     * @throws Exception
     */
    public function validateOpenInvoiceCheckoutAdditionalFields($orderId, PaymentMethodInterface $paymentMethod);

    /**
     * Initiate Open Invoice Payment
     *
     * @param mixed $orderId
     * @param Alias $alias
     * @param array $fields
     * @throws \Exception
     */
    public function initiateOpenInvoicePayment($orderId, $alias, array $fields = []);
}
