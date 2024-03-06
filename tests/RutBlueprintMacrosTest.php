<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Arr;

class RutBlueprintMacrosTest extends TestCase
{
    public function test_helper_returns_rut_number_column(): void
    {
        $blueprint = new Blueprint('test_table');

        $number = $blueprint->rut();

        static::assertInstanceOf(ColumnDefinition::class, $number);
    }

    public function test_creates_database_with_rut_columns(): void
    {
        $schema = $this->app->make('db')->connection()->getSchemaBuilder();

        /** @var \Illuminate\Database\Schema\Blueprint $blueprint */
        $blueprint = null;

        $schema->create('test_table', function (Blueprint $table) use (&$blueprint) {
            $table->rut();

            $blueprint = $table;
        });

        static::assertTrue($schema->hasColumn('test_table', 'rut_num'));
        static::assertTrue($schema->hasColumn('test_table', 'rut_vd'));
        static::assertEquals('integer', $schema->getColumnType('test_table', 'rut_num'));
        static::assertEquals('varchar', $schema->getColumnType('test_table', 'rut_vd'));

        static::assertFalse($blueprint->getColumns()[0]->autoIncrement);
        static::assertNull($blueprint->getColumns()[0]->nullable);
        static::assertTrue($blueprint->getColumns()[0]->unsigned);

        static::assertEquals(1, $blueprint->getColumns()[1]->length);
        static::assertNull($blueprint->getColumns()[1]->nullable);

        $schema->create('test_table_with_index', function (Blueprint $table) {
            $table->rut()->index();
        });

        $indexes = $schema->getIndexes('test_table_with_index');

        static::assertSame('test_table_with_index_rut_num_index', Arr::get($indexes, '0.name'));
        static::assertSame(['rut_num'], Arr::get($indexes, '0.columns'));

        $schema->create('test_table_with_primary', function (Blueprint $table) {
            $table->rut()->primary();
        });

        $primary = $schema->getIndexes('test_table_with_primary');

        static::assertSame('primary', Arr::get($primary, '0.name'));
        static::assertSame(['rut_num'], Arr::get($primary, '0.columns'));
        static::assertTrue(Arr::get($primary, '0.unique'));
        static::assertTrue(Arr::get($primary, '0.primary'));

        $schema->create('test_table_with_unique', function (Blueprint $table) {
            $table->rut()->unique();
        });

        $unique = $schema->getIndexes('test_table_with_unique');

        static::assertSame('test_table_with_unique_rut_num_unique', Arr::get($unique, '0.name'));
        static::assertSame(['rut_num'], Arr::get($unique, '0.columns'));
        static::assertTrue(Arr::get($unique, '0.unique'));
        static::assertFalse(Arr::get($unique, '0.primary'));
    }

    public function test_creates_database_with_named_rut_columns(): void
    {
        /** @var \Illuminate\Database\Schema\Builder $schema */
        $schema = $this->app->make('db')->connection()->getSchemaBuilder();

        $schema->create('test_table', function (Blueprint $table) {
            $table->rut('foo');
        });

        static::assertTrue($schema->hasColumn('test_table', 'foo_num'));
        static::assertTrue($schema->hasColumn('test_table', 'foo_vd'));
    }

    public function test_creates_database_with_rut_nullable_columns(): void
    {
        /** @var \Illuminate\Database\Schema\Builder $schema */
        $schema = $this->app->make('db')->connection()->getSchemaBuilder();

        /** @var \Illuminate\Database\Schema\Blueprint $blueprint */
        $blueprint = null;

        $schema->create('test_table', function (Blueprint $table) use (&$blueprint) {
            $table->rutNullable();
            $blueprint = $table;
        });

        static::assertTrue($schema->hasColumn('test_table', 'rut_num'));
        static::assertTrue($schema->hasColumn('test_table', 'rut_vd'));
        static::assertEquals('integer', $schema->getColumnType('test_table', 'rut_num'));
        static::assertEquals('varchar', $schema->getColumnType('test_table', 'rut_vd'));

        static::assertFalse($blueprint->getColumns()[0]->autoIncrement);
        static::assertTrue($blueprint->getColumns()[0]->nullable);
        static::assertTrue($blueprint->getColumns()[0]->unsigned);

        static::assertEquals(1, $blueprint->getColumns()[1]->length);
        static::assertTrue($blueprint->getColumns()[1]->nullable);

        $schema->create('test_table_with_index', function (Blueprint $table) {
            $table->rut()->index();
        });

        $indexes = $schema->getIndexes('test_table_with_index');

        static::assertSame('test_table_with_index_rut_num_index', Arr::get($indexes, '0.name'));
        static::assertSame(['rut_num'], Arr::get($indexes, '0.columns'));
        static::assertFalse(Arr::get($indexes, '0.unique'));
        static::assertFalse(Arr::get($indexes, '0.primary'));

        $schema->create('test_table_with_primary', function (Blueprint $table) {
            $table->rut()->primary();
        });

        $primary = $schema->getIndexes('test_table_with_primary');

        static::assertSame('primary', Arr::get($primary, '0.name'));
        static::assertSame(['rut_num'], Arr::get($primary, '0.columns'));

        $schema->create('test_table_with_unique', function (Blueprint $table) {
            $table->rut()->unique();
        });

        $unique = $schema->getIndexes('test_table_with_unique');

        static::assertSame('test_table_with_unique_rut_num_unique', Arr::get($unique, '0.name'));
        static::assertSame(['rut_num'], Arr::get($unique, '0.columns'));
    }

    public function test_creates_database_with_named_rut_nullable_columns(): void
    {
        /** @var \Illuminate\Database\Schema\Builder $schema */
        $schema = $this->app->make('db')->connection()->getSchemaBuilder();

        $schema->create('test_table', function (Blueprint $table) {
            $table->rutNullable('foo');
        });

        static::assertTrue($schema->hasColumn('test_table', 'foo_num'));
        static::assertTrue($schema->hasColumn('test_table', 'foo_vd'));
    }
}
