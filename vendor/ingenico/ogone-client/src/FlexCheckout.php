<?php

namespace IngenicoClient;

use Ogone\FlexCheckout\FlexCheckoutPaymentRequest;

trait FlexCheckout
{
    /**
     * Get Inline payment method URL
     *
     * @param $orderId
     * @param Alias $alias
     * @return string
     */
    public function getInlineIFrameUrl($orderId, Alias $alias)
    {
        $order = $this->getOrder($orderId);

        $request = $this->getFlexCheckoutPaymentRequest($order, $alias);
        $request->setShaSign();
        $request->validate();

        return $request->getCheckoutUrl();
    }

    /**
     * Get Flex Checkout Payment Request Instance
     *
     * @return FlexCheckoutPaymentRequest
     */
    public function getFlexCheckoutPaymentRequest(Order $order, Alias $alias)
    {
        $request = new FlexCheckoutPaymentRequest($this->getConfiguration()->getShaComposer('in'));
        $request->setOgoneUri($this->getConfiguration()->getApiFlexcheckout());

        /** @var ReturnUrl $urls */
        $urls = $this->requestReturnUrls($order->getOrderId());

        $request->setPspId($this->getConfiguration()->getPspid())
            ->setOrderId($order->getOrderId())
            ->setPaymentMethod($alias->getPm())
            ->setBrand($alias->getBrand())
            ->setAccepturl($urls->getAcceptUrl())
            ->setExceptionurl($urls->getExceptionUrl())
            ->setStorePermanently($alias->getIsShouldStoredPermanently() ? 'Y' : 'N')
            ->setAliasId(new \Ogone\FlexCheckout\Alias($alias->getAlias()))
            ->setTemplate($this->getConfiguration()->getPaymentpageTemplateName())
            ->setLanguage($order->getLocale())
            ->setForceAliasSave($alias->getIsShouldStoredPermanently());

        return $request;
    }
}
