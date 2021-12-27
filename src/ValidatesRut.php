<?php

declare(strict_types=1);

namespace Laragear\Rut;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class ValidatesRut
{
    /**
     * Returns if the RUTs are valid.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public static function validateRut(string $attribute, mixed $value): bool
    {
        foreach (Arr::wrap($value) as $rut) {
            if (!Rut::check($rut)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns if the RUTs are valid and properly formatted
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public static function validateRutStrict(string $attribute, mixed $value): bool
    {
        foreach (Arr::wrap($value) as $rut) {
            try {
                if (Rut::parse($rut)->validate()->format(Format::Strict) !== $rut) {
                    return false;
                }
            } catch (Exceptions\RutException) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns if the number of the RUT exist in the Database
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     *
     * @return bool
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
     * Returns if the number of the RUT exist in the Database
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     *
     * @return bool
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
     * Returns if the RUT exist in the Database
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     *
     * @return bool
     */
    public static function validateRutExists(
        string $attribute,
        mixed $value,
        array $parameters,
        Validator $validator
    ): bool {
        $validator->requireParameterCount(1, $parameters, 'rut_exists');

        try {
            $rut = Rut::parse($value)->validate();
        } catch (Exceptions\RutException) {
            return false;
        }

        [$parameters, $wheres] = static::parseParameters($parameters, 3, 5);

        // If the parameters doesn't include the columns for the number and verification
        // digit, we will assume it's the attribute name plus "_num" and "_vd" in the
        // target table. We will just put these into the parameters array and pass.
        [$connection, $table] = $validator->parseTable($parameters[0] ?? Str::plural($attribute));

        $num_column = $parameters[1] ?? $attribute.'_num';
        $vd_column = $parameters[2] ?? $attribute.'_vd';

        $query = DB::connection($connection)
            ->table($table)
            ->where($num_column, $rut->num)
            ->whereRaw("UPPER(\"$vd_column\") = ?", strtoupper($rut->vd));

        return static::addExtraWheres($query, $wheres)->exists();
    }

    /**
     * Returns if the RUT exist in the Database
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     *
     * @return bool
     */
    public static function validateRutUnique(string $attribute, $value, array $parameters, Validator $validator): bool
    {
        $validator->requireParameterCount(1, $parameters, 'rut_unique');

        try {
            $rut = Rut::parse($value)->validate();
        } catch (Exceptions\RutException) {
            return false;
        }

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

        return static::addExtraWheres($query, $wheres)->doesntExist();
    }

    /**
     * Parse the parameters
     *
     * @param  array  $parameters
     * @param  int  $sliceOffset
     * @param  int  $pad
     * @return array
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
     * Add additional where clauses
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $wheres
     *
     * @return \Illuminate\Database\Query\Builder
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