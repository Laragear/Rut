<?php

namespace Tests\Casts;

use Illuminate\Foundation\Auth\User;
use Laragear\Rut\Casts\CastRut;
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

        $this->model = new class extends User {
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
}