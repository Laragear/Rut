<?php

declare(strict_types=1);

namespace Laragear\Rut;

use Illuminate\Support\Collection;
use LogicException;
use function max;
use function rand;

class Generator
{
    /**
     * The default number of iterations.
     *
     * @var int
     */
    protected const ITERATIONS = 15;

    /** @var array Boundaries for all types of RUT */
    protected const BOUNDARY_NONE = [Rut::MIN, Rut::MAX];
    /** @var array Boundaries for people RUTs */
    protected const BOUNDARY_PEOPLE = [Rut::MIN, Rut::INVESTOR_BASE - 1];
    /** @var array Boundaries for investor RUTs */
    protected const BOUNDARY_INVESTOR = [Rut::INVESTOR_BASE, Rut::INVESTMENT_COMPANY_BASE - 1];
    /** @var array Boundaries for investment companies RUTs */
    protected const BOUNDARY_INVESTMENT_COMPANY = [Rut::INVESTMENT_COMPANY_BASE, Rut::CONTINGENCY_BASE - 1];
    /** @var array Boundaries for contingency RUTs */
    protected const BOUNDARY_CONTINGENCY = [Rut::CONTINGENCY_BASE, Rut::COMPANY_BASE - 1];
    /** @var array Boundaries for company RUTs */
    protected const BOUNDARY_COMPANIES = [Rut::COMPANY_BASE, Rut::TEMPORAL_BASE - 1];
    /** @var array Boundaries for temporal RUTs */
    protected const BOUNDARY_TEMPORAL = [Rut::TEMPORAL_BASE, Rut::MAX];

    /**
     * Create a new Generator instance.
     *
     * @param  int  $iterations
     * @param  int  $min
     * @param  int  $max
     * @param  bool  $unique
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
     *
     * @param  bool  $unique
     * @return static
     */
    public function unique(bool $unique = true): static
    {
        return new static($this->iterations, $this->min, $this->max, $unique);
    }

    /**
     * Sets the generator to create people RUTs.
     *
     * @return static
     */
    public function asPeople(): static
    {
        return $this->between(...static::BOUNDARY_PEOPLE);
    }

    /**
     * Sets the generator to create investor RUTs.
     *
     * @return static
     */
    public function asInvestors(): static
    {
        return $this->between(...static::BOUNDARY_INVESTOR);
    }

    /**
     * Sets the generator to create investment companies RUTs.
     *
     * @return static
     */
    public function asInvestmentCompanies(): static
    {
        return $this->between(...static::BOUNDARY_INVESTMENT_COMPANY);
    }

    /**
     * Sets the generator to create contingency RUTs.
     *
     * @return static
     */
    public function asContingency(): static
    {
        return $this->between(...static::BOUNDARY_CONTINGENCY);
    }

    /**
     * Sets the generator to create company RUTs.
     *
     * @return static
     */
    public function asCompanies(): static
    {
        return $this->between(...static::BOUNDARY_COMPANIES);
    }

    /**
     * Sets the generator to create temporal RUTs.
     *
     * @return static
     */
    public function asTemporal(): static
    {
        return $this->between(...static::BOUNDARY_TEMPORAL);
    }

    /**
     * Sets the generator to create definitive RUTs.
     *
     * @return static
     */
    public function asDefinitive(): static
    {
        return $this->between(Rut::MIN, Rut::TEMPORAL_BASE - 1);
    }

    /**
     * Sets the generator to create any RUTs.
     *
     * @return static
     */
    public function asAnything(): static
    {
        return $this->between(...static::BOUNDARY_NONE);
    }

    /**
     * Sets the boundaries for numbers for the RUTs.
     *
     * @param  int  $min
     * @param  int  $max
     * @return static
     */
    public function between(int $min, int $max): static
    {
        return new static($this->iterations, $min, $max, $this->unique);
    }

    /**
     * Makes one random RUT.
     *
     * @return \Laragear\Rut\Rut
     */
    public function makeOne(): Rut
    {
        return $this->make(1)->first();
    }

    /**
     * Makes one or many random RUT.
     *
     * @param  int  $iterations
     * @return \Illuminate\Support\Collection<\Laragear\Rut\Rut>
     */
    public function make(int $iterations = self::ITERATIONS): Collection
    {
        return static::generate($iterations, $this->unique, $this->min, $this->max);
    }

    /**
     * Generates many random RUT.
     *
     * @param  int  $iterations
     * @param  bool  $unique
     * @param  int  $min
     * @param  int  $max
     * @return \Illuminate\Support\Collection<\Laragear\Rut\Rut>
     */
    protected static function generate(int $iterations, bool $unique, int $min, int $max): Collection
    {
        $min = max(0, $min);
        $max = max($min, $max);

        static::validateIterationsUnderBoundaries($iterations, $min, $max);

        $ruts = Collection::times($iterations, static function () use ($min, $max): Rut {
            return new Rut($num = rand($min, $max), Rut::getVd($num));
        });

        // When forcing unique RUTs to avoid collisions, we'll reject duplicates
        // RUTs from the collection, get the remaining RUTs to make, and recall
        // this function for the remaining items, merge, and check this again.
        /** @codeCoverageIgnoreStart  */
        if ($unique) {
            do {
                $ruts = $ruts->unique();

                $ruts = $ruts->merge(static::generate($iterations - $ruts->count(), false, $min, $max));
            } while ($ruts->count() < $iterations);
        }
        /** @codeCoverageIgnoreEnd  */

        return $ruts;
    }

    /**
     * Check if the possible iterations for the generator exceed the boundaries.
     *
     * @param  int  $iterations
     * @param  int  $min
     * @param  int  $max
     * @return void
     */
    protected static function validateIterationsUnderBoundaries(int $iterations, int $min, int $max): void
    {
        $possible = $max - $min;

        if ($min !== $max && $possible < $iterations) {
            throw new LogicException("The set $iterations iterations exceeds the possible $possible iterations.");
        }
    }
}
