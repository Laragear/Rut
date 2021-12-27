<?php

declare(strict_types=1);

namespace Laragear\Rut\Scopes;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Scope;
use Laragear\Rut\Rut;

class RutScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected const EXTENSIONS = [
        'findRut',
        'findManyRut',
        'findRutOrFail',
        'findRutOrNew',
        'whereRut',
        'orWhereRut',
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     *
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
     *
     * @return void
     */
    public function extend(Builder $builder): void
    {
        foreach (static::EXTENSIONS as $extension) {
            $builder->macro($extension, [__CLASS__, $extension]);
        }
    }

    /**
     * Find a model by its RUT number key.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut|iterable|int|string  $rut
     * @param  array|string  $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRut(
        Builder $builder,
        Arrayable|Rut|iterable|int|string $rut,
        string|array $columns = ['*']
    ): Model|Collection|null
    {
        if (is_array($rut) || $rut instanceof Arrayable) {
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
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findManyRut(
        Builder $builder,
        Arrayable|iterable $ruts,
        array|string $columns = ['*']
    ): Collection
    {
        $ruts = $ruts instanceof Arrayable ? $ruts->toArray() : $ruts;

        foreach ($ruts as $key => $id) {
            $ruts[$key] = Rut::split($id)[0];
        }

        return $builder->whereIn($builder->getModel()->getQualifiedRutNumColumn(), $ruts)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut|iterable|int|string  $rut
     * @param  array|string  $columns
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRutOrFail(
        Builder $builder,
        Arrayable|Rut|iterable|int|string $rut,
        array|string $columns = ['*']
    ): Model|Collection
    {
        $result = static::findRut($builder, $rut, $columns);

        $rut = $rut instanceof Arrayable ? $rut->toArray() : $rut;

        if (is_array($rut)) {
            if (count($result) === count(array_unique($rut))) {
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
     * @param  \Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut|iterable|int|string  $rut
     * @param  array|string  $columns
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function findRutOrNew(
        Builder $builder,
        Arrayable|Rut|iterable|int|string $rut,
        array|string $columns = ['*']
    ): Model
    {
        return static::findRut($builder, $rut, $columns) ?? $builder->newModelInstance();
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  int|string|\Laragear\Rut\Rut  $rut
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereRut(Builder $builder, Rut|int|string $rut, string $boolean = 'and'): Builder
    {
        return $builder->where($builder->getModel()->getQualifiedRutNumColumn(), Rut::split($rut)[0], null, $boolean);
    }

    /**
     * Adds a `WHERE` clause to the query with the RUT number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  int|string|\Laragear\Rut\Rut  $rut
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function orWhereRut(Builder $builder, Rut|int|string $rut): Builder
    {
        return static::whereRut($builder, $rut, 'or');
    }
}