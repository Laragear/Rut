<?php

declare(strict_types=1);

namespace Laragear\Rut\Scopes;

use function count;
use function get_class;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Collection as BaseCollection;
use function is_countable;
use function is_iterable;
use Laragear\Rut\Rut;
use ReflectionClass;
use ReflectionMethod as Method;
use SplFixedArray;

class RutScope implements Scope
{
    /**
     * List of (fixed) methods for the current scope.
     *
     * @var \SplFixedArray
     */
    protected static SplFixedArray $methods;

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // ...
    }

    /**
     * Extend the Eloquent Query Builder instance with macros.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        foreach (static::$methods ??= SplFixedArray::fromArray($this->filterMethods()->toArray()) as $method) {
            $query->macro($method, [static::class, $method]);
        }
    }

    /**
     * Filters the methods of this Scope by those static and public.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function filterMethods(): BaseCollection
    {
        return BaseCollection::make((new ReflectionClass($this))->getMethods(Method::IS_PUBLIC | Method::IS_STATIC))
            ->filter(static function (Method $method): bool {
                return $method->isPublic() && $method->isStatic();
            })
            ->map(static function (Method $method): string {
                return $method->getName();
            });
    }

    /**
     * Find a model by its RUT number key.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut  $rut
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRut(Builder $builder, iterable|int|string|Arrayable|Rut $rut, string|array $columns = ['*']): Model|Collection|null
    {
        if (is_iterable($rut) || $rut instanceof Arrayable) {
            return static::findManyRut($builder, $rut, $columns);
        }

        return static::whereRut($builder, $rut)->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|\Illuminate\Contracts\Support\Arrayable  $ruts
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findManyRut(Builder $builder, iterable|Arrayable $ruts, array|string $columns = ['*']): Collection
    {
        return static::whereRutIn($builder, $ruts)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut  $rut
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRutOrFail(Builder $builder, iterable|int|string|Arrayable|Rut $rut, array|string $columns = ['*']): Model|Collection
    {
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut  $rut
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRutOrNew(Builder $builder, iterable|int|string|Arrayable|Rut $rut, array|string $columns = ['*']): Model
    {
        return static::findRut($builder, $rut, $columns) ?? $builder->newModelInstance();
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut  $rut
     * @param  string  $boolean
     * @param  bool  $not
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereRut(Builder $builder, int|string|iterable|Arrayable|Rut $rut, string $boolean = 'and', bool $not = false): Builder
    {
        if (is_iterable($rut) || $rut instanceof Arrayable) {
            return static::whereRutIn($builder, $rut, $boolean, $not);
        }

        return $builder->where(
            $builder->getModel()->getQualifiedRutNumColumn(), $not ? '!=' : '=', Rut::split($rut)[0], $boolean
        );
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut  $rut
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function orWhereRut(Builder $builder, iterable|int|string|Arrayable|Rut $rut): Builder
    {
        return static::whereRut($builder, $rut, 'or');
    }

    /**
     * Adds a `WHERE` clause to the query without the RUT number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut  $rut
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereRutNot(Builder $builder, int|string|iterable|Arrayable|Rut $rut, string $boolean = 'and'): Builder
    {
        return static::whereRut($builder, $rut, $boolean, true);
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut  $rut
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function orWhereRutNot(Builder $builder, iterable|int|string|Arrayable|Rut $rut): Builder
    {
        return static::whereRutNot($builder, $rut, 'or');
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|\Illuminate\Contracts\Support\Arrayable  $ruts
     * @param  string  $boolean
     * @param  bool  $not
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereRutIn(Builder $builder, iterable|Arrayable $ruts, string $boolean = 'and', bool $not = false): Builder
    {
        $ruts = BaseCollection::make($ruts)->map(static function (int|string|Rut $rut): int {
            return Rut::split($rut)[0];
        });

        return $builder->whereIn(
            $builder->getModel()->getQualifiedRutNumColumn(), $ruts, $boolean, $not
        );
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|\Illuminate\Contracts\Support\Arrayable  $ruts
     * @param  bool  $not
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function orWhereRutIn(Builder $builder, iterable|Arrayable $ruts, bool $not = false): Builder
    {
        return static::whereRutIn($builder, $ruts, 'or', $not);
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|\Illuminate\Contracts\Support\Arrayable  $ruts
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereRutNotIn(Builder $builder, iterable|Arrayable $ruts, string $boolean = 'and'): Builder
    {
        return static::whereRutIn($builder, $ruts, $boolean, true);
    }

    /**
     * Adds a `WHERE IN` clause to the query with the RUTs number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  iterable|\Illuminate\Contracts\Support\Arrayable  $ruts
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function orWhereRutNotIn(Builder $builder, iterable|Arrayable $ruts): Builder
    {
        return static::orWhereRutIn($builder, $ruts, true);
    }
}
