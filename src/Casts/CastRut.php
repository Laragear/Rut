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
     * @param  \Illuminate\Database\Eloquent\Model|\Laragear\Rut\HasRut  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Laragear\Rut\Rut|null
     */
    #[Pure]
    public function get($model, string $key, $value, array $attributes): ?Rut
    {
        if (isset($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()])) {
            return new Rut($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()]);
        }

        return null;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Laragear\Rut\HasRut  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array|null
     *
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        if (null === $value) {
            return [
                $model->getRutNumColumn() => null,
                $model->getRutVdColumn()  => null,
            ];
        }

        $value = Rut::parse($value);

        return [
            $model->getRutNumColumn() => $value->num,
            $model->getRutVdColumn()  => $value->vd,
        ];
    }
}
