<?php

namespace Laragear\Rut\Livewire\Synthesizers;

use Laragear\Rut\Exceptions\RutException;
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

/**
 * @see https://livewire.laravel.com/docs/synthesizers
 */
class RutSynth extends Synth
{
    /**
     * The default key to use the synth in the frontend.
     *
     * @const string
     */
    public const DEFAULT_KEY = 'rut';

    /**
     * The default key to use for serialization
     */
    public static $key = self::DEFAULT_KEY;

    /**
     * Matches the data object from the backend.
     */
    public static function match(mixed $target): bool
    {
        return $target instanceof Rut;
    }

    /**
     * Receive the data from the frontend into the backend.
     */
    public function hydrate(int|string|null $value): ?Rut
    {
        if (!$value) {
            return null;
        }

        try {
            return Rut::parse($value);
        } catch (RutException) {
            return null;
        }
    }

    /**
     * Receive the data from the backend into the frontend.
     *
     * @return array{0: string|null, array{}}
     */
    public function dehydrate(?Rut $target): array
    {
        return [$target?->format(RutFormat::Raw), []];
    }
}
