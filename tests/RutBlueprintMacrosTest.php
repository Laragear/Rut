<?php

namespace Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\Grammars\Grammar;

use function tap;

class RutBlueprintMacrosTest extends TestCase
{
    protected function blueprint(): Blueprint
    {
        $connection = $this->mock(Connection::class);
        $connection->expects('getSchemaGrammar')->zeroOrMoreTimes()->andReturn($this->mock(Grammar::class));

        return $this->app->make(Blueprint::class, [
            'table' => 'table',
            'connection' => $connection,
        ]);
    }

    public function test_helper_returns_rut_num_column(): void
    {
        $column = $this->blueprint()->rut();

        static::assertInstanceOf(ColumnDefinition::class, $column);
        static::assertSame('rut_num', $column->get('name'));

        $column = $this->blueprint()->rutNullable();

        static::assertInstanceOf(ColumnDefinition::class, $column);
        static::assertSame('rut_num', $column->get('name'));
    }

    public function test_helper_register_two_rut_columns(): void
    {
        $blueprint = tap($this->blueprint())->rut();

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
        $blueprint = tap($this->blueprint())->rut('foo');

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
        $blueprint = tap($this->blueprint())->rutNullable();

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
        $blueprint = tap($this->blueprint())->rutNullable('foo');

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
