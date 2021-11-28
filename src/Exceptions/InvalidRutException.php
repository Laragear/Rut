<?php

namespace Laragear\Rut\Exceptions;

use Exception;
use Throwable;

class InvalidRutException extends Exception
{
    /**
     * Construct the exception.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  null|Throwable  $previous
     * @return void
     */
    public function __construct($message = 'The given RUT is invalid.', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}