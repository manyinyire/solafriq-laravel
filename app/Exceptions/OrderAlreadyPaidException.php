<?php

namespace App\Exceptions;

use Exception;

class OrderAlreadyPaidException extends Exception
{
    protected $message = 'Order is already paid';
    protected $code = 422;
}
