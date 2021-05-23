<?php
/*
 * This file is part of the Marlon Ogone package.
 *
 * (c) Marlon BVBA <info@marlon.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ogone;

use InvalidArgumentException;
use Ogone\DirectLink\PaymentOperation;

abstract class AbstractPaymentRequest extends AbstractRequest
{
    const MODE_TEST = 'test';
    const MODE_PRODUCTION = 'production';

    /**
     * Set Mode
     *
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        if ($mode === self::MODE_TEST) {
            return $this->setOgoneUri(self::TEST);
        } elseif ($mode === self::MODE_PRODUCTION) {
            return $this->setOgoneUri(self::PRODUCTION);
        }

        throw new InvalidArgumentException('Invalid mode parameter');
    }

    /**
     * Set Test Mode
     *
     * @return $this
     */
    public function setTestMode()
    {
        return $this->setMode(self::MODE_TEST);
    }

    /**
     * Set Production Mode
     *
     * @return $this
     */
    public function setProductionMode()
    {
        return $this->setMode(self::MODE_PRODUCTION);
    }

    public function setOrderid($orderid)
    {
        if (mb_strlen($orderid, 'UTF-8') > 40) {
            throw new InvalidArgumentException("Orderid cannot be longer than 40 characters");
        }
        if (preg_match('/[^a-zA-Z0-9_-]/', $orderid)) {
            throw new InvalidArgumentException("Order id cannot contain special characters");
        }
        $this->parameters['orderid'] = $orderid;

        return $this;
    }

    /** Friend alias for setCom() */
    public function setOrderDescription($orderDescription)
    {
        return $this->setCom($orderDescription);
    }

    public function setCom($com)
    {
        if (mb_strlen($com, 'UTF-8') > 100) {
            throw new InvalidArgumentException("Order description cannot be longer than 100 characters");
        }
        $this->parameters['com'] = $com;

        return $this;
    }

    /**
     * Set amount in cents, eg EUR 12.34 is written as 1234
     *
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        if (!is_int($amount)) {
            throw new InvalidArgumentException("Integer expected. Amount is always in cents");
        }

        if ($amount >= 1.0E+15) {
            throw new InvalidArgumentException("Amount is too high");
        }

        $this->parameters['amount'] = $amount;

        return $this;
    }

    public function setCurrency($currency)
    {
        $this->parameters['currency'] = $currency;

        return $this;
    }

    public function setEmail($email)
    {
        if (mb_strlen($email, 'UTF-8') > 50) {
            throw new InvalidArgumentException("Email is too long");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email is invalid");
        }
        $this->parameters['email'] = $email;

        return $this;
    }

    public function setOwnerAddress($owneraddress)
    {
        if (mb_strlen($owneraddress, 'UTF-8') > 35) {
            throw new InvalidArgumentException("Owner address is too long");
        }
        $this->parameters['owneraddress'] = $owneraddress;

        return $this;
    }

    public function setOwnerZip($ownerzip)
    {
        if (mb_strlen($ownerzip, 'UTF-8') > 10) {
            throw new InvalidArgumentException("Owner Zip is too long");
        }
        $this->parameters['ownerzip'] = $ownerzip;

        return $this;
    }

    public function setOwnerTown($ownertown)
    {
        if (mb_strlen($ownertown, 'UTF-8') > 40) {
            throw new InvalidArgumentException("Owner town is too long");
        }
        $this->parameters['ownertown'] = $ownertown;

        return $this;
    }

    /**
     * Alias for setOwnercty
     *
     * @see http://www.iso.org/iso/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm
     */
    public function setOwnerCountry($ownercountry)
    {
        return $this->setOwnercty($ownercountry);
    }

    /**
     * @see http://www.iso.org/iso/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm
     */
    public function setOwnercty($ownercty)
    {
        if (mb_strlen($ownercty, 'UTF-8') > 2) {
            throw new InvalidArgumentException("Owner country code is too long");
        }

        if (!preg_match('/^[A-Z]{2}$/', strtoupper($ownercty))) {
            throw new InvalidArgumentException("Illegal country code");
        }
        $this->parameters['ownercty'] = strtoupper($ownercty);

        return $this;
    }

    /** Alias for setOwnertelno() */
    public function setOwnerPhone($ownerphone)
    {
        return $this->setOwnertelno($ownerphone);
    }

    public function setOwnertelno($ownertelno)
    {
        if (mb_strlen($ownertelno, 'UTF-8') > 30) {
            throw new InvalidArgumentException("Owner phone is too long");
        }
        $this->parameters['ownertelno'] = $ownertelno;

        return $this;
    }

    /** Alias for setComplus() */
    public function setFeedbackMessage($feedbackMessage)
    {
        return $this->setComplus($feedbackMessage);
    }

    public function setComplus($complus)
    {
        $this->parameters['complus'] = $complus;

        return $this;
    }

    public function setBrand($brand)
    {
        $this->parameters['brand'] = $brand;

        return $this;
    }

    public function setPaymentMethod($paymentMethod)
    {
        return $this->setPm($paymentMethod);
    }

    public function setPm($pm)
    {
        $this->parameters['pm'] = $pm;

        return $this;
    }

    public function setParamvar($paramvar)
    {
        if (mb_strlen($paramvar, 'UTF-8') < 2 || mb_strlen($paramvar, 'UTF-8') > 50) {
            throw new InvalidArgumentException("Paramvar must be between 2 and 50 characters in length");
        }
        $this->parameters['paramvar'] = $paramvar;

        return $this;
    }

    /** Alias for setTp */
    public function setDynamicTemplateUri($uri)
    {
        $this->validateUri($uri);
        $this->setTp($uri);
    }

    /** Alias for setTp */
    public function setStaticTemplate($tp)
    {
        return $this->setTp($tp);
    }

    public function setTp($tp)
    {
        $this->parameters['tp'] = $tp;

        return $this;
    }

    public function setOperation(?PaymentOperation $operation)
    {
        $this->parameters['operation'] = (string) $operation;

        return $this;
    }

    abstract protected function getValidOperations();
}
