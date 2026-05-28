<?php

declare(strict_types=1);

namespace Laragear\Rut\Scopes;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Collection as BaseCollection;
use Laragear\Rut\Rut;
use ReflectionClass;
use ReflectionMethod as Method;
use SplFixedArray;

use function count;
use function get_class;
use function is_countable;
use function is_iterable;

/**
 * @internal
 */
class RutScope implements Scope
{
    /**
     * List of (fixed) methods for the current scope.
     *
     * @var \SplFixedArray<string>
     */
    protected static SplFixedArray $methods;

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // ...
    }

    /**
     * Extend the Eloquent Query Builder instance with macros.
     */
    public function extend(Builder $builder): void
    {
        foreach (static::$methods ??= SplFixedArray::fromArray($this->filterMethods()->toArray()) as $method) {
            $builder->macro($method, [static::class, $method]);
        }
    }

    /**
     * Filters the methods of this Scope by those static and public.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function filterMethods(): BaseCollection
    {
        return BaseCollection::make((new ReflectionClass($this))->getMethods(Method::IS_PUBLIC | Method::IS_STATIC))
            ->filter(static function (Method $method): bool {
                return $method->isPublic() && $method->isStatic();
            })
            ->map(static function (Method $method): string {
                return $method->getName();
            })
            ->values();
    }

    /**
     * Find a model by its RUT number key.
     *
     * @param  string|string[]  $columns
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRut(
        Builder $builder,
        iterable|int|string|Arrayable|Rut $rut,
        string|array $columns = ['*'],
    ): Model|Collection|null {
        if (is_iterable($rut) || $rut instanceof Arrayable) {
            return static::findManyRut($builder, $rut, $columns);
        }

        return static::whereRut($builder, $rut)->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param  string|string[]  $columns
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findManyRut(
        Builder $builder,
        iterable|Arrayable $ruts,
        array|string $columns = ['*'],
    ): Collection {
        return static::whereRutIn($builder, $ruts)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  string|string[]  $columns
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRutOrFail(
        Builder $builder,
        iterable|int|string|Arrayable|Rut $rut,
        array|string $columns = ['*'],
    ): Model|Collection {
        $result = static::findRut($builder, $rut, $columns);

        $rut = $rut instanceof Arrayable ? $rut->toArray() : $rut;

        if (is_countable($rut)) {
            if (count($result) === BaseCollection::wrap($rut)->unique()->count()) {
                return $result;
            }
        } elseif ($result !== null) {
            return $result;
        }

        throw (new ModelNotFoundException())->setModel(get_class($builder->getModel()), $rut);
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @param  string|string[]  $columns
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRutOrNew(
        Builder $builder,
        iterable|int|string|Arrayable|Rut $rut,
        array|string $columns = ['*'],
    ): Model {
        return static::findRut($builder, $rut, $columns) ?? $builder->newModelInstance();
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     */
    public static function whereRut(
        Builder $builder,
        int|string|iterable|Arrayable|Rut $rut,
        string $boolean = 'and',
        bool $not = false,
    ): Builder {
        if (is_iterable($rut) || $rut instanceof Arrayable) {
            return static::whereRutIn($builder, $rut, $boolean, $not);
        }

        return $builder->where(
            // @phpstan-ignore-next-line
            $builder->getModel()->getQualifiedRutNumColumn(),
            $not ? '!=' : '=',
            Rut::split($rut)[0],
            $boolean,
        );
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function orWhereRut(Builder $builder, iterable|int|string|Arrayable|Rut $rut): Builder
    {
        return static::whereRut($builder, $rut, 'or');
    }

    /**
     * Adds a `WHERE` clause to the query without the RUT number.
     */
    public static function whereRutNot(
        Builder $builder,
        int|string|iterable|Arrayable|Rut $rut,
        string $boolean = 'and',
    ): Builder {
        return static::whereRut($builder, $rut, $boolean, true);
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function orWhereRutNot(Builder $builder, iterable|int|string|Arrayable|Rut $rut): Builder
    {
        return static::whereRutNot($builder, $rut, 'or');
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     */
    public static function whereRutIn(
        Builder $builder,
        iterable|Arrayable $ruts,
        string $boolean = 'and',
        bool $not = false,
    ): Builder {
        $ruts = BaseCollection::make($ruts)->map(static function (int|string|Rut $rut): int {
            return Rut::split($rut)[0];
        });

        // @phpstan-ignore-next-line
        return $builder->whereIn(
            // @phpstan-ignore-next-line
            $builder->getModel()->getQualifiedRutNumColumn(),
            $ruts,
            $boolean,
            $not,
        );
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     */
    public static function orWhereRutIn(Builder $builder, iterable|Arrayable $ruts, bool $not = false): Builder
    {
        return static::whereRutIn($builder, $ruts, 'or', $not);
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     */
    public static function whereRutNotIn(Builder $builder, iterable|Arrayable $ruts, string $boolean = 'and'): Builder
    {
        return static::whereRutIn($builder, $ruts, $boolean, true);
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     */
    public static function orWhereRutNotIn(Builder $builder, iterable|Arrayable $ruts): Builder
    {
        return static::orWhereRutIn($builder, $ruts, true);
    }

    /**
     * Filters the query by RUTs that are below 46.000.000.
     */
    public static function whereRutIsPerson(Builder $builder, string $boolean = 'and'): Builder
    {
        // @phpstan-ignore-next-line
        return $builder->where($builder->getModel()->getQualifiedRutNumColumn(), '<', Rut::INVESTOR_BASE, $boolean);
    }

    /**
     * Filters the query by RUTs that are below 46.000.000.
     */
    public static function orWhereRutIsPerson(Builder $builder): Builder
    {
        return static::whereRutIsPerson($builder, 'or');
    }

    /**
     * Filters the query by RUTs that are between 46.000.000 inclusive and below 47.000.000.
     */
    public static function whereRutIsInvestor(Builder $builder, string $boolean = 'and'): Builder
    {
        return $builder->where(static function (Builder $builder): void {
            $builder->where([
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '>=', Rut::INVESTOR_BASE],
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '<', Rut::INVESTMENT_COMPANY_BASE],
            ]);
        }, null, null, $boolean);
    }

    /**
     * Filters the query by RUTs that are between 46.000.000 inclusive and below 47.000.000.
     */
    public static function orWhereRutIsInvestor(Builder $builder): Builder
    {
        return static::whereRutIsInvestor($builder, 'or');
    }

    /**
     * Filters the query by RUTs that are between 47.000.000 inclusive and below 48.000.000.
     */
    public static function whereRutIsInvestmentCompany(Builder $builder, string $boolean = 'and'): Builder
    {
        return $builder->where(static function (Builder $builder): void {
            $builder->where([
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '>=', Rut::INVESTMENT_COMPANY_BASE],
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '<', Rut::CONTINGENCY_BASE],
            ]);
        }, null, null, $boolean);
    }

    /**
     * Filters the query by RUTs that are between 47.000.000 inclusive and below 48.000.000.
     */
    public static function orWhereRutIsInvestmentCompany(Builder $builder): Builder
    {
        return static::whereRutIsInvestmentCompany($builder, 'or');
    }

    /**
     * Filters the query by RUTs that are between 48.000.000 inclusive and below 60.000.000.
     */
    public static function whereRutIsContingency(Builder $builder, string $boolean = 'and'): Builder
    {
        return $builder->where(static function (Builder $builder): void {
            $builder->where([
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '>=', Rut::CONTINGENCY_BASE],
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '<', Rut::COMPANY_BASE],
            ]);
        }, null, null, $boolean);
    }

    /**
     * Filters the query by RUTs that are between 48.000.000 inclusive and below 60.000.000.
     */
    public static function orWhereRutIsContingency(Builder $builder): Builder
    {
        return static::whereRutIsContingency($builder, 'or');
    }

    /**
     * Filters the query by RUTs that are between 60.000.000 inclusive and below 100.000.000.
     */
    public static function whereRutIsCompany(Builder $builder, string $boolean = 'and'): Builder
    {
        return $builder->where(static function (Builder $builder): void {
            $builder->where([
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '>=', Rut::COMPANY_BASE],
                // @phpstan-ignore-next-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '<', Rut::TEMPORAL_BASE],
            ]);
        }, null, null, $boolean);
    }

    /**
     * Filters the query by RUTs that are between 60.000.000 inclusive and below 100.000.000.
     */
    public static function orWhereRutIsCompany(Builder $builder): Builder
    {
        return static::whereRutIsCompany($builder, 'or');
    }

    /**
     * Filters the query by RUTs that are 100.000.000 or over.
     */
    public static function whereRutIsTemporal(Builder $builder, string $boolean = 'and'): Builder
    {
        return $builder->where(static function (Builder $builder): void {
            $builder->where([
                [$builder->getModel()->getQualifiedRutNumColumn(), '>=', Rut::COMPANY_BASE], // @phpstan-ignore-line
                [$builder->getModel()->getQualifiedRutNumColumn(), '<', Rut::MAX], // @phpstan-ignore-line
            ]);
        }, null, null, $boolean);
    }

    /**
     * Filters the query by RUTs that are 100.000.000 or over.
     */
    public static function orWhereRutIsTemporal(Builder $builder): Builder
    {
        return static::whereRutIsTemporal($builder, 'or');
    }

    /**
     * Filters the query by RUTs that contain the given number.
     */
    public static function whereRutLike(
        Builder $builder,
        string $search,
        string $boolean = 'and',
        bool $not = false,
    ): Builder {
        if ($not) {
            $boolean .= ' not';
        }

        // @phpstan-ignore-next-line
        return $builder->where($builder->getModel()->getQualifiedRutNumColumn(), 'like', "%$search%", $boolean);
    }

    /**
     * Filter the query by RUTs that contain the given number or the next condition.
     */
    public static function orWhereRutLike(Builder $builder, string $search, bool $not = false): Builder
    {
        return static::whereRutLike($builder, $search, 'or', $not);
    }

    /**
     * Filter the query by RUTs that does not contain the given numbers.
     */
    public static function whereRutNotLike(Builder $builder, string $search, string $boolean = 'and'): Builder
    {
        return static::whereRutLike($builder, $search, $boolean, true);
    }

    /**
     * Filter the query by RUTs that doesn't contain the given number or the next condition.
     */
    public static function orWhereRutNotLike(Builder $builder, string $search, bool $not = false): Builder
    {
        return static::whereRutLike($builder, $search, 'or', true);
    }
}
