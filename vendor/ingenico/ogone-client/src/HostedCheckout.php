<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\Afterpay;
use IngenicoClient\PaymentMethod\Bancontact;
use IngenicoClient\PaymentMethod\Ideal;
use IngenicoClient\PaymentMethod\Ingenico;
use IngenicoClient\PaymentMethod\Klarna;
use IngenicoClient\PaymentMethod\KlarnaBankTransfer;
use IngenicoClient\PaymentMethod\KlarnaDirectDebit;
use IngenicoClient\PaymentMethod\KlarnaFinancing;
use IngenicoClient\PaymentMethod\KlarnaPayLater;
use IngenicoClient\PaymentMethod\KlarnaPayNow;
use IngenicoClient\PaymentMethod\FacilyPay3x;
use IngenicoClient\PaymentMethod\FacilyPay3xnf;
use IngenicoClient\PaymentMethod\FacilyPay4x;
use IngenicoClient\PaymentMethod\FacilyPay4xnf;
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
            $this->logger->debug(__METHOD__, $params);
        }

        return (new Data())->setUrl($paymentRequest->getOgoneUri())
            ->setFields($params);
    }

    /**
     * Get Hosted Checkout Payment Request
     * @see https://epayments-support.ingenico.com/en/integration-solutions/integrations/hosted-payment-page
     *
     * @param \IngenicoClient\Order $order
     * @param \IngenicoClient\Alias $alias
     * @return EcommercePaymentRequest
     * @throws \Exception
     */
    public function getHostedCheckoutPaymentRequest(Order $order, Alias $alias)
    {
        // Convert customer dob to timestamp if needs
        if ($order->hasCustomerDob() && is_string($order->getCustomerDob())) {
            $order->setCustomerDob((new \DateTime($order->getCustomerDob()))->getTimestamp());
        }

        // Get Payment Method
        $paymentMethod = $alias->getPaymentMethod();

        // Payment ID
        $paymentId = $paymentMethod ? $paymentMethod->getId() : null;

        // Operation Code
        if (in_array($paymentId, [
                Klarna::CODE,
                KlarnaBankTransfer::CODE,
                KlarnaDirectDebit::CODE,
                KlarnaFinancing::CODE,
                KlarnaPayLater::CODE,
                KlarnaPayNow::CODE,
            ])
        ) {
            // Klarna allows RES only
            $operation = new PaymentOperation(PaymentOperation::REQUEST_FOR_AUTHORISATION);
        } else {
            $operation = new PaymentOperation(
                $this->configuration->getSettingsDirectsales() ? PaymentOperation::REQUEST_FOR_DIRECT_SALE :
                    PaymentOperation::REQUEST_FOR_AUTHORISATION
            );
        }

        // Get Items
        $items = [];
        if ($paymentMethod && $paymentMethod->getOrderLineItemsRequired()) {
            $items = (array) $order->getItems();

            // Workaround for the rounding issue
            // Checking for the rounding issue
            $amount = $order->getAmount();

            // Calculate amount
            $calculated = 0;

            /** @var OrderItem $item */
            foreach ($items as $id => $item) {
                $price = $item->getQty() * sprintf("%.2f", $item->getUnitPrice());
                if ($item->getVatIncluded()) {
                    $calculated += $price;
                } else {
                    $calculated += $price + ($item->getQty() * sprintf("%.2f", $item->getUnitVat()));
                }
            }

            // Add Discount
            if (bccomp($calculated, $amount, 2) === 1) {
                if (($calculated - $amount) > 0.9) {
                    throw new Exception(
                        sprintf('Error: Total amount is different to the sum of the details %s/%s occurred.',
                            $calculated,
                            $amount
                        )
                    );
                }

                $items[] = new OrderItem(
                    [
                        OrderItem::ITEM_TYPE => OrderItem::TYPE_DISCOUNT,
                        OrderItem::ITEM_ID => 'rounding',
                        OrderItem::ITEM_NAME => 'Discount',
                        OrderItem::ITEM_DESCRIPTION => 'Discount',
                        OrderItem::ITEM_UNIT_PRICE => -1 * ($calculated - $amount),
                        OrderItem::ITEM_QTY => 1,
                        OrderItem::ITEM_UNIT_VAT => 0,
                        OrderItem::ITEM_VATCODE => 0,
                        OrderItem::ITEM_VAT_INCLUDED => 1
                    ]
                );

                if ($this->logger) {
                    $this->logger->warn(
                        sprintf('Rounding issue. Amount %s vs %s', $amount, $calculated),
                        [$order->getOrderId()]
                    );
                }
            }

            // Add Fee
            if (bccomp($calculated, $amount, 2) === -1) {
                if (($amount - $calculated) > 0.9) {
                    throw new Exception(
                        sprintf('Error: Total amount is different to the sum of the details %s/%s occurred.',
                            $calculated,
                            $amount
                        )
                    );
                }

                $items[] = new OrderItem(
                    [
                        OrderItem::ITEM_TYPE => OrderItem::TYPE_FEE,
                        OrderItem::ITEM_ID => 'rounding',
                        OrderItem::ITEM_NAME => 'Fee',
                        OrderItem::ITEM_DESCRIPTION => 'Fee',
                        OrderItem::ITEM_UNIT_PRICE => ($amount - $calculated),
                        OrderItem::ITEM_QTY => 1,
                        OrderItem::ITEM_UNIT_VAT => 0,
                        OrderItem::ITEM_VATCODE => 0,
                        OrderItem::ITEM_VAT_INCLUDED => 1
                    ]
                );

                if ($this->logger) {
                    $this->logger->warn(
                        sprintf('Rounding issue. Amount %s vs %s', $amount, $calculated),
                        [$order->getOrderId()]
                    );
                }
            }
        }

        // Redirect method require empty Alias name to generate new Alias
        if ($alias->getIsShouldStoredPermanently()) {
            $alias->setAlias('');
        }

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

        // Set List Type
        $listType = $this->getConfiguration()->getPaymentpageListType();
        if ($listType) {
            $request->setData('pmlisttype', $listType);
        }

        // Exclude methods which don't work in Generic mode
        if (in_array($paymentId, [null, Ingenico::CODE])) {
            $request->setData('exclpmlist', 'FACILYPAY3X;FACILYPAY3XNF;FACILYPAY4X;FACILYPAY4XNF;KLARNA_BANK_TRANSFER;KLARNA_DIRECT_DEBIT;KLARNA_FINANCING;KLARNA_PAYLATER;KLARNA_PAYNOW;Open Invoice DE;Open Invoice NL;Open Invoice NO');
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

        // Parameters for BCMC
        if ($paymentId === Bancontact::CODE) {
            $request->setDevice((new DeviceDetect)->getDeviceType());
        }

        // Parameters for Klarna
        // @see https://epayments-support.ingenico.com/en/payment-methods/alternative-payment-methods/klarna
        if (in_array($paymentId, [
                KlarnaBankTransfer::CODE,
                KlarnaDirectDebit::CODE,
                KlarnaFinancing::CODE,
                KlarnaPayLater::CODE,
                KlarnaPayNow::CODE,
            ])
        ) {
            $request->setEcomConsumerGender($order->getCustomerGender())
                ->setEcomShiptoPostalNamePrefix($order->getShippingCustomerTitle())
                ->setEcomShiptoDob(
                    $order->getCustomerDob() ? date('Y-m-d', $order->getCustomerDob()) : null
                )
                ->setEcomShiptoTelecomFaxNumber($order->getShippingFax())
                ->setEcomShiptoTelecomPhoneNumber($order->getShippingPhone())
                ->setEcomBilltoPostalStreetNumber($order->getBillingStreetNumber())
                ->setEcomShiptoPostalStreetNumber($order->getShippingStreetNumber())
                ->setEcomBilltoCompany($order->getCompanyName())
                ->setEcomShiptoCompany($order->getCompanyName());

            // Remove OWNERADDRESS if persist
            $request->unsOwnerAddress();

            // Shipping details (recommended)
            //if (!$order->getIsVirtual()) {
            //    $request->setOrdershipmeth($order->getShippingMethod())
            //            ->setOrdershipcost(bcmul($order->getShippingAmount() - $order->getShippingTaxAmount(), 100))
            //            ->setOrdershiptax(bcmul($order->getShippingTaxAmount(), 100))
            //            ->setOrdershiptaxcode((int) $order->getShippingTaxCode() . '%');

                // Don't pass shipping item. It uses Ordershipcost instead of.
                /** @var OrderItem $item */
            //    foreach ($items as $id => $item) {
            //        if ($item->getType() === OrderItem::TYPE_SHIPPING) {
            //            unset($items[$id]);
            //        }
            //    }
            //}
        }

        // Parameters for Klarna (deprecated)
        if ($paymentId === Klarna::CODE) {
            $request->setEcomShiptoOnlineEmail($order->getBillingEmail())
                ->setCuid($order->getCustomerRegistrationNumber())
                ->setCivility($order->getCustomerCivility())
                ->setEcomConsumerGender($order->getCustomerGender())
                ->setEcomShiptoPostalNamePrefix($order->getShippingCustomerTitle())
                ->setEcomShiptoDob(
                    $order->getCustomerDob() ? date('Y-m-d', $order->getCustomerDob()) : null
                )
                ->setEcomShiptoTelecomFaxNumber($order->getShippingFax())
                ->setEcomShiptoTelecomPhoneNumber($order->getShippingPhone())
                ->setEcomBilltoPostalStreetNumber($order->getBillingStreetNumber())
                ->setEcomShiptoPostalStreetNumber($order->getShippingStreetNumber())
                //->setEcomBilltoCompany($order->getCompanyName())
                ->setEcomShiptoCompany($order->getCompanyName()) // Not required

                // Klarna doesn't support ECOM_BILLTO_POSTAL_STREET_LINE3
                ->unsEcomBilltoPostalStreetLine3()
                ->unsEcomShiptoPostalStreetLine3();

            // Set owner address
            $ownerAddress = trim(implode(' ', [
                trim($order->getBillingAddress1()),
                trim($order->getBillingAddress2()),
                trim($order->getBillingAddress3())
            ]));

            if (mb_strlen($ownerAddress, 'UTF-8') <= 35) {
                $request->setOwnerAddress($ownerAddress);
            }
        }

        // Parameters for Afterpay
        // @see https://epayments-support.ingenico.com/en/payment-methods/alternative-payment-methods/afterpay
        if ($paymentId === Afterpay::CODE) {
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

            // OWNERADDRESS is mandatory for Afterpay. But we have address problem if it's defined
            $request->unsOwnerAddress();
        }

        // Parameters for Oney
        // @see https://epayments-support.ingenico.com/en/payment-methods/alternative-payment-methods/limonetik
        if (in_array($paymentId, [
                FacilyPay3x::CODE,
                FacilyPay3xnf::CODE,
                FacilyPay4x::CODE,
                FacilyPay4xnf::CODE
            ])
        ) {
            $request->setEcomBilltoPostalNamePrefix($order->getBillingCustomerTitle())
                ->setEcomBilltoPostalStreetNumber($order->getBillingStreetNumber())
                ->setEcomBilltoTelecomPhoneNumber($order->getBillingPhone())
                ->setEcomBilltoTelecomMobileNumber($order->getBillingPhone())
                ->setEcomShiptoPostalNamePrefix($order->getShippingCustomerTitle())
                ->setEcomShiptoPostalStreetNumber($order->getShippingStreetNumber())
                ->setEcomShiptoTelecomPhoneNumber($order->getShippingPhone())
                ->setEcomShiptoTelecomMobileNumber($order->getShippingPhone())
                ->setRefCustomerid($order->getCustomerId())
                ->setEcomShipmethod('Other')
                ->setEcomShipmethoddetails('Standard')
                ->setEcomEstimateddeliverydate(date('Y-m-d', strtotime('+3 days')))
                ->setEcomShipmethodspeed(3 * 24);

            // Remove OWNERADDRESS if persist
            $request->unsOwnerAddress();

            $checkoutType = $order->getCheckoutType() ? $order->getCheckoutType() : Checkout::TYPE_B2C;
            if ($checkoutType === Checkout::TYPE_B2B) {
                $request->setEcomBilltoCompany($order->getCompanyName())
                    ->setEcomShiptoCompany($order->getCompanyName());
            }

            // ITEMCATEGORYX is mandatory for Oney
            /** @var OrderItem $item */
            foreach ($items as $id => $item) {
                // Parameters for Oney
                $item->setCategory('Fashion');
                $items[$id] = $item;
            }
        }

        // Shipping cost parameters for Klarna/Afterpay
        if (in_array($paymentId, [
                Klarna::CODE,
                Afterpay::CODE
            ])
        ) {
            $request->setOrdershipmeth($order->getShippingMethod())
                ->setOrdershipcost(bcmul($order->getShippingAmount() - $order->getShippingTaxAmount(), 100))
                ->setOrdershiptax(bcmul($order->getShippingTaxAmount(), 100))
                ->setOrdershiptaxcode((int) $order->getShippingTaxCode() . '%');

            // Don't pass shipping item for Klarna/Afterpay. It uses Ordershipcost instead of.
            /** @var OrderItem $item */
            foreach ($items as $id => $item) {
                if ($item->getType() === OrderItem::TYPE_SHIPPING) {
                    unset($items[$id]);
                }
            }
        }

        // Generate the list of items for the PMs that are requiring it (i.e. Klarna)
        if ($paymentMethod && $paymentMethod->getOrderLineItemsRequired()) {
            /** @var OrderItem $item */
            foreach ($items as $id => $item) {
                $fields = $item->exchange();

                foreach ($fields as $key => $value) {
                    $request->setData($key . ($id + 1), $value);
                }
            }
        }

        // Override PM and BRAND for Blank payment method
        $additionalData = (array) $order->getAdditionalData();
        if (isset($additionalData['flex_pm']) && isset($additionalData['flex_brand'])) {
            $request->setData('PM', $additionalData['flex_pm']);
            $request->setData('BRAND', $additionalData['flex_brand']);
        }

        // Add ISSUERID for iDeal
        if ($paymentMethod->getId() === Ideal::CODE && isset($additionalData['issuer_id'])) {
            $request->setData('ISSUERID', $additionalData['issuer_id']);
        }

        if ($this->logger) {
            $this->logger->debug(__METHOD__, $request->toArray());
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
            $this->logger->debug(__METHOD__, $params);
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
            ->setEcomShiptoPostalNameFirst($order->getShippingFirstName())
            ->setEcomShiptoPostalNameLast($order->getShippingLastName())
            ->setEcomShiptoPostalCountrycode($order->getShippingCountryCode())
            ->setEcomShiptoPostalCity($order->getShippingCity())
            ->setEcomShiptoPostalPostalcode($order->getShippingPostcode())
            ->setEcomShiptoPostalStreetLine1($order->getShippingAddress1())
            ->setEcomShiptoPostalStreetLine2($order->getShippingAddress2());

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
