<?php

declare(strict_types=1);

namespace Laragear\Rut;

enum Format
{
    /**
     * The default format for RUTs.
     *
     * @var string
     */
    public const DEFAULT = self::Strict;

    /**
     * Sets RUT representation properly formatted.
     *
     * @example "18.765.432-1"
     */
    case Strict;

    /**
     * Sets RUT representation with basic format.
     *
     * @example "18765432-1"
     */
    case Basic;

    /**
     * Sets RUT representation with only the characters.
     *
     * @example "187654321"
     */
    case Raw;
}