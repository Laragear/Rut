<?php

namespace Tests;

use Laragear\Rut\Facades\Generator as GeneratorFacade;
use Laragear\Rut\Generator;
use LogicException;

class GeneratorTest extends TestCase
{
    protected Generator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator ??= $this->app->make(Generator::class);
    }

    public function test_registers_facade(): void
    {
        static::assertInstanceOf(Generator::class, GeneratorFacade::getFacadeRoot());
    }

    public function test_immutable(): void
    {
        static::assertNotSame($this->generator, $this->generator->between(0, 100000000));
        static::assertNotSame($this->generator, $this->generator->unique(false));
    }

    public function test_generates_one(): void
    {
        $rut = $this->generator->makeOne();

        static::assertTrue($rut->isValid());
    }

    public function test_generates_15_by_default(): void
    {
        $collection = $this->generator->make();

        static::assertCount(15, $collection);
    }

    public function test_generates_many(): void
    {
        $collection = $this->generator->make(10);

        static::assertCount(10, $collection);
    }

    public function test_makes_person_rut(): void
    {
        $rut = $this->generator->asPeople()->makeOne();

        static::assertGreaterThanOrEqual(100000, $rut->num);
        static::assertLessThan(46000000, $rut->num);
    }

    public function test_makes_investor_rut(): void
    {
        $rut = $this->generator->asInvestors()->makeOne();

        static::assertGreaterThanOrEqual(46000000, $rut->num);
        static::assertLessThan(47000000, $rut->num);
    }

    public function test_makes_investment_companies_rut(): void
    {
        $rut = $this->generator->asInvestmentCompanies()->makeOne();

        static::assertGreaterThanOrEqual(47000000, $rut->num);
        static::assertLessThan(48000000, $rut->num);
    }

    public function test_makes_contingency_rut(): void
    {
        $rut = $this->generator->asContingency()->makeOne();

        static::assertGreaterThanOrEqual(48000000, $rut->num);
        static::assertLessThan(60000000, $rut->num);
    }

    public function test_makes_company_rut(): void
    {
        $rut = $this->generator->asCompanies()->makeOne();

        static::assertGreaterThanOrEqual(60000000, $rut->num);
        static::assertLessThan(100000000, $rut->num);
    }

    public function test_makes_temporal_rut(): void
    {
        $rut = $this->generator->asTemporal()->makeOne();

        static::assertGreaterThanOrEqual(100000000, $rut->num);
        static::assertLessThan(200000000, $rut->num);
    }

    public function test_makes_definitive_rut(): void
    {
        $rut = $this->generator->asDefinitive()->makeOne();

        static::assertGreaterThanOrEqual(100000, $rut->num);
        static::assertLessThan(200000000, $rut->num);
    }

    public function test_makes_any_rut(): void
    {
        $rut = $this->generator->asAnything()->makeOne();

        static::assertGreaterThanOrEqual(100000, $rut->num);
        static::assertLessThanOrEqual(200000000, $rut->num);
    }

    public function test_makes_rut_between_two_numbers(): void
    {
        $rut = $this->generator->between(10000, 10000)->makeOne();

        static::assertSame(10000, $rut->num);
    }

    public function test_doesnt_throws_if_possible_combinations_more_than_iterations(): void
    {
        $collection = $this->generator->between(10000, 10010)->make(10);

        static::assertCount(10, $collection);

        $collection = $this->generator->between(10000, 10010)->make(9);

        static::assertCount(9, $collection);
    }

    public function test_exception_if_possible_combinations_less_than_iterations(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The set 11 iterations exceeds the possible 10 iterations.');

        $this->generator->between(10000, 10010)->make(11);
    }

    public function test_can_make_unique_ruts(): void
    {
        $collection = $this->generator->unique()->between(10000, 10010)->make(10);

        static::assertCount(10, $collection);
    }

    public function test_makes_empty_collection_if_iterations_zero(): void
    {
        $collection = $this->generator->make(0);

        static::assertEmpty($collection);
    }

    public function test_makes_empty_collection_if_iterations_negative(): void
    {
        $collection = $this->generator->make(-2);

        static::assertEmpty($collection);
    }
}
