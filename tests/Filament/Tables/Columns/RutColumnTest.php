<?php

namespace Tests\Filament\Tables\Columns;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\TablesServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Laragear\Rut\Filament\Tables\Columns\RutColumn;
use Laragear\Rut\HasRut;
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;
use Mockery;
use Tests\TestCase;

use function array_merge;

class RutColumnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Rut::$format = RutFormat::DEFAULT;
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            TablesServiceProvider::class,
        ]);
    }

    protected function model(array $attributes = ['rut_num' => 18765432, 'rut_vd' => 1]): Model
    {
        return new class($attributes) extends Model
        {
            use HasRut;
            protected $guarded = [];
        };
    }

    protected function rutColumn(string $name = 'rut')
    {
        $hasTable = Mockery::mock(HasTable::class);
        $hasTable->expects('getTableRecordKey')->andReturn(1)->atLeast()->once();

        $table = Mockery::mock(Table::class);
        $table->expects('getLivewire')->andReturn($hasTable)->atLeast()->once();

        $column = (new RutColumn($name));
        $column->table($table);
        $column->configure();

        return $column;
    }

    public function test_returns_null_if_rut_is_empty(): void
    {
        static::assertNull(
            $this->rutColumn()->record($this->model(['rut_num' => 18765432, 'rut_vd' => null]))->getState()
        );
        static::assertNull(
            $this->rutColumn()->record($this->model(['rut_num' => null, 'rut_vd' => 1]))->getState()
        );
    }

    public function test_returns_null_if_the_attribute_is_null(): void
    {
        static::assertNull($this->rutColumn('invalid')->record($this->model())->getState());
    }

    public function test_uses_rut_instance_by_default(): void
    {
        static::assertSame('18.765.432-1', $this->rutColumn()->record($this->model())->getState());
    }

    public function test_uses_from_array_as_list(): void
    {
        $column = $this->rutColumn()->fromRutArray()->record(new class() extends Model
        {
            public $attributes = [
                'rut' => [18765432, 1],
            ];
        });

        static::assertSame('18.765.432-1', $column->getState());
    }

    public function test_uses_from_array_with_keys(): void
    {
        $column = $this->rutColumn()->fromRutArray();

        $column->record(new class extends Model
        {
            public $attributes = [
                'rut' => ['num' => 18765432, 'vd' => 1],
            ];
        });

        static::assertSame('18.765.432-1', $column->getState());
    }

    public function test_uses_from_rut_number(): void
    {
        $column = $this->rutColumn()->fromRutNumber();

        $column->record(new class extends Model
        {
            public $attributes = [
                'rut' => 50537182,
            ];
        });

        static::assertSame('50.537.182-8', $column->getState());
    }

    public function test_uses_raw_format(): void
    {
        $column = $this->rutColumn()->record($this->model());

        static::assertSame('187654321', $column->formatAsRaw()->getState());
    }

    public function test_uses_basic_format(): void
    {
        $column = $this->rutColumn()->record($this->model());

        static::assertSame('18765432-1', $column->formatAsBasic()->getState());
    }

    public function test_uses_basic_strict(): void
    {
        Rut::$format = RutFormat::Raw;

        $column = $this->rutColumn()->record($this->model());

        static::assertSame('18.765.432-1', $column->formatAsStrict()->getState());
    }
}
