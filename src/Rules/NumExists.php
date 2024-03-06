<?php

namespace Laragear\Rut\Rules;

use Illuminate\Validation\Rules\Exists;

class NumExists extends Exists
{
    /**
     * Convert the rule to a validation string.
     */
    public function __toString()
    {
        return 'num_'.parent::__toString();
    }
}
