<?php

namespace Laragear\Rut\Rules;

use Illuminate\Validation\Rules\Unique;

class NumUnique extends Unique
{
    /**
     * Convert the rule to a validation string.
     */
    public function __toString()
    {
        return 'num_'.parent::__toString();
    }
}
