<?php

namespace Laragear\Rut\Exceptions;

use LogicException;
use Throwable;

class EmptyRutException extends LogicException implements RutException
{
    /**
     * Construct the exception.
     */
    public function __construct(
        string $message = 'The RUT needs at least 7 valid characters.',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
