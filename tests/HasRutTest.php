<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User;
use Laragear\Rut\Exceptions\EmptyRutException;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\HasRut;
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;

class HasRutTest extends TestCase
{
    use PreparesDatabase;

    protected User $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends User
        {
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
        static::assertTrue($model->newQuery()->hasMacro('whereRutIsPerson'));
        static::assertTrue($model->newQuery()->hasMacro('orWhereRutIsPerson'));
        static::assertTrue($model->newQuery()->hasMacro('whereRutIsCompany'));
        static::assertTrue($model->newQuery()->hasMacro('orWhereRutIsCompany'));
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
        $rut = $this->uniqueRut()->format(RutFormat::Basic);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("No query results for model [Tests\DummyModel] $rut");

        DummyModel::findRutOrFail($rut);
    }

    public function test_model_finds_rut_or_fails_returns_exception_not_found_on_many(): void
    {
        $rut = $this->uniqueRut()->format(RutFormat::Basic);

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
        $rut = $this->uniqueRut()->format(RutFormat::Basic);

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
        $rut = $this->uniqueRut()->format(RutFormat::Basic);

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
        $rut = $this->uniqueRut();

        static::assertCount(1, DummyModel::whereRutIn([$rut, '20490006K'])->get());
        static::assertEmpty(DummyModel::whereRutIn([$rut, $this->uniqueRut()])->get());
    }

    public function test_error_where_rut_in_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::whereRutIn(['20490006K', 'invalid-rut'])->get();
    }

    public function test_or_where_rut_in(): void
    {
        static::assertCount(1, DummyModel::where('id', 10)->orWhereRutIn([$this->uniqueRut(), '20490006K'])->get());
        static::assertEmpty(DummyModel::where('id', 10)->orWhereRutIn([$this->uniqueRut(), $this->uniqueRut()])->get());
    }

    public function test_error_or_where_rut_in_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        static::assertCount(1, DummyModel::where('id', 10)->orWhereRutIn(['invalid-rut', '20490006K'])->get());
    }

    public function test_where_rut_not_in(): void
    {
        static::assertCount(2, DummyModel::whereRutNotIn([$this->uniqueRut(), '20490006K'])->get());
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

    public function test_where_rut_is_person(): void
    {
        static::assertCount(3, DummyModel::whereRutIsPerson()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::INVESTOR_BASE - 1),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(4, DummyModel::whereRutIsPerson()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::INVESTOR_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(4, DummyModel::whereRutIsPerson()->get());
    }

    public function test_or_where_rut_is_person(): void
    {
        $rut = Rut::fromNum(Rut::INVESTOR_BASE);

        static::assertCount(3, DummyModel::whereRut($rut)->orWhereRutIsPerson()->get());

        DummyModel::make()->forceFill([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(4, DummyModel::whereRut($rut)->orWhereRutIsPerson()->get());
    }

    public function test_where_rut_is_investor(): void
    {
        static::assertEmpty(DummyModel::whereRutIsInvestor()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::INVESTOR_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsInvestor()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::INVESTMENT_COMPANY_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsInvestor()->get());
    }

    public function test_or_where_rut_is_investor(): void
    {
        $rut = Rut::fromNum(Rut::INVESTOR_BASE);

        static::assertCount(1, DummyModel::whereKey(1)->orWhereRutIsInvestor()->get());

        DummyModel::make()->forceFill([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(2, DummyModel::whereKey(1)->orWhereRutIsInvestor()->get());
    }

    public function test_where_rut_is_investment_company(): void
    {
        static::assertEmpty(DummyModel::whereRutIsInvestmentCompany()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::INVESTMENT_COMPANY_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsInvestmentCompany()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::CONTINGENCY_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsInvestmentCompany()->get());
    }

    public function test_or_where_rut_is_investment_company(): void
    {
        $rut = Rut::fromNum(Rut::INVESTMENT_COMPANY_BASE);

        static::assertCount(1, DummyModel::whereKey(1)->orWhereRutIsInvestmentCompany()->get());

        DummyModel::make()->forceFill([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(2, DummyModel::whereKey(1)->orWhereRutIsInvestmentCompany()->get());
    }

    public function test_where_rut_is_contingency(): void
    {
        static::assertEmpty(DummyModel::whereRutIsContingency()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::CONTINGENCY_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsContingency()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::COMPANY_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsContingency()->get());
    }

    public function test_or_where_rut_is_contingency(): void
    {
        $rut = Rut::fromNum(Rut::CONTINGENCY_BASE);

        static::assertCount(1, DummyModel::whereKey(1)->orWhereRutIsContingency()->get());

        DummyModel::make()->forceFill([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(2, DummyModel::whereKey(1)->orWhereRutIsContingency()->get());
    }

    public function test_where_rut_is_company(): void
    {
        static::assertEmpty(DummyModel::whereRutIsCompany()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::COMPANY_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsCompany()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::TEMPORAL_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsCompany()->get());
    }

    public function test_or_where_rut_is_company(): void
    {
        $rut = Rut::fromNum(Rut::COMPANY_BASE);

        static::assertCount(1, DummyModel::whereKey(1)->orWhereRutIsCompany()->get());

        DummyModel::make()->forceFill([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(2, DummyModel::whereKey(1)->orWhereRutIsCompany()->get());
    }

    public function test_where_rut_is_temporal(): void
    {
        static::assertEmpty(DummyModel::whereRutIsTemporal()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::TEMPORAL_BASE),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsTemporal()->get());

        DummyModel::make()->forceFill([
            'name' => $rut = Rut::fromNum(Rut::MAX + 1),
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(1, DummyModel::whereRutIsTemporal()->get());
    }

    public function test_or_where_rut_is_temporal(): void
    {
        $rut = Rut::fromNum(Rut::TEMPORAL_BASE);

        static::assertCount(1, DummyModel::whereKey(1)->orWhereRutIsTemporal()->get());

        DummyModel::make()->forceFill([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ])->save();

        static::assertCount(2, DummyModel::whereKey(1)->orWhereRutIsTemporal()->get());
    }

    public function test_where_rut_like(): void
    {
        $rut = '27451610-0';

        static::assertCount(0, DummyModel::whereRutLike('745161')->get());

        DummyModel::forceCreate([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ]);

        static::assertCount(1, DummyModel::whereRutLike('745161')->get());
    }

    public function test_or_where_rut_like(): void
    {
        $rut = '27451610-0';

        static::assertCount(1, DummyModel::whereKey(1)->orWhereRutLike('745161')->get());

        DummyModel::forceCreate([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ]);

        static::assertCount(2, DummyModel::whereKey(1)->orWhereRutLike('745161')->get());
    }

    public function test_where_rut_not_like(): void
    {
        $rut = '27451610-0';

        static::assertCount(3, DummyModel::whereRutNotLike('745161')->get());

        DummyModel::forceCreate([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ]);

        static::assertCount(3, DummyModel::whereRutNotLike('745161')->get());
    }

    public function test_or_where_rut_not_like(): void
    {
        $rut = '27451610-0';

        static::assertCount(3, DummyModel::whereKey(1)->orWhereRutNotLike('745161')->get());

        DummyModel::forceCreate([
            'name' => $rut,
            'email' => "$rut@email.com",
            'password' => '123456',
            'rut' => $rut,
        ]);

        static::assertCount(3, DummyModel::whereKey(1)->orWhereRutNotLike('745161')->get());
    }

    public function test_error_or_where_rut_not_in_invalid_rut(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        DummyModel::where('id', 1)->orWhereRutNotIn([DummyModel::find(2)->rut, 'invalid-rut'])->get();
    }

    public function test_appends_rut_and_hides_columns_if_enabled(): void
    {
        $model = DummyModelAppendingRut::make()->forceFill(['rut' => $rut = Generator::makeOne()]);

        static::assertArrayHasKey('rut', $model->toArray());
        static::assertArrayNotHasKey('rut_num', $model->toArray());
        static::assertArrayNotHasKey('rut_vd', $model->toArray());

        static::assertEquals($rut, $model->toArray()['rut']);
    }

    public function test_shows_primary_key_when_rut_num_is_primary_key(): void
    {
        $model = DummyModelWithPrimaryKeyAsRutNum::make()->forceFill(['rut' => $rut = Generator::makeOne()]);

        static::assertArrayHasKey('rut', $model->toArray());
        static::assertArrayNotHasKey('rut_num', $model->toArray());
        static::assertArrayNotHasKey('rut_vd', $model->toArray());

        static::assertEquals($rut->num, $model->toArray()['id']);
    }

    public function test_hides_primary_key_when_rut_num_is_primary_key(): void
    {
        $model = DummyModelWithPrimaryKeyAsRutNumAndId::make()->forceFill(['rut' => $rut = Generator::makeOne()]);

        static::assertArrayHasKey('rut', $model->toArray());
        static::assertArrayNotHasKey('rut_num', $model->toArray());
        static::assertArrayNotHasKey('rut_vd', $model->toArray());
        static::assertArrayNotHasKey('id', $model->toArray());

        static::assertEquals($rut, $model->toArray()['rut']);
    }
}

class DummyModel extends Model
{
    use HasRut;

    protected $table = 'users';
}

class DummyModelAppendingRut extends Model
{
    use HasRut;

    protected $table = 'users';

    public function shouldAppendRut(): bool
    {
        return true;
    }
}

class DummyModelWithPrimaryKeyAsRutNum extends Model
{
    use HasRut;

    protected const RUT_NUM = 'id';

    protected $table = 'users';
}

class DummyModelWithPrimaryKeyAsRutNumAndId extends Model
{
    use HasRut;

    protected const RUT_NUM = 'id';

    protected $table = 'users';

    public function shouldShowPrimaryKeyIfIsRutNum(): bool
    {
        return false;
    }
}
