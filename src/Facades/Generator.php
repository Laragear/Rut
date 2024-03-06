<?php

namespace Laragear\Rut\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Laragear\Rut\Generator unique(bool $unique = true)
 * @method static \Laragear\Rut\Generator asPeople()
 * @method static \Laragear\Rut\Generator asInvestors()
 * @method static \Laragear\Rut\Generator asInvestmentCompanies()
 * @method static \Laragear\Rut\Generator asContingency()
 * @method static \Laragear\Rut\Generator asCompanies()
 * @method static \Laragear\Rut\Generator asTemporal()
 * @method static \Laragear\Rut\Generator asDefinitive()
 * @method static \Laragear\Rut\Generator asAnything()
 * @method static \Laragear\Rut\Generator between(int $min, int $max)
 * @method static \Illuminate\Support\Collection make(int $iterations = 15)
 * @method static \Laragear\Rut\Rut makeOne()
 * @method static \Laragear\Rut\Generator getFacadeRoot()
 *
 * @see \Laragear\Rut\Generator
 */
class Generator extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Laragear\Rut\Generator::class;
    }
}
