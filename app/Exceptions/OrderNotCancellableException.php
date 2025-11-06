<?php

namespace App\Exceptions;

use Exception;

class OrderNotCancellableException extends Exception
{
    protected $message = 'Order cannot be cancelled in current status';
    protected $code = 422;
}
