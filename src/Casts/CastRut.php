<?php

namespace Laragear\Rut\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laragear\Rut\Rut;
use Laragear\Rut\RutIn;
use ValueError;
use function is_string;

class CastRut implements CastsAttributes
{
    /**
     * @inheritdoc
     *
     * @param  \Laragear\Rut\Rut|string|int|null  $value
     */
    public function get($model, string $key, $value, array $attributes): ?Rut
    {
        $config = $this->findConfigForAttribute($model, $key);

        if (isset($attributes[$config->num])) {
            if ($config->vd) {
                if (!isset($attributes[$config->vd])) {
                    throw new ValueError("The RUT Verification Digit is required for the [$key] key.");
                }

                return new Rut($attributes[$config->num], $attributes[$config->vd]);
            }

            return Rut::fromNum($attributes[$config->num]);
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @param  \Laragear\Rut\Rut|string|int|null  $value
     * @return array{string: int|null, string?: string|int|null}
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        $config = $this->findConfigForAttribute($model, $key);

        $array = [$config->num => null];
        $config->vd && $array[$config->vd] = null;

        if (null === $value) {
            return $array;
        }

        // By this point the string should be already validated.
        $value = Rut::parse($value);

        $array[$config->num] = $value->num;
        $config->vd && $array[$config->vd] = $value->vd;

        return $array;
    }


    /**
     * Finds the configuration for the RUT attribute of the given key.
     */
    protected function findConfigForAttribute(Model $model, string $key): RutIn
    {
        /** @var \Laragear\Rut\RutIn|string|null $rut */
        $rut = $model->ruts()[$key] ?? null; // @phpstan-ignore-line

        if (!$rut) {
            throw new InvalidArgumentException("No RUT set for the [$key] attribute.");
        }

        if (is_string($rut)) {
            $rut = RutIn::fromString($rut);
        }

        return $rut;
    }
}
