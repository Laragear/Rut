<?php

namespace Tests\Casts;

use Illuminate\Foundation\Auth\User;
use Laragear\Rut\Casts\CastRut;
use Laragear\Rut\Exceptions\EmptyRutException;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\HasRut;
use Laragear\Rut\Rut;
use Tests\PreparesDatabase;
use Tests\TestCase;

class CastsRutTest extends TestCase
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

    public function test_registers_cast(): void
    {
        static::assertEquals(
            [
                'id'  => 'int',
                'rut' => CastRut::class,
            ],
            $this->model->getCasts()
        );
    }

    public function test_casts_ruts_gets_rut(): void
    {
        $user = $this->model->first();

        static::assertInstanceOf(Rut::class, $user->rut);
        static::assertEquals($user->rut->num, $user->rut_num);
        static::assertEquals($user->rut->vd, $user->rut_vd);
    }

    public function test_casts_ruts_sets_rut(): void
    {
        $rut = Generator::makeOne();

        $this->model->make()->forceFill(
            [
                'name' => 'John',
                'email' => 'anything@cmail.com',
                'password' => '123456',
                'rut' => $rut->format(Rut::FORMAT_BASIC),
            ]
        )->save();

        $user = $this->model->find(4);

        static::assertInstanceOf(Rut::class, $user->rut);
        static::assertEquals($rut->num, $user->rut_num);
        static::assertEquals($rut->vd, $user->rut_vd);
    }

    public function test_casts_rut_as_nullable(): void
    {
        $user = $this->model->find(1);

        $user->setRawAttributes([
            'rut_num' => null,
            'rut_vd' => null,
        ]);

        static::assertNull($user->rut);
    }

    public function test_sets_rut_using_instance(): void
    {
        $user = $this->model->find(1);

        $user->rut = new Rut(0, '');

        static::assertInstanceOf(Rut::class, $user->rut);
        static::assertSame(0, $user->rut->num);
        static::assertSame('', $user->rut->vd);
    }

    public function test_gets_null_if_one_rut_column_value_is_null(): void
    {
        $user = $this->model->find(1);

        $user->setRawAttributes([
            'rut_num' => null
        ]);

        static::assertNull($user->rut);

        $user = $this->model->find(1);

        $user->setRawAttributes([
            'rut_vd' => null
        ]);

        static::assertNull($user->rut);
    }

    public function test_throws_when_setting_value_not_enough_data(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 1 given.');

        $user = $this->model->find(1);

        $user->rut = 0;
    }

    public function test_doesnt_throws_when_getting_value_not_enough_data(): void
    {
        $user = $this->model->find(1);

        $user->setRawAttributes([
            'rut_num' => 0,
            'rut_vd' => ''
        ]);

        static::assertInstanceOf(Rut::class, $user->rut);
        static::assertSame(0, $user->rut->num);
        static::assertSame('', $user->rut->vd);
    }

    public function test_sets_as_nullable(): void
    {
        $user = $this->model->find(1);

        $user->rut = null;

        static::assertNull($user->rut);
    }
}
