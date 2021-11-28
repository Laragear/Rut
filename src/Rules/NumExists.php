<?php

namespace Laragear\Rut\Rules;

use Illuminate\Validation\Rules\Exists;

class NumExists extends Exists
{
    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        return rtrim(sprintf('num_exists:%s,%s,%s',
            $this->table,
            $this->column,
            $this->formatWheres()
        ), ',');
    }
}