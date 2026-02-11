<?php

namespace App\Exceptions;

use Exception;

class ExpectedException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
