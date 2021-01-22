<?php

namespace Ogone;

abstract class AbstractAlias
{
    const OPERATION_BY_MERCHANT = 'BYMERCHANT';
    const OPERATION_BY_PSP = 'BYPSP';

    /** @var string */
    protected $aliasOperation;

    /** @var string */
    protected $aliasUsage;

    /** @var string */
    protected $alias;

    /**
     * Set Alias Name
     *
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get Alias Name
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set Alias Usage
     *
     * @param $aliasUsage
     *
     * @return $this
     */
    public function setAliasUsage($aliasUsage)
    {
        $this->aliasUsage = $aliasUsage;

        return $this;
    }

    /**
     * Get Alias Usage
     *
     * @return string
     */
    public function getAliasUsage()
    {
        return $this->aliasUsage;
    }

    /**
     * Set Alias Operation
     *
     * @param string $aliasOperation
     *
     * @return $this
     */
    public function setAliasOperation($aliasOperation)
    {
        $this->aliasOperation = $aliasOperation;

        return $this;
    }

    /**
     * Get Alias Operation
     *
     * @return string
     */
    public function getAliasOperation()
    {
        return $this->aliasOperation;
    }

    /**
     * Set Alias Operation: By Merchant
     *
     * @return $this
     */
    public function operationByMerchant()
    {
        return $this->setAliasOperation(self::OPERATION_BY_MERCHANT);
    }

    /**
     * Set Alias Operation: By Psp
     *
     * @return $this
     */
    public function operationByPsp()
    {
        return $this->setAliasOperation(self::OPERATION_BY_PSP);
    }

    /**
     * To String
     * ы
     * @return string
     */
    public function __toString()
    {
        return $this->alias;
    }
}
