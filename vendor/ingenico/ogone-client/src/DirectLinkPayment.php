<?php

namespace IngenicoClient;

use Ogone\DirectLink\DirectLinkPaymentRequest;
use Ogone\DirectLink\DirectLinkPaymentResponse;
use Ogone\DirectLink\Eci;
use Ogone\DirectLink\PaymentOperation;

trait DirectLinkPayment
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
    public function executePayment($orderId, Alias $alias)
    {
        $order = $this->getOrder($orderId);

        $dlPaymentRequest = $this->getDirectLinkPaymentRequest($order, $alias);

        $client = new Client($this->getLogger());
        $response = $client->post(
            $dlPaymentRequest->toArray(),
            $dlPaymentRequest->getOgoneUri(),
            $dlPaymentRequest->getShaSign()
        );

        return new Payment((new DirectLinkPaymentResponse($response))->toArray());
    }

    /**
     * @param Order $order
     * @param Alias $alias
     * @param Data|array $additional
     * @return DirectLinkPaymentRequest
     */
    public function getDirectLinkPaymentRequest(Order $order, Alias $alias, $additional = [])
    {
        if (is_array($additional)) {
            $additional = (new Data())->setData($additional);
        }

        // Get the operation
        if ($this->configuration->getSettingsDirectsales()) {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_DIRECT_SALE);
        } else {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_AUTHORISATION);
        }

        // Build Payment Request
        $request = new DirectLinkPaymentRequest($this->getConfiguration()->getShaComposer('in'));
        $request->setOgoneUri($this->getConfiguration()->getApiOrderdirect());

        // Get the "Skip security check"
        $skipSecurityCheck = $this->configuration->getSettingsSkipsecuritycheck();

        // Get Payment Method by Alias
        $paymentMethod = $alias->getPaymentMethod();

        // Get the credit-debit flag
        $creditDebitFlag = $paymentMethod->getCreditDebit();
        if (!empty($creditDebitFlag)) {
            $request->setCreditDebit($creditDebitFlag);
        }

        // Force 3DSecure
        if ($paymentMethod->isSecurityMandatory()) {
            $skipSecurityCheck = false;
        }

        // Workaround for Bancontact, Aurore
        // BCMC can't be processed in 2 steps so the OPERATION parameter must contain
        // the value SAL for this payment method. Or don't submit the OPERATION parameter
        // for this payment method and our platform will processes the transaction with the correct operation.
        if (!$paymentMethod->isTwoPhaseFlow()) {
            $operation = null;
        }

        // Force 3DSecure
        if ($alias->getForceSecurity()) {
            $skipSecurityCheck = false;
        }

        /** @var ReturnUrl $urls */
        $urls = $this->requestReturnUrls($order->getOrderId());

        // Add the customer name
        if ($alias->getCn()) {
            $request->setCn(str_replace(['"', "'"], '', $alias->getCn()));
        } else {
            $request->setCn(str_replace(['"', "'"], '', $order->getBillingFullName()));
        }

        // @see https://epayments-support.ingenico.com/en/integration-solutions/integrations/directlink
        $request->setOrig($this->getConfiguration()->getShoppingCartExtensionId())
            ->setShoppingCartExtensionId($this->getConfiguration()->getShoppingCartExtensionId())
            ->setPspId($this->getConfiguration()->getPspid())
            ->setUserId($this->getConfiguration()->getUserId())
            ->setPassword($this->getConfiguration()->getPassword())
            ->setAccepturl($urls->getAcceptUrl())
            ->setDeclineurl($urls->getDeclineUrl())
            ->setExceptionurl($urls->getExceptionUrl())
            ->setCancelurl($urls->getCancelUrl())
            ->setBackurl($urls->getBackUrl())
            ->setAmount($order->getAmountInCents())
            ->setCurrency($order->getCurrency())
            ->setLanguage($order->getLocale())
            ->setAlias($alias->exchangeDirectLink())
            ->setOperation($operation)
            ->setEci(new Eci(Eci::ECOMMERCE_RECURRING))
            ->setData($additional->getData());

        // Add Order values
        $request = self::copyOrderDataToPaymentRequest($request, $order);

        // Set owner address
        $ownerAddress = trim(implode(' ', [
            trim($order->getBillingAddress1()),
            trim($order->getBillingAddress2()),
            trim($order->getBillingAddress3())
        ]));

        if (mb_strlen($ownerAddress, 'UTF-8') <= 35) {
            $request->setOwnerAddress($ownerAddress);
        }

        // Use 3DSecure
        if (!$skipSecurityCheck) {
            // MPI 2.0 (3DS V.2)
            $request->setFlag3D('Y')
                ->setHttpAccept($order->getHttpAccept())
                ->setHttpUserAgent($order->getHttpUserAgent())
                ->setWin3DS(self::WIN3DS_MAIN)
                ->setComplus($order->getOrderId());

            // Add Browser values
            $request = self::copyBrowserDataToPaymentRequest($request, $order);
        }

        $request->validate();

        return $request;
    }
}
