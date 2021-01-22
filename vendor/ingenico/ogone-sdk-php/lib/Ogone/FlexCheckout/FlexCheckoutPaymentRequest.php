<?php

namespace Ogone\FlexCheckout;

use Ogone\AbstractPaymentRequest;
use Ogone\ShaComposer\ShaComposer;

class FlexCheckoutPaymentRequest extends AbstractPaymentRequest
{
    const TEST = "https://ogone.test.v-psp.com/Tokenization/HostedPage";

    const PRODUCTION = "https://secure.ogone.com/Tokenization/HostedPage";

    protected $payment_methods = [
        "CreditCard",
        "DirectDebit",
    ];

    public function __construct(ShaComposer $shaComposer)
    {
        $this->shaComposer = $shaComposer;
        $this->ogoneUri    = self::TEST;
    }

    public function getCheckoutUrl()
    {
        return $this->getOgoneUri()."?". http_build_query($this->toArray());
    }

    public function getRequiredFields()
    {
        return array(
            'account.pspid',
            'alias.orderid',
            'card.paymentmethod',
            'parameters.accepturl',
            'parameters.exceptionurl',
        );
    }

    public function setPspId($pspid)
    {
        $this->parameters['account.pspid'] = $pspid;

        return $this;
    }

    public function setOrderId($orderid)
    {
        $this->parameters['alias.orderid'] = $orderid;

        return $this;
    }

    public function setAliasId(Alias $alias)
    {
        $this->parameters['alias.aliasid'] = $alias->getAlias();

        return $this;
    }

    /**
     * Force saving alias
     * @param bool $force
     * @return $this
     */
    public function setForceAliasSave($force = false) {
        if ($force) {
            if (!isset($this->parameters['alias.aliasid'])) {
                $this->parameters['alias.aliasid'] = '';
            }

            $this->setStorePermanently('Y');
        }

        return $this;
    }

    /**
     * It indicates whether you want to store a temporary (N) or indefinite (Y) Alias. The possible values are:
     * "N": the alias will be deleted after 2 hours.
     * "Y": the alias will be stored indefinitely, for future use.
     * @param $value
     */
    public function setStorePermanently($value)
    {
        $this->parameters['alias.storepermanently'] = $value;

        return $this;
    }

    public function setPm($payment_method)
    {
        //if (!in_array($payment_method, $this->payment_methods)) {
        //    throw new \InvalidArgumentException("Unknown Payment method [$payment_method].");
        //}
        $this->parameters['card.paymentmethod'] = $payment_method;

        return $this;
    }

    public function setAccepturl($accepturl)
    {
        $this->validateUri($accepturl);
        $this->parameters['parameters.accepturl'] = $accepturl;

        return $this;
    }

    public function setExceptionurl($exceptionurl)
    {
        $this->validateUri($exceptionurl);
        $this->parameters['parameters.exceptionurl'] = $exceptionurl;

        return $this;
    }

    public function setLanguage($language)
    {
        $this->parameters['layout.language'] = $language;

        return $this;
    }

    public function setShaSign()
    {
        $this->parameters['shasignature.shasign'] = parent::getShaSign();

        return $this;
    }

    public function setTemplate($template)
    {
        $this->parameters['layout.templatename'] = $template;

        return $this;
    }

    public function setPaymentBrand($brand)
    {
        $this->parameters['card.brand'] = $brand;

        return $this;
    }

    protected function getValidOperations()
    {
        return [];
    }
}
