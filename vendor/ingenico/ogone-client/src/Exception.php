<?php

namespace IngenicoClient;

use Throwable;

class Exception extends \Exception
{
    const ERROR_DECLINED = 1;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
