<?php

namespace Tests;

use Illuminate\Support\Collection;
use Laragear\Rut\Rut;
use Laragear\Rut\ValidatesRut;
use PHPUnit\Framework\TestCase as PHPUnit;

class ValidatesRutTest extends PHPUnit
{
    protected function setUp(): void
    {
        ValidatesRut::setDummies();
    }

    public function test_uses_default_list(): void
    {
        $original = Collection::make(ValidatesRut::DUMMY_RUTS)->map(static function (true $value, int $num): Rut {
            return Rut::fromNum($num);
        })->values();

        static::assertEquals($original, ValidatesRut::dummies());
    }

    public function test_sets_custom_dummies_and_corrects_them(): void
    {
        ValidatesRut::setDummies([
            '24.000.000-8',
            '18.765.432-1', // Invalid will be corrected
        ]);

        static::assertSame('24.000.000-8', ValidatesRut::dummies()->get(0)->formatStrict());
        static::assertSame('18.765.432-7', ValidatesRut::dummies()->get(1)->formatStrict());
    }

    public function test_sets_original_dummies_list(): void
    {
        $this->test_sets_custom_dummies_and_corrects_them();

        ValidatesRut::setDummies(null);

        $this->test_uses_default_list();
    }
}
