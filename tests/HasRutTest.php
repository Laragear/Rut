<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User;
use Laragear\Rut\Exceptions\EmptyRutException;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\HasRut;
use Laragear\Rut\Rut;

class HasRutTest extends TestCase
{
    use PreparesDatabase;

    protected User $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends User {
            use HasRut;

            protected $table = 'users';
        };
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
        static::assertTrue($model->newQuery()->hasMacro('whereRutNot'));
        static::assertTrue($model->newQuery()->hasMacro('orWhereRut'));
        static::assertTrue($model->newQuery()->hasMacro('orWhereRutNot'));
    }

    public function test_model_finds_by_rut(): void
    {
        static::assertEquals(1, DummyModel::findRut(DummyModel::query()->first()->rut)->getKey());
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
        $rut = $this->randomRut()->format(Rut::FORMAT_BASIC);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("No query results for model [Tests\DummyModel] $rut");

        DummyModel::findRutOrFail($rut);
    }

    public function test_model_finds_rut_or_fails_returns_exception_not_found_on_many(): void
    {
        $rut = $this->randomRut()->format(Rut::FORMAT_BASIC);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("No query results for model [Tests\DummyModel] 20490006K, $rut");

        DummyModel::findRutOrFail(['20490006K', $rut]);
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
        $rut = $this->randomRut()->format(Rut::FORMAT_BASIC);

        static::assertEquals(1, DummyModel::whereRut($this->model->first()->rut)->first()->getKey());
        static::assertNull(DummyModel::whereRut($rut)->first());

        $result = DummyModel::whereRut([
            '20490006K',
            $this->model->first()->rut,
        ])->get();

        static::assertCount(2, $result);
        static::assertSame(1, $result->first()->getKey());
        static::assertSame(3, $result->last()->getKey());
    }

    public function test_error_where_rut_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::whereRut('invalid-rut');
    }

    public function test_or_where_rut(): void
    {
        $rut = $this->randomRut()->format(Rut::FORMAT_BASIC);

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

    public function test_where_rut_not(): void
    {
        static::assertCount(2, DummyModel::whereRutNot('20490006K')->get());
    }

    public function test_error_where_rut_not_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::whereRutNot('invalid-rut');
    }

    public function test_or_where_rut_not(): void
    {
        $result = DummyModel::where('id', 1)->orWhereRutNot('20490006K')->get();

        static::assertCount(2, $result);
        static::assertSame(1, $result->first()->getKey());
        static::assertSame(2, $result->last()->getKey());
    }

    public function test_error_or_where_rut_not_invalid_rut(): void
    {

        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::where('id', 1)->orWhereRutNot('invalid-rut')->get();
    }

    public function test_where_rut_in(): void
    {
        $rut = $this->randomRut();

        static::assertCount(1, DummyModel::whereRutIn([$rut, '20490006K'])->get());
        static::assertEmpty(DummyModel::whereRutIn([$rut, $this->randomRut()])->get());
    }

    public function test_error_where_rut_in_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::whereRutIn(['20490006K', 'invalid-rut'])->get();
    }

    public function test_or_where_rut_in(): void
    {
        $rut = $this->randomRut();

        static::assertCount(1, DummyModel::where('id', 10)->orWhereRutIn([$rut, '20490006K'])->get());
        static::assertEmpty(DummyModel::where('id', 10)->orWhereRutIn([$rut, $this->randomRut()])->get());
    }

    public function test_error_or_where_rut_in_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        static::assertCount(1, DummyModel::where('id', 10)->orWhereRutIn(['invalid-rut', '20490006K'])->get());
    }

    public function test_where_rut_not_in(): void
    {
        $rut = $this->randomRut();

        static::assertCount(2, DummyModel::whereRutNotIn([$rut, '20490006K'])->get());
        static::assertEmpty(DummyModel::whereRutNotIn([DummyModel::find(1)->rut, DummyModel::find(2)->rut, '20490006K'])->get());
    }

    public function test_error_where_rut_not_in_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::whereRutNotIn([$this->randomRut(), 'invalid-rut'])->get();
    }

    public function test_or_where_rut_not_in(): void
    {
        static::assertCount(1, DummyModel::where('id', 1)->orWhereRutNotIn([DummyModel::find(2)->rut, '20490006K'])->get());
        static::assertEmpty(DummyModel::where('id', 10)->orWhereRutNotIn([DummyModel::find(1)->rut, DummyModel::find(2)->rut, '20490006K'])->get());
    }

    public function test_error_or_where_rut_not_in_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::where('id', 1)->orWhereRutNotIn([DummyModel::find(2)->rut, 'invalid-rut'])->get();
    }
}

class DummyModel extends Model
{
    use HasRut;

    protected $table = 'users';
}