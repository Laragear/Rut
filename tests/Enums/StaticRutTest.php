<?php

namespace Tests\Enums;

use Generator;
use Laragear\Rut\Enums\StaticRut;
use Laragear\Rut\Rut;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StaticRutTest extends TestCase
{
    public static function providesStaticRuts(): Generator
    {
        yield [StaticRut::TestCorporateReceiver, '77.777.777-7'];
        yield [StaticRut::TestCorporateReceiverSecondary, '88.888.888-8'];
        yield [StaticRut::GenericConsumer, '66.666.666-6'];
        yield [StaticRut::ForeignEntity, '55.555.555-5'];
        yield [StaticRut::ForeignInvestor, '44.444.444-4'];
        yield [StaticRut::DigitalContentMonetization, '44.444.447-9'];
        yield [StaticRut::TaxAuthority, '60.803.000-K'];
        yield [StaticRut::NationalTreasury, '60.805.000-0'];
        yield [StaticRut::FinanceUndersecretariat, '60.801.000-9'];
        yield [StaticRut::CivilRegistry, '61.002.000-3'];
        yield [StaticRut::BudgetOffice, '60.802.000-4'];
        yield [StaticRut::CustomsService, '60.804.000-5'];
    }

    #[DataProvider('providesStaticRuts')]
    public function test_static_ruts_transforms_into_rut_instance(StaticRut $staticRut, string $rut): void
    {
        static::assertSame($staticRut->value, $rut);
        static::assertEquals($staticRut->toRut(), Rut::parse($rut));
    }
}
