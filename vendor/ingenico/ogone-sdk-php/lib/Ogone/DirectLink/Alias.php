<?php
/*
 * This file is part of the Marlon Ogone package.
 *
 * (c) Marlon BVBA <info@marlon.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ogone\DirectLink;

use Ogone\AbstractAlias;
use InvalidArgumentException;

class Alias extends AbstractAlias
{
    /** @var string */
    private $cardName;

    /** @var string */
    private $cardNumber;

    /** @var string */
    private $expiryDate;

    /**
     * @param $alias
     * @param string|null $cardName
     * @param string|null $cardNumber
     * @param string|null $expiryDate
     */
    public function __construct($alias, $cardName = null, $cardNumber = null, $expiryDate = null)
    {
        if (empty($alias)) {
            throw new InvalidArgumentException("Alias cannot be empty");
        }

        if (strlen($alias) > 50) {
            throw new InvalidArgumentException("Alias is too long");
        }

        if (preg_match('/[^a-zA-Z0-9_-]/', $alias)) {
            throw new InvalidArgumentException("Alias cannot contain special characters");
        }

        $this->setAlias($alias)
            ->setCardName($cardName)
            ->setCardNumber($cardNumber)
            ->setExpiryDate($expiryDate);
    }

    /**
     * Set Card Name
     *
     * @param string $cardName
     *
     * @return $this
     */
    public function setCardName($cardName)
    {
        $this->cardName = $cardName;

        return $this;
    }

    /**
     * Get Card Name
     *
     * @return string|null
     */
    public function getCardName()
    {
        return $this->cardName;
    }

    /**
     * Set Card Number
     *
     * @param string $cardNumber
     *
     * @return $this
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    /**
     * Get Card Number
     *
     * @return string|null
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * Set Expiry Date
     * @param string $expiryDate
     *
     * @return $this
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get Expiry Date
     *
     * @return string|null
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }
}
