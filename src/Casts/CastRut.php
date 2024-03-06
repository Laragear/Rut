<?php

namespace Laragear\Rut\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Laragear\Rut\Rut;

class CastRut implements CastsAttributes
{
    /**
     * @inheritdoc
     *
     * @param  \Illuminate\Database\Eloquent\Model&\Laragear\Rut\HasRut  $model
     */
    public function get($model, string $key, $value, array $attributes): ?Rut
    {
        // Only return a Rut instance when both number and verification digit are filled.
        if (isset($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()])) {
            return new Rut($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()]);
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @param  \Illuminate\Database\Eloquent\Model&\Laragear\Rut\HasRut  $model
     * @param  \Laragear\Rut\Rut|string|int|null  $value
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        if (null === $value) {
            return [
                $model->getRutNumColumn() => null,
                $model->getRutVdColumn() => null,
            ];
        }

        // By this point the string should be already validated.
        $value = Rut::parse($value);

        return [
            $model->getRutNumColumn() => $value->num,
            $model->getRutVdColumn() => $value->vd,
        ];
    }
}
