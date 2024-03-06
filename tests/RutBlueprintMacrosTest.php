<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

use function tap;

class RutBlueprintMacrosTest extends TestCase
{
    public function test_helper_returns_rut_num_column(): void
    {
        $column = (new Blueprint('test_table'))->rut();

        static::assertInstanceOf(ColumnDefinition::class, $column);
        static::assertSame('rut_num', $column->get('name'));

        $column = (new Blueprint('test_table'))->rutNullable();

        static::assertInstanceOf(ColumnDefinition::class, $column);
        static::assertSame('rut_num', $column->get('name'));
    }

    public function test_helper_register_two_rut_columns(): void
    {
        $blueprint = tap(new Blueprint('test_table'))->rut();

        [$rutNum, $rutVd] = $blueprint->getColumns();

        static::assertSame('rut_num', $rutNum->get('name'));
        static::assertSame('integer', $rutNum->get('type'));
        static::assertFalse($rutNum->get('autoIncrement'));
        static::assertTrue($rutNum->get('unsigned'));

        static::assertSame('rut_vd', $rutVd->get('name'));
        static::assertSame('char', $rutVd->get('type'));
    }

    public function test_helper_register_columns_with_custom_name(): void
    {
        $blueprint = tap(new Blueprint('test_table'))->rut('foo');

        [$rutNum, $rutVd] = $blueprint->getColumns();

        static::assertSame('foo_num', $rutNum->get('name'));
        static::assertSame('integer', $rutNum->get('type'));
        static::assertFalse($rutNum->get('autoIncrement'));
        static::assertTrue($rutNum->get('unsigned'));

        static::assertSame('foo_vd', $rutVd->get('name'));
        static::assertSame('char', $rutVd->get('type'));
    }

    public function test_helper_register_two_rut_columns_nullable(): void
    {
        $blueprint = tap(new Blueprint('test_table'))->rutNullable();

        [$rutNum, $rutVd] = $blueprint->getColumns();

        static::assertSame('rut_num', $rutNum->get('name'));
        static::assertSame('integer', $rutNum->get('type'));
        static::assertFalse($rutNum->get('autoIncrement'));
        static::assertTrue($rutNum->get('unsigned'));
        static::assertTrue($rutNum->get('nullable'));

        static::assertSame('rut_vd', $rutVd->get('name'));
        static::assertSame('char', $rutVd->get('type'));
        static::assertTrue($rutVd->get('nullable'));
    }

    public function test_helper_register_columns_with_custom_name_nullable(): void
    {
        $blueprint = tap(new Blueprint('test_table'))->rutNullable('foo');

        [$rutNum, $rutVd] = $blueprint->getColumns();

        static::assertSame('foo_num', $rutNum->get('name'));
        static::assertSame('integer', $rutNum->get('type'));
        static::assertFalse($rutNum->get('autoIncrement'));
        static::assertTrue($rutNum->get('unsigned'));
        static::assertTrue($rutNum->get('nullable'));

        static::assertSame('foo_vd', $rutVd->get('name'));
        static::assertSame('char', $rutVd->get('type'));
        static::assertTrue($rutVd->get('nullable'));
    }
}
