<?php

declare(strict_types=1);

namespace Laragear\Rut\Rules;

use Illuminate\Validation\Rules\DatabaseRule;

use function rtrim;
use function sprintf;

class RutExists
{
    use DatabaseRule;

    /**
     * Create a new rule instance.
     *
     * @param  string  $table
     * @param  string  $numColumn
     * @param  string  $vdColumn
     */
    public function __construct(
        string $table,
        protected string $numColumn = 'NULL',
        protected string $vdColumn = 'NULL'
    ) {
        $this->table = $table;
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        return rtrim(sprintf('rut_exists:%s,%s,%s,%s',
            $this->table,
            $this->numColumn,
            $this->vdColumn,
            $this->formatWheres()
        ), ',');
    }
}
