<?php

namespace Ogone\Ecommerce;

use Ogone\AbstractAlias;
use InvalidArgumentException;

class Alias extends AbstractAlias
{

    const OPERATION_BY_MERCHANT = 'BYMERCHANT';
    const OPERATION_BY_PSP = 'BYPSP';

    public function __construct($alias, $aliasOperation = self::OPERATION_BY_MERCHANT, $aliasUsage = null)
    {
        if (strlen($alias) > 50) {
            throw new InvalidArgumentException("Alias is too long");
        }

        if (preg_match('/[^a-zA-Z0-9_-]/', $alias)) {
            throw new InvalidArgumentException("Alias cannot contain special characters");
        }

        $this->setAlias($alias)
            ->setAliasUsage($aliasUsage)
            ->setAliasOperation($aliasOperation);
    }

}
