<?php

namespace Tests\Eloquent;

use Laragear\Rut\Eloquent\RutAttribute;
use Laragear\Rut\Rut;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RutAttributeTest extends TestCase
{
    public function test_for_returns_canonical_attribute(): void
    {
        $attribute = RutAttribute::for('test');

        $result = ($attribute->get)(null, ['test_num' => 11_111_111, 'test_vd' => 1]);

        static::assertInstanceOf(Rut::class, $result);
        static::assertEquals('11.111.111-1', $result);
    }

    public function test_it_returns_null_when_no_num_exist(): void
    {
        $attribute = RutAttribute::for('test');

        $result = ($attribute->get)(null, ['test_num' => null, 'test_vd' => 1]);

        static::assertNull($result);
    }

    public function test_for_stores_null(): void
    {
        $attribute = RutAttribute::for('test');

        $result = ($attribute->set)(null);

        static::assertSame([
            'test_num' => null,
            'test_vd' => null,
        ], $result);
    }

    public static function providesRutValues(): iterable
    {
        return [
            'string' => ['11.111.111-1'],
            'integer' => [11_111_111_1],
            'rut' => [new Rut(11_111_111, 1)],
        ];
    }

    #[DataProvider('providesRutValues')]
    public function test_for_stores_canonical_attribute(mixed $rut): void
    {
        $attribute = RutAttribute::for('test');

        $result = ($attribute->set)($rut);

        static::assertSame([
            'test_num' => 11_111_111,
            'test_vd' => '1'
        ], $result);
    }

    public function test_for_num_returns_rut_from_number(): void
    {
        $attribute = RutAttribute::forNum('test_num');

        $result = ($attribute->get)(11_111_111);

        static::assertInstanceOf(Rut::class, $result);
        static::assertEquals('11.111.111-1', $result);
    }

    public function test_for_num_returns_null_when_attribute_null(): void
    {
        $attribute = RutAttribute::forNum('test_num');

        $result = ($attribute->get)(null);

        static::assertNull($result);
    }

    #[DataProvider('providesRutValues')]
    public function test_for_num_stores_num(mixed $rut): void
    {
        $attribute = RutAttribute::forNum('test');

        $result = ($attribute->set)($rut);

        static::assertSame([
            'test' => 11_111_111,
        ], $result);
    }

    public function test_for_num_stores_null(): void
    {
        $attribute = RutAttribute::forNum('test');

        $result = ($attribute->set)(null);

        static::assertSame([
            'test' => null,
        ], $result);
    }
}
