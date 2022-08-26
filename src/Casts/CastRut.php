<?php

namespace Laragear\Rut\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JetBrains\PhpStorm\Pure;
use Laragear\Rut\Rut;

class CastRut implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Laragear\Rut\Rut|null
     */
    #[Pure]
    public function get($model, string $key, $value, array $attributes): ?Rut
    {
        // @phpstan-ignore-next-line
        if (isset($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()])) {
            // @phpstan-ignore-next-line
            return new Rut($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()]);
        }

        return null;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array|null
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        if (null === $value) {
            return [
                // @phpstan-ignore-next-line
                $model->getRutNumColumn() => null,
                // @phpstan-ignore-next-line
                $model->getRutVdColumn()  => null,
            ];
        }

        $value = Rut::parse($value);

        return [
            // @phpstan-ignore-next-line
            $model->getRutNumColumn() => $value->num,
            // @phpstan-ignore-next-line
            $model->getRutVdColumn()  => $value->vd,
        ];
    }
}
