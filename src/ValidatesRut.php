<?php

declare(strict_types=1);

namespace Laragear\Rut;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * @internal
 */
class ValidatesRut
{
    /**
     * Returns if the RUTs are valid.
     */
    public static function validateRut(string $attribute, mixed $value): bool
    {
        foreach (Arr::wrap($value) as $rut) {
            try {
                if (Rut::parse($rut)->isInvalid()) {
                    return false;
                }
            } catch (Exceptions\RutException) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns if the RUTs are valid and properly formatted.
     */
    public static function validateRutStrict(string $attribute, mixed $value): bool
    {
        foreach (Arr::wrap($value) as $rut) {
            try {
                if (Rut::parse($rut)->validate()->format(RutFormat::Strict) !== $rut) {
                    return false;
                }
            } catch (Exceptions\RutException) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns if the number of the RUT exist in the Database.
     */
    public static function validateNumExists(
        string $attribute,
        mixed $value,
        array $parameters,
        Validator $validator
    ): bool {
        $validator->requireParameterCount(1, $parameters, 'num_exists');

        try {
            $rut = Rut::parse($value)->validate();
        } catch (Exceptions\RutException) {
            return false;
        }

        $parameters = static::parseParameters($parameters, 0, 2);

        $parameters[1] = $parameters[1] ?? $attribute.'_num';

        return $validator->validateExists($attribute, $rut->num, $parameters);
    }

    /**
     * Returns if the number of the RUT exist in the Database.
     */
    public static function validateNumUnique(
        string $attribute,
        mixed $value,
        array $parameters,
        Validator $validator
    ): bool {
        $validator->requireParameterCount(1, $parameters, 'num_unique');

        try {
            $rut = Rut::parse($value)->validate();
        } catch (Exceptions\RutException) {
            return false;
        }

        $parameters = static::parseParameters($parameters);

        $parameters[1] = $parameters[1] ?? $attribute.'_num';

        return $validator->validateUnique($attribute, $rut->num, $parameters);
    }

    /**
     * Returns if the RUT exist in the Database.
     */
    public static function validateRutExists(string $attribute, mixed $value, array $parameters, Validator $validator): bool
    {
        $validator->requireParameterCount(1, $parameters, 'rut_exists');

        $rut = Rut::parse($value);

        if ($rut->isInvalid()) {
            return false;
        }

        return static::query($attribute, $rut, $parameters, $validator)->exists();
    }

    /**
     * Returns if the RUT exist in the Database.
     */
    public static function validateRutUnique(string $attribute, mixed $value, array $parameters, Validator $validator): bool
    {
        $validator->requireParameterCount(1, $parameters, 'rut_unique');

        $rut = Rut::parse($value);

        if ($rut->isInvalid()) {
            return false;
        }

        return static::query($attribute, $rut, $parameters, $validator)->doesntExist();
    }

    /**
     * Creates a query to check records existence.
     */
    protected static function query(string $attribute, Rut $rut, array $parameters, Validator $validator): Builder
    {
        [$parameters, $wheres] = static::parseParameters($parameters, 3, 5);

        [$connection, $table] = $validator->parseTable($parameters[0] ?? Str::plural($attribute));

        $num_column = $parameters[1] ?? $attribute.'_num';
        $vd_column = $parameters[2] ?? $attribute.'_vd';

        $query = DB::connection($connection)
            ->table($table)
            ->where($num_column, $rut->num)
            ->whereRaw("UPPER(\"$vd_column\") = ?", strtoupper($rut->vd))
            ->when($wheres[0] ?? null, function (Builder $query) use ($wheres) {
                $query->where($wheres[1] ?? 'id', '!=', $wheres[0]);
            });

        if (count($wheres) > 2) {
            unset($wheres[0], $wheres[1]);
        }

        return static::addExtraWheres($query, $wheres);
    }

    /**
     * Parse the parameters.
     */
    protected static function parseParameters(array $parameters, int $sliceOffset = 0, int $pad = 0): array
    {
        foreach ($parameters as $key => $value) {
            if (strtolower($value) === 'null') {
                $parameters[$key] = null;
            }
        }

        if ($pad) {
            $parameters = array_pad($parameters, $pad, null);
        }

        return $sliceOffset
            ? [array_slice($parameters, 0, $sliceOffset), array_slice($parameters, $sliceOffset)]
            : $parameters;
    }

    /**
     * Add additional where clauses.
     */
    protected static function addExtraWheres(Builder $query, array $wheres): Builder
    {
        foreach (array_chunk($wheres, 2) as $item) {
            if ($item[1]) {
                $query->where($item[0], $item[1]);
            }
        }

        return $query;
    }
}
