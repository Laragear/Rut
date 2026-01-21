<?php

declare(strict_types=1);

namespace Laragear\Rut;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use function array_map;

/**
 * @internal
 */
class ValidatesRut
{
    /**
     * List of development (dummy) RUTs numbers.
     *
     * This list is "flipped" for faster checks. To un-flip it, use the "dummies()" static method.
     *
     * @const string[]
     */
    public const DUMMY_RUTS = [
        177777 => true,
        233333 => true,
        466666 => true,
        522222 => true,
        699999 => true,
        755555 => true,
        811111 => true,
        988888 => true,
        1777777 => true,
        2333333 => true,
        4666666 => true,
        5222222 => true,
        6999999 => true,
        7555555 => true,
        8111111 => true,
        9888888 => true,
        11111111 => true,
        22222222 => true,
        33333333 => true,
        44444444 => true,
        55555555 => true,
        66666666 => true,
        76000000 => true,
        77777777 => true,
        88888888 => true,
        99999999 => true,
    ];

    /**
     * Active list of dummy RUTs as [RUT number => true].
     *
     * @var array<string,true>
     */
    protected static array $dummies = self::DUMMY_RUTS;

    /**
     * Should the validation rules blacklist dummy ruts
     *
     * @var bool
     */
    public static bool $blacklistDummyRuts = false;

    /**
     * Return a list of Dummy RUTs for development.
     *
     * @return \Illuminate\Support\Collection<int, \Laragear\Rut\Rut>
     */
    public static function dummies(): Collection
    {
        return Collection::make(static::$dummies)->map(static function (true $value, int $num): Rut {
            return Rut::fromNum($num);
        })->values();
    }

    /**
     * Sets the list of dummy RUTs to blocklist.
     */
    public static function setDummies(Enumerable|array|null $dummies = null): void
    {
        static::$dummies = null === $dummies ? self::DUMMY_RUTS : static::parseDummies($dummies);
    }

    /**
     * Parse the dummies list into an optimized lookup list.
     *
     * @param  \Illuminate\Support\Enumerable<int, \Laragear\Rut\Rut|string|int>|array<\Laragear\Rut\Rut|string|int> $list
     * @return array<int, true>
     */
    protected static function parseDummies(Enumerable|array $list): array
    {
        return Collection::make($list)->mapWithKeys(static function (Rut|string|int $value): array {
            return [Rut::parse($value)->num => true];
        })->toArray();
    }

    /**
     * Check if the RUT is blacklisted.
     */
    protected static function isBlacklisted(Rut $rut): bool
    {
        return static::$blacklistDummyRuts && isset(static::$dummies[$rut->num]);
    }

    /**
     * Returns the RUT instance or `null` if it's not valid.
     */
    protected static function parse(mixed $rut): ?Rut
    {
        try {
            $rut = Rut::parse($rut);
        } catch (Exceptions\RutException) {
            return null;
        }

        if (static::isBlacklisted($rut) || $rut->isInvalid()) {
            return null;
        }

        return $rut;
    }

    /**
     * Returns if the RUTs are valid.
     */
    public static function validateRut(string $attribute, mixed $value): bool
    {
        foreach (Arr::wrap($value) as $rut) {
            if (!static::parse($rut)) {
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
            $instance = static::parse($rut);

            if (!$instance || RutFormat::Strict->format($instance) !== $rut) {
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

        if (! $rut = static::parse($value)) {
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

        if (! $rut = static::parse($value)) {
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

        if (! $rut = static::parse($value)) {
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

        if (! $rut = static::parse($value)) {
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
            ->whereRaw("UPPER($table.$vd_column) = ?", strtoupper($rut->vd))
            ->when($wheres[0] ?? null, static function (Builder $query) use ($wheres) {
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
