<?php

namespace App\Exceptions;

use Exception;

class ConstraintViolationException extends Exception
{
    protected $message = 'Unable to modify constrained record';
}
