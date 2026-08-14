<?php

namespace Laragear\Rut\Eloquent;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Laragear\Rut\Rut;

class RutAttribute extends Attribute
{
    /**
     * Create a new RUT Attribute instance.
     */
    public static function for(string $attribute): static
    {
        return static::make(
            get: static function (null $value, array $attributes) use ($attribute): ?Rut {
                return isset($attributes[$attribute.'_num'])
                    ? new Rut($attributes[$attribute.'_num'], $attributes[$attribute.'_vd'])
                    : null;
            },
            set: static function (mixed $value) use ($attribute): array {
                $value = $value === null ? null : Rut::parse($value);

                return [
                    $attribute.'_num' => $value?->num,
                    $attribute.'_vd' => $value?->vd,
                ];
            }
        )
            ->withoutObjectCaching();
    }

    /**
     * Create a new RUT Attribute instance only to retrieve the RUT number as a `Rut` instance.
     */
    public static function forNum(string $attribute): static
    {
        return static::make(
            get: static function (mixed $value): ?Rut {
                return isset($value) ? Rut::fromNum($value) : null;
            },
            set: static function (mixed $value) use ($attribute): array {
                if ($value !== null) {
                    [$value] = Rut::split($value);
                }

                return [$attribute => $value];
            }
        )
            ->withoutObjectCaching();
    }
}
