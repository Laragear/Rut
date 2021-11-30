<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Laragear\Rut\Exceptions\EmptyRutException;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\Format;
use Laragear\Rut\HasRut;
use Laragear\Rut\Rut;
use Orchestra\Testbench\TestCase;

class HasRutTest extends TestCase
{
    use RegistersPackage;
    use PreparesDatabase;

    protected User $model;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(
            function () {
                $this->prepareDatabase();

                $this->model = new class extends User {
                    use HasRut;

                    protected $table = 'users';
                };
            }
        );

        parent::setUp();
    }

    public function test_model_retrieves_rut_instance(): void
    {
        static::assertInstanceOf(Rut::class, $this->model->first()->rut);
    }

    public function test_model_adds_builder_macros(): void
    {
        $model = new DummyModel();

        static::assertTrue($model->newQuery()->hasMacro('findRut'));
        static::assertTrue($model->newQuery()->hasMacro('findManyRut'));
        static::assertTrue($model->newQuery()->hasMacro('findRutOrFail'));
        static::assertTrue($model->newQuery()->hasMacro('findRutOrNew'));
        static::assertTrue($model->newQuery()->hasMacro('whereRut'));
        static::assertTrue($model->newQuery()->hasMacro('orWhereRut'));
    }

    public function test_model_finds_by_rut(): void
    {
        static::assertEquals(1, DummyModel::findRut($this->model->first()->rut)->getKey());
        static::assertEquals(3, DummyModel::findRut(new Rut(20490006, 'K'))->getKey());

        static::assertCount(
            2,
            DummyModel::findRut(
                [$this->model->first()->rut, $this->model->skip(1)->first()->rut]
            )
        );
    }

    public function test_exception_finds_by_rut_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::findRut(
            [$this->model->first()->rut, 'invalid-rut']
        );
    }

    public function test_model_finds_many_by_rut(): void
    {
        static::assertCount(
            2,
            DummyModel::findRut(
                [$this->model->first()->rut, $this->model->skip(1)->first()->rut]
            )
        );
    }

    public function test_model_finds_rut_or_fails(): void
    {
        static::assertInstanceOf(DummyModel::class, DummyModel::findRutOrFail($this->model->first()->rut));

        static::assertCount(
            2,
            DummyModel::findRutOrFail(
                [$this->model->first()->rut, $this->model->skip(1)->first()->rut]
            )
        );
    }

    public function test_model_finds_rut_or_fails_returns_exception_not_found(): void
    {
        do {
            $rut = Generator::makeOne();
        } while (DB::table('users')->where('rut_num', $rut->num)->exists());

        $rut = $rut->format(Format::Basic);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("No query results for model [Tests\DummyModel] $rut");

        DummyModel::findRutOrFail($rut);
    }

    public function test_exception_model_finds_rut_or_fails_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::findRutOrFail([$this->model->first()->rut, 'invalid-rut']);
    }

    public function test_find_rut_or_new(): void
    {
        static::assertEquals(1, DummyModel::findRutOrNew($this->model->first()->rut)->getKey());

        $new = DummyModel::findRutOrNew(Generator::makeOne());

        static::assertInstanceOf(DummyModel::class, $new);
        static::assertFalse($new->exists);
    }

    public function test_error_finds_rut_or_new_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::findRutOrNew('invalid-rut');
    }

    public function test_where_rut(): void
    {
        do {
            $rut = Generator::makeOne();
        } while (DB::table('users')->where('rut_num', $rut->num)->exists());

        $rut = $rut->format(Format::Basic);

        static::assertEquals(1, DummyModel::whereRut($this->model->first()->rut)->first()->getKey());
        static::assertNull(DummyModel::whereRut($rut)->first());
    }

    public function test_error_where_rut_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::whereRut('invalid-rut');
    }

    public function test_or_where_rut(): void
    {
        do {
            $rut = Generator::makeOne();
        } while (DB::table('users')->where('rut_num', $rut->num)->exists());

        $rut = $rut->format(Format::Basic);

        $query = DummyModel::where('id', 10)->orWhereRut($this->model->first()->rut);

        static::assertEquals(1, $query->first()->getKey());
        static::assertNull(DummyModel::where('id', 10)->orWhereRut($rut)->first());
    }

    public function test_error_or_where_rut_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::orWhereRut('invalid-rut');
    }
}

class DummyModel extends Model
{
    use HasRut;

    protected $table = 'users';
}