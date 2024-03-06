<?php

declare(strict_types=1);

namespace Laragear\Rut\Rules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\DatabaseRule;

use function addslashes;
use function rtrim;
use function sprintf;

class RutUnique
{
    use DatabaseRule;

    /**
     * Create a new rule instance.
     */
    public function __construct(
        string $table,
        protected string $numColumn,
        protected string $vdColumn,
        protected mixed $ignore = null,
        protected string $idColumn = 'id'
    ) {
        $this->table = $table;
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @return $this
     */
    public function ignore(mixed $id, string $idColumn = null): RutUnique
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignore = $id;
        $this->idColumn = $idColumn ?? 'id';

        return $this;
    }

    /**
     * Ignore the given model during the unique check.
     *
     * @return $this
     */
    public function ignoreModel(Model $model, ?string $idColumn = null): RutUnique
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};

        return $this;
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        return rtrim(sprintf('rut_unique:%s,%s,%s,%s,%s,%s',
            $this->table,
            $this->numColumn,
            $this->vdColumn,
            $this->ignore ? '"'.addslashes((string) $this->ignore).'"' : 'NULL',
            $this->idColumn,
            $this->formatWheres()
        ), ',');
    }
}
