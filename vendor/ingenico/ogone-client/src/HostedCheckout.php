<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\Afterpay;
use IngenicoClient\PaymentMethod\Klarna;
use IngenicoClient\PaymentMethod\KlarnaBankTransfer;
use IngenicoClient\PaymentMethod\KlarnaDirectDebit;
use IngenicoClient\PaymentMethod\KlarnaFinancing;
use IngenicoClient\PaymentMethod\KlarnaPayLater;
use IngenicoClient\PaymentMethod\KlarnaPayNow;
use Ogone\AbstractPaymentRequest;
use Ogone\DirectLink\PaymentOperation;
use Ogone\Ecommerce\EcommercePaymentRequest;
use Ogone\Ecommerce\EcommercePaymentResponse;

trait HostedCheckout
{
    /**
     * Get Hosted Checkout parameters to generate the payment form.
     *
     * @deprecated Use IngenicoCoreLibrary::getHostedCheckoutPaymentRequest() instead of
     * @param $orderId
     * @param Alias $alias
     * @return Data
     */
    public function initiateRedirectPayment($orderId, Alias $alias)
    {
        $order = $this->getOrder($orderId);

        $paymentRequest = $this->getHostedCheckoutPaymentRequest($order, $alias);

        $params = $paymentRequest->toArray();
        $params['SHASIGN'] = $paymentRequest->getShaSign();

        if ($this->logger) {
            $this->logger->debug(__CLASS__. '::' . __METHOD__, $params);
        }

        return (new Data())->setUrl($paymentRequest->getOgoneUri())
            ->setFields($params);
    }

    /**
     * Get Hosted Checkout Payment Request
     *
     * @param \IngenicoClient\Order $order
     * @param \IngenicoClient\Alias $alias
     * @return EcommercePaymentRequest
     * @throws \Exception
     */
    public function getHostedCheckoutPaymentRequest(Order $order, Alias $alias)
    {
        if ($this->configuration->getSettingsDirectsales()) {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_DIRECT_SALE);
        } else {
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_AUTHORISATION);
        }

        // Redirect method require empty Alias name to generate new Alias
        if ($alias->getIsShouldStoredPermanently()) {
            $alias->setAlias('');
        }

        // Get Payment Method
        $paymentMethod = $alias->getPaymentMethod();

        // Build Payment Request
        $request = new EcommercePaymentRequest($this->getConfiguration()->getShaComposer('in'));
        $request->setOgoneUri($this->getConfiguration()->getApiEcommerce());

        /** @var ReturnUrl $urls */
        $urls = $this->requestReturnUrls($order->getOrderId());

        $request->setOrig($this->getConfiguration()->getShoppingCartExtensionId())
            ->setShoppingCartExtensionId($this->getConfiguration()->getShoppingCartExtensionId())
            ->setPspId($this->getConfiguration()->getPspid())
            ->setAccepturl($urls->getAcceptUrl())
            ->setDeclineurl($urls->getDeclineUrl())
            ->setExceptionurl($urls->getExceptionUrl())
            ->setCancelurl($urls->getCancelUrl())
            ->setBackurl($urls->getBackUrl())
            ->setLanguage($order->getLocale())
            ->setOperation($operation)
            ->setPm($alias->getPm())
            ->setBrand($alias->getBrand())
            ->setLanguage($order->getLocale());

        // Set Alias
        if (!$alias->getIsPreventStoring()) {
            $request->setAlias($alias->exchange());
        }

        // Set up templates
        switch ($this->getConfiguration()->getPaymentpageTemplate()) {
            case Configuration::PAYMENT_PAGE_TEMPLATE_INGENICO:
                $request->setTp($this->getConfiguration()->getPaymentpageTemplateName());
                break;
            case Configuration::PAYMENT_PAGE_TEMPLATE_STORE:
                $request->setTp($this->getConfiguration()->getRedirectPaymentPageTemplateUrl());
                break;
            case Configuration::PAYMENT_PAGE_TEMPLATE_EXTERNAL:
                $request->setTp($this->getConfiguration()->getPaymentpageTemplateExternalurl());
                break;
            default:
                // no break
        }

        /** @var EcommercePaymentRequest $request */
        $request = self::copyOrderDataToPaymentRequest($request, $order);
        $request = self::copyBrowserDataToPaymentRequest($request, $order);

        // Add the customer name
        if ($alias->getCn()) {
            $request->setCn(str_replace(['"', "'"], '', $alias->getCn()));
        } else {
            $request->setCn(str_replace(['"', "'"], '', $order->getBillingFullName()));
        }

        // Parameters for Klarna
        if ($paymentMethod &&
            in_array($paymentMethod->getId(), [
                Klarna::CODE,
                KlarnaBankTransfer::CODE,
                KlarnaDirectDebit::CODE,
                KlarnaFinancing::CODE,
                KlarnaPayLater::CODE,
                KlarnaPayNow::CODE,
            ])
        ) {
            $request->setCuid($order->getCustomerRegistrationNumber())
                ->setCivility($order->getCustomerCivility())
                ->setEcomConsumerGender($order->getCustomerGender())
                ->setEcomShiptoPostalNamePrefix($order->getShippingCustomerTitle())
                ->setEcomShiptoDob(
                    $order->getCustomerDob() ? date('d/m/Y', $order->getCustomerDob()) : null
                )
                ->setEcomShiptoTelecomFaxNumber($order->getShippingFax())
                ->setEcomShiptoTelecomPhoneNumber($order->getShippingPhone())
                ->setEcomBilltoPostalStreetNumber($order->getBillingStreetNumber())
                ->setEcomShiptoPostalStreetNumber($order->getShippingStreetNumber())
                ->setEcomShiptoPostalState($order->getShippingState())
                //->setEcomBilltoCompany($order->getCompanyName())
                ->setEcomShiptoCompany($order->getCompanyName()) // Not required

                // Klarna doesn't support ECOM_BILLTO_POSTAL_STREET_LINE3
                ->unsEcomBilltoPostalStreetLine3()
                ->unsEcomShiptoPostalStreetLine3();
        }

        // Parameters for Klarna (deprecated)
        if ($paymentMethod && $paymentMethod->getId() === Klarna::CODE) {
            $request->setEcomShiptoOnlineEmail($order->getBillingEmail());
        }

        // Parameters for Afterpay
        if ($paymentMethod && $paymentMethod->getId() === Afterpay::CODE) {
            $request->setEcomShiptoPostalNamePrefix($order->getShippingCustomerTitle())
                ->setEcomShiptoOnlineEmail($order->getBillingEmail())
                ->setEcomBilltoPostalStreetNumber($order->getBillingStreetNumber())
                ->setEcomShiptoPostalStreetNumber($order->getShippingStreetNumber());

            $checkoutType = $order->getCheckoutType() ? $order->getCheckoutType() : Checkout::TYPE_B2C;
            if ($checkoutType === Checkout::TYPE_B2C) {
                // B2C
                $request->setEcomShiptoOnlineEmail($order->getBillingEmail())
                    ->setEcomConsumerGender($order->getCustomerGender())
                    ->setEcomShiptoDob(
                        $order->getCustomerDob() ? date('d/m/Y', $order->getCustomerDob()) : null
                    )
                    ->setDatein($order->getShippingDateTime());
            } else {
                // B2B
                $request->setRefCustomerref($order->getRefCustomerref())
                    ->setEcomShiptoCompany($order->getCompanyName())
                    ->setEcomShiptoTva($order->getCompanyVat())
                    ->setRefCustomerid($order->getCustomerId())
                    ->setCostcenter($order->getCustomerId());
            }

            // Afterpay doesn't support ECOM_BILLTO_POSTAL_STREET_LINE3
            $request->unsEcomBilltoPostalStreetLine3()
                ->unsEcomShiptoPostalStreetLine3();
        }

        // Shipping cost parameters for Klarna/Afterpay
        if ($paymentMethod &&
            in_array($paymentMethod->getId(), [
                Klarna::CODE,
                Afterpay::CODE
            ])
        ) {
            $request->setOrdershipmeth($order->getShippingMethod())
                ->setOrdershipcost(bcmul($order->getShippingAmount() - $order->getShippingTaxAmount(), 100))
                ->setOrdershiptax(bcmul($order->getShippingTaxAmount(), 100))
                ->setOrdershiptaxcode((int) $order->getShippingTaxCode() . '%');
        }

        if ($paymentMethod) {
            // Generating the string with the list of items for the PMs that are requiring it (i.e. Open Invoice)
            if ($paymentMethod->getOrderLineItemsRequired() && $items = (array) $order->getItems()) {
                /** @var OrderItem $item */
                foreach ($items as $id => $item) {
                    // Don't pass shipping item for Klarna/Afterpay. It uses Ordershipcost instead of.
                    if (in_array($paymentMethod->getId(), [
                        Afterpay::CODE,
                        Klarna::CODE,
                    ])) {
                        if ($item->getType() === OrderItem::TYPE_SHIPPING) {
                            continue;
                        }
                    }

                    $fields = $item->exchange();

                    foreach ($fields as $key => $value) {
                        $request->setData($key . ($id + 1), $value);
                    }
                }
            }
        }

        // Validate
        $request->validate();

        return $request;
    }

    /**
     * Get "Redirect" Payment Request with specified PaymentMethod and Brand.
     * @see \IngenicoClient\PaymentMethod\PaymentMethod
     *
     * @param mixed $orderId
     * @param mixed|null $aliasId
     * @param string $paymentMethod
     * @param string $brand
     * @param string|null $paymentId
     *
     * @return Data Data with url and fields keys
     * @throws Exception
     */
    public function getSpecifiedRedirectPaymentRequest(
        $orderId,
        $aliasId,
        $paymentMethod,
        $brand,
        $paymentId = null
    ) {
        $order = $this->getOrder($orderId);

        if (!$paymentMethod || !$brand) {
            throw new Exception('Missing required parameters');
        }

        if ($this->configuration->getSettingsOneclick()) {
            // Customer chose the saved alias
            $aliasUsage = $this->__('core.authorization_usage');
            if (!empty($aliasId) && $aliasId !== self::ALIAS_CREATE_NEW) {
                // Payment with Saved Alias
                $alias = $this->getAlias($aliasId);
                if (!$alias->getId()) {
                    throw new Exception($this->__('exceptions.alias_none'));
                }

                // Check Access
                if ($alias->getCustomerId() != $this->extension->requestCustomerId()) {
                    throw new Exception($this->__('exceptions.access_denied'));
                }

                $alias->setOperation(Alias::OPERATION_BY_PSP)
                      ->setUsage($aliasUsage);
            } else {
                // New alias will be saved
                $alias = new Alias();
                $alias->setIsShouldStoredPermanently(true)
                      ->setOperation(Alias::OPERATION_BY_PSP)
                      ->setUsage($aliasUsage);
            }
        } else {
            $alias = new Alias();
            $alias->setIsPreventStoring(true);
        }

        // Build Alias with PaymentMethod and Brand
        $alias->setPm($paymentMethod)
              ->setBrand($brand);

        // Build Alias with PaymentMethod and Brand
        if ($paymentId) {
            $alias->setPaymentId($paymentId);
        }

        $paymentRequest = $this->getHostedCheckoutPaymentRequest($order, $alias);

        $params = $paymentRequest->toArray();
        $params['SHASIGN'] = $paymentRequest->getShaSign();

        if ($this->logger) {
            $this->logger->debug(__CLASS__. '::' . __METHOD__, $params);
        }

        return (new Data())->setUrl($paymentRequest->getOgoneUri())
                           ->setFields($params);
    }

    /**
     * Copy Order data to Payment request
     *
     * @param AbstractPaymentRequest $request
     * @param Order $order
     * @return AbstractPaymentRequest
     */
    public static function copyOrderDataToPaymentRequest(AbstractPaymentRequest $request, Order $order)
    {
        // Set values for Request instance
        $request->setOrderId($order->getOrderId())
            ->setAmount($order->getAmountInCents())
            ->setCurrency($order->getCurrency())
            ->setOwnercty($order->getBillingCountryCode())
            ->setOwnerTown($order->getBillingCity())
            ->setOwnerZip($order->getBillingPostcode())
            ->setOwnertelno($order->getBillingPhone())
            ->setCivility($order->getCustomerCivility())
            ->setEmail($order->getBillingEmail())
            ->setRemoteAddr($order->getCustomerIp())
            ->setAddrmatch($order->getIsShippingSame() ? '1' : '0')
            ->setEcomBilltoPostalNameFirst($order->getBillingFirstName())
            ->setEcomBilltoPostalNameLast($order->getBillingLastName())
            ->setEcomBilltoPostalCountrycode($order->getBillingCountryCode())
            ->setEcomBilltoPostalCity($order->getBillingCity())
            ->setEcomBilltoPostalPostalcode($order->getBillingPostcode())
            ->setEcomBilltoPostalStreetLine1($order->getBillingAddress1())
            ->setEcomBilltoPostalStreetLine2($order->getBillingAddress2())
            //->setEcomBilltoPostalStreetLine3($order->getBillingAddress3())
            ->setEcomShiptoPostalNameFirst($order->getShippingFirstName())
            ->setEcomShiptoPostalNameLast($order->getShippingLastName())
            ->setEcomShiptoPostalCountrycode($order->getShippingCountryCode())
            ->setEcomShiptoPostalCity($order->getShippingCity())
            ->setEcomShiptoPostalPostalcode($order->getShippingPostcode())
            ->setEcomShiptoPostalStreetLine1($order->getShippingAddress1())
            ->setEcomShiptoPostalStreetLine2($order->getShippingAddress2());
        //->setEcomShiptoPostalStreetLine3($order->getShippingAddress3());

        // Set owner address
        $ownerAddress = implode(' ', [
            $order->getBillingAddress1(),
            $order->getBillingAddress2(),
            $order->getBillingAddress3()
        ]);

        if (mb_strlen($ownerAddress, 'UTF-8') <= 50) {
            $request->setOwnerAddress($ownerAddress);
        }

        return $request;
    }

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
    public static function copyBrowserDataToPaymentRequest(AbstractPaymentRequest $request, Order $order)
    {
        $request->setBrowseracceptheader($order->getHttpAccept());
        $request->setBrowseruseragent($order->getHttpUserAgent());

        // Add Browser values
        $browserValues = [
            'browserColorDepth',
            'browserJavaEnabled',
            'browserLanguage',
            'browserScreenHeight',
            'browserScreenWidth',
            'browserTimeZone'
        ];

        foreach ($browserValues as $key) {
            if (isset($_COOKIE[$key])) {
                $request->setData(strtolower($key), $_COOKIE[$key]);
            }
        }

        return $request;
    }

    /**
     * Validate Hosted payment return request.
     *
     * @param $response
     *
     * @return mixed
     */
    public function validateHostedCheckoutResponse($response)
    {
        $ecommercePaymentResponse = new EcommercePaymentResponse($response);

        return $ecommercePaymentResponse->isValid($this->configuration->getShaComposer('out'));
    }
}
