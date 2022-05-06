<?php

namespace Laragear\Rut;

enum Format
{
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
    case Simple;

    /**
     * Sets RUT representation with thousand separator and hyphen.
     *
     * @example "18.765.432-1"
     */
    case Strict;
}