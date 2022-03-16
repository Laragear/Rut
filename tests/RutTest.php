<?php

namespace Tests;

use function json_encode;
use Laragear\Rut\Exceptions\EmptyRutException;
use Laragear\Rut\Exceptions\InvalidRutException;
use Laragear\Rut\Generator;
use Laragear\Rut\Rut;
use PHPUnit\Framework\TestCase;
use function serialize;
use function unserialize;

class RutTest extends TestCase
{
    public function test_rut_saves_data(): void
    {
        $rut = new Rut(10, 'k');

        static::assertSame(10, $rut->num);
        static::assertSame('K', $rut->vd);
    }

    public function test_rut_sets_vd_as_string(): void
    {
        $rut = new Rut(10, 100);

        static::assertSame('100', $rut->vd);
        static::assertNotSame(100, $rut->vd);
    }

    public function test_rut_lowercase_vd(): void
    {
        Rut::$uppercase = false;

        $rut = new Rut(10, 'k');

        static::assertSame(10, $rut->num);
        static::assertSame('k', $rut->vd);
    }

    public function test_rut_checks_for_person(): void
    {
        $rut = new Rut(50000000 - 1, 'k');

        static::assertTrue($rut->isPerson());
        static::assertFalse($rut->isCompany());
    }

    public function test_rut_checks_for_company(): void
    {
        $rut = new Rut(50000000, 'k');

        static::assertFalse($rut->isPerson());
        static::assertTrue($rut->isCompany());
    }

    public function test_rut_checks_invalid_rut(): void
    {
        $rut = new Rut(10, 10);

        static::assertFalse($rut->isValid());
        static::assertTrue($rut->isInvalid());
    }

    public function test_rut_below_min_is_invalid(): void
    {
        static::assertFalse(Rut::fromNum(99999)->isValid());
        static::assertTrue(Rut::fromNum(100000)->isValid());
    }

    public function test_rut_over_max_is_invalid(): void
    {
        static::assertTrue(Rut::fromNum(100000000)->isValid());
        static::assertFalse(Rut::fromNum(100000001)->isValid());
    }

    public function test_ruts_checks_valid_rut(): void
    {
        $rut = (new Generator)->makeOne();

        static::assertTrue($rut->isValid());
        static::assertFalse($rut->isInvalid());
    }

    public function test_rut_validation_throws_exception_if_invalid(): void
    {
        $this->expectException(InvalidRutException::class);
        $this->expectExceptionMessage('The given RUT is invalid.');

        $rut = new Rut(10, 10);

        $rut->validate();
    }

    public function test_rut_validation_doesnt_throw_exception_if_valid(): void
    {
        $rut = (new Generator)->makeOne();

        $rut->validate();

        static::assertTrue($rut->isValid());
    }

    public function test_rut_is_equal_to_same_instance(): void
    {
        $rut = new Rut(10, 100);

        static::assertTrue($rut->isEqual(new Rut('10', '100')));
        static::assertFalse($rut->isNotEqual(new Rut('10', '100')));
    }

    public function test_rut_is_not_equal_to_different_instance(): void
    {
        $rut = new Rut(10, 100);

        static::assertFalse($rut->isEqual(new Rut(10, 'k')));
        static::assertTrue($rut->isNotEqual(new Rut(10, 'K')));
    }

    public function test_rut_is_equal_to_string(): void
    {
        $rut = new Rut(53851562, 0);

        static::assertTrue($rut->isEqual('53851562-0'));
        static::assertFalse($rut->isNotEqual('53851562-0'));
    }

    public function test_rut_is_not_equal_to_string(): void
    {
        $rut = new Rut(53851562, 0);

        static::assertFalse($rut->isEqual('72611521-4'));
        static::assertTrue($rut->isNotEqual('72611521-4'));
    }

    public function test_rut_is_equal_to_below_one_hundred_string(): void
    {
        $rut = Rut::fromNum(99999);

        static::assertTrue($rut->isEqual('99999-7'));
        static::assertFalse($rut->isNotEqual('99999-7'));
    }

    public function test_rut_is_not_equal_throws_exception_if_invalid_string(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 1 given.');

        static::assertFalse((new Rut(0, 'N'))->isEqual('0-MD'));
    }

    public function test_rut_formats_to_strict_by_default(): void
    {
        $rut = new Rut(53851562, 0);

        static::assertSame('53.851.562-0', $rut->format());
        static::assertSame('53.851.562-0', (string) $rut);
        static::assertSame('53.851.562-0', $rut->toString());
    }

    public function test_rut_formats_into_different_formats(): void
    {
        $rut = new Rut(53851562, 0);

        static::assertSame('53.851.562-0', $rut->format());
        static::assertSame('53.851.562-0', $rut->format(Rut::FORMAT_STRICT));
        static::assertSame('53851562-0', $rut->format(Rut::FORMAT_BASIC));
        static::assertSame('538515620', $rut->format(Rut::FORMAT_RAW));
    }

    public function test_rut_format_changes_globally(): void
    {
        $rut = new Rut(53851562, 0);

        Rut::$format = Rut::FORMAT_RAW;

        static::assertSame('538515620', $rut->format());
    }

    public function test_rut_json_to_string(): void
    {
        $rut = new Rut(53851562, 0);

        static::assertSame('"53.851.562-0"', $rut->toJson());
        static::assertSame('"53.851.562-0"', json_encode($rut));
    }

    public function test_rut_serializes_to_raw_string(): void
    {
        $rut = new Rut(53851562, 0);

        $serialized = serialize($rut);

        static::assertSame('O:16:"Laragear\Rut\Rut":1:{i:0;s:9:"538515620";}', $serialized);

        static::assertTrue($rut->isEqual(unserialize($serialized)));
    }

    public function test_parses_string(): void
    {
        $rut = Rut::parse('996377024');

        static::assertTrue($rut->isValid());
    }

    public function test_parse_throws_exception_if_string_invalid(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 1 given.');

        Rut::parse('1');
    }

    public function test_maps_string_ruts(): void
    {
        $ruts = ['996377024', new Rut('63763609', 'K'), '!4!@&8250863*-4!'];

        Rut::map($ruts)->each(static function (Rut $rut, int $key) use ($ruts): void {
            static::assertTrue($rut->isEqual($ruts[$key]));
        });
    }

    public function test_maps_throws_exception_if_one_rut_invalid(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        $ruts = ['996377024', 'invalid', '!4!@&8250863*-4!'];

        Rut::map($ruts);
    }

    public function test_check_valid_rut(): void
    {
        static::assertTrue(Rut::check(996377024));
        static::assertTrue(Rut::check(99637702, 4));
    }

    public function test_checks_invalid_rut(): void
    {
        static::assertFalse(Rut::check(996377020));
        static::assertFalse(Rut::check(99637702, 0));
    }

    public function test_check_empty_rut(): void
    {
        static::assertFalse(Rut::check(0));
        static::assertFalse(Rut::check(1, 0));
    }

    public function test_splits_string_into_num_and_vd(): void
    {
        static::assertSame([99637702, '4'], Rut::split('996377024'));
    }

    public function test_split_throws_exception_if_invalid_string(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 1 given.');

        Rut::split('0');
    }

    public function test_math_vd_from_number(): void
    {
        static::assertSame('0', Rut::getVd(0));
        static::assertSame('5', Rut::getVd(10000));
        static::assertSame('4', Rut::getVd(99637702));
        static::assertSame('7', Rut::getVd(18765432));
    }

    public function test_creates_rut_from_number()
    {
        static::assertEquals('0-0', Rut::fromNum(0)->format());
        static::assertEquals('10.000-5', Rut::fromNum(10000)->format());
        static::assertEquals('99.637.702-4', Rut::fromNum(99637702)->format());
        static::assertEquals('18.765.432-7', Rut::fromNum(18765432)->format());
    }

    public function test_formats_json_using_different_format(): void
    {
        Rut::$jsonFormat = Rut::FORMAT_RAW;

        $rut = new Rut(99637702, '4');

        static::assertEquals('99.637.702-4', $rut->toString());
        static::assertEquals('99.637.702-4', $rut->format());
        static::assertEquals('"996377024"', $rut->toJson());
        static::assertEquals('"996377024"', json_encode($rut));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Rut::$uppercase = true;
        Rut::$format = RUT::FORMAT_STRICT;
        Rut::$jsonFormat = null;
    }
}
