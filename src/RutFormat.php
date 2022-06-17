<?php

declare(strict_types=1);

namespace Laragear\Rut;

use function number_format;

enum RutFormat
{
    /**
     * The default format to use when serializing RUTs.
     *
     * @var \Laragear\Rut\RutFormat
     */
    public const DEFAULT = self::Strict;

    /**
     * Sets RUT representation with only its characters.
     *
     * @example "187654321"
     */
    case Raw;

    /**
     * Sets RUT representation with a hyphen only.
     *
     * @example "18765432-1"
     */
    case Basic;

    /**
     * Sets RUT representation with a thousand separator and hyphen.
     *
     * @example "18.765.432-1"
     */
    case Strict;

    /**
     * Formats a RUT.
     *
     * @param  \Laragear\Rut\Rut  $rut
     * @return string
     */
    public function format(Rut $rut): string
    {
        return match ($this) {
            self::Strict => number_format($rut->num, 0, null, '.').'-'.$rut->vd,
            self::Basic => "$rut->num-$rut->vd",
            default => "$rut->num$rut->vd",
        };
    }
}
