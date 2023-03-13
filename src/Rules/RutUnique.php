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
     * Column of the RUT number.
     *
     * @var string
     */
    protected string $numColumn;

    /**
     * Column of the VD number.
     *
     * @var string
     */
    protected string $vdColumn;

    /**
     * The ID that should be ignored.
     *
     * @var mixed
     */
    protected mixed $ignore = null;

    /**
     * The name of the ID column.
     *
     * @var string
     */
    protected string $idColumn = 'id';

    /**
     * Create a new rule instance.
     *
     * @param  string  $table
     * @param  string  $numColumn
     * @param  string  $vdColumn
     */
    public function __construct(string $table, string $numColumn, string $vdColumn)
    {
        $this->table = $table;
        $this->numColumn = $numColumn;
        $this->vdColumn = $vdColumn;
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @param  mixed  $id
     * @param  string|null  $idColumn
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
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|null  $idColumn
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
     *
     * @return string
     */
    public function __toString()
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
