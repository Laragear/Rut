<?php

declare(strict_types=1);

namespace Laragear\Rut;

use Illuminate\Support\Collection;
use LogicException;

use function array_values;
use function max;

class Generator
{
    /**
     * The default number of iterations.
     */
    protected const int ITERATIONS = 15;

    // Boundaries for all types of RUT.
    protected const array BOUNDARY_NONE = [Rut::MIN, Rut::MAX];
    // Boundaries for people RUTs.
    protected const array BOUNDARY_PEOPLE = [Rut::MIN, Rut::INVESTOR_BASE - 1];
    // Boundaries for investor RUTs.
    protected const array BOUNDARY_INVESTOR = [Rut::INVESTOR_BASE, Rut::INVESTMENT_COMPANY_BASE - 1];
    // Boundaries for investment companies RUTs.
    protected const array BOUNDARY_INVESTMENT_COMPANY = [Rut::INVESTMENT_COMPANY_BASE, Rut::CONTINGENCY_BASE - 1];
    // Boundaries for contingency RUTs.
    protected const array BOUNDARY_CONTINGENCY = [Rut::CONTINGENCY_BASE, Rut::COMPANY_BASE - 1];
    // Boundaries for company RUTs.
    protected const array BOUNDARY_COMPANIES = [Rut::COMPANY_BASE, Rut::TEMPORAL_BASE - 1];
    // Boundaries for temporal RUTs.
    protected const array BOUNDARY_TEMPORAL = [Rut::TEMPORAL_BASE, Rut::MAX];

    /**
     * Create a new Generator instance.
     */
    final public function __construct(
        protected int $iterations = self::ITERATIONS,
        protected int $min = Rut::MIN,
        protected int $max = Rut::MAX,
        protected bool $unique = false,
    ) {
        //
    }

    /**
     * Forces the generator to create unique RUTs.
     *
     * This can be performance detrimental on some scenarios or large iterations.
     */
    public function unique(bool $unique = true): static
    {
        return new static($this->iterations, $this->min, $this->max, $unique);
    }

    /**
     * Sets the generator to create people RUTs.
     */
    public function asPeople(): static
    {
        return $this->between(...static::BOUNDARY_PEOPLE);
    }

    /**
     * Sets the generator to create investor RUTs.
     */
    public function asInvestors(): static
    {
        return $this->between(...static::BOUNDARY_INVESTOR);
    }

    /**
     * Sets the generator to create investment companies RUTs.
     */
    public function asInvestmentCompanies(): static
    {
        return $this->between(...static::BOUNDARY_INVESTMENT_COMPANY);
    }

    /**
     * Sets the generator to create contingency RUTs.
     */
    public function asContingency(): static
    {
        return $this->between(...static::BOUNDARY_CONTINGENCY);
    }

    /**
     * Sets the generator to create company RUTs.
     */
    public function asCompanies(): static
    {
        return $this->between(...static::BOUNDARY_COMPANIES);
    }

    /**
     * Sets the generator to create temporal RUTs.
     */
    public function asTemporal(): static
    {
        return $this->between(...static::BOUNDARY_TEMPORAL);
    }

    /**
     * Sets the generator to create definitive RUTs.
     */
    public function asDefinitive(): static
    {
        return $this->between(Rut::MIN, Rut::TEMPORAL_BASE - 1);
    }

    /**
     * Sets the generator to create any RUTs.
     */
    public function asAnything(): static
    {
        return $this->between(...static::BOUNDARY_NONE);
    }

    /**
     * Sets the boundaries for numbers for the RUTs.
     */
    public function between(int $min, int $max): static
    {
        return new static($this->iterations, $min, $max, $this->unique);
    }

    /**
     * Makes one random RUT.
     */
    public function makeOne(): Rut
    {
        return $this->make(1)->first();
    }

    /**
     * Makes one or many random RUT.
     *
     * @return \Illuminate\Support\Collection<int, \Laragear\Rut\Rut>
     */
    public function make(int $iterations = self::ITERATIONS): Collection
    {
        return static::generate($iterations, $this->unique, $this->min, $this->max);
    }

    /**
     * Generates many random RUT.
     *
     * @return \Illuminate\Support\Collection<int, \Laragear\Rut\Rut>
     */
    protected static function generate(int $iterations, bool $unique, int $min, int $max): Collection
    {
        $min = max(0, $min);
        $max = max($min, $max);

        static::validateIterationsUnderBoundaries($iterations, $min, $max);

        $results = [];

        while (count($results) < $iterations) {
            $num = random_int($min, $max);

            if ($unique) {
                // Using the number as a key provides O(1) uniqueness check
                $results[$num] = new Rut($num, Rut::getVd($num));
            } else {
                $results[] = new Rut($num, Rut::getVd($num));
            }
        }

        return Collection::make(array_values($results));
    }

    /**
     * Check if the possible iterations for the generator exceed the boundaries.
     */
    protected static function validateIterationsUnderBoundaries(int $iterations, int $min, int $max): void
    {
        $possible = $max - $min;

        if ($min !== $max && $possible < $iterations) {
            throw new LogicException("The set $iterations iterations exceeds the possible $possible iterations.");
        }
    }
}
