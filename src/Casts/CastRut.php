<?php

namespace Laragear\Rut\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Laragear\Rut\Rut;

class CastRut implements CastsAttributes
{
    /**
     * @inheritdoc
     */
    public function get($model, string $key, $value, array $attributes): ?Rut
    {
        // Only return a Rut instance when both number and verification digit are filled.
        // @phpstan-ignore-next-line
        if (isset($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()])) {
            // @phpstan-ignore-next-line
            return new Rut($attributes[$model->getRutNumColumn()], $attributes[$model->getRutVdColumn()]);
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @param  \Laragear\Rut\Rut|string|int|null  $value
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        if (null === $value) {
            return [
                // @phpstan-ignore-next-line
                $model->getRutNumColumn() => null,
                // @phpstan-ignore-next-line
                $model->getRutVdColumn() => null,
            ];
        }

        // By this point the string should be already validated.
        $value = Rut::parse($value);

        return [
            // @phpstan-ignore-next-line
            $model->getRutNumColumn() => $value->num,
            // @phpstan-ignore-next-line
            $model->getRutVdColumn() => $value->vd,
        ];
    }
}
