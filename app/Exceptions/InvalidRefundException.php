<?php

namespace App\Exceptions;

use Exception;

class InvalidRefundException extends Exception
{
    protected $code = 422;

    public function __construct(string $message = 'Cannot refund order')
    {
        parent::__construct($message);
    }
}
