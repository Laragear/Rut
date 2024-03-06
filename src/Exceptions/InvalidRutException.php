<?php

namespace Laragear\Rut\Exceptions;

use LogicException;
use Throwable;

class InvalidRutException extends LogicException implements RutException
{
    /**
     * Construct the exception.
     */
    public function __construct(
        string $message = 'The given RUT is invalid.',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
