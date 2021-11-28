<?php

namespace Tests\Validation;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\Rut;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;


class ValidateRutUniqueTest extends TestCase
{
    use RegistersPackage,
        PreparesDatabase;

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->prepareDatabase();
        });

        parent::setUp();
    }

    public function test_unique(): void
    {
        do {
            $rut = Generator::makeOne();
        } while (User::where('rut_num', $rut->num)->exists());

        $validator = Validator::make([
            'rut' => $rut->format(),
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_unique_with_column_guessing(): void
    {
        do {
            $rut = Generator::makeOne();
        } while (User::where(['rut_num', $rut->num, 'rut_vd', $rut->vd])->exists());

        $validator = Validator::make([
            'rut' => $rut->format(),
        ], [
            'rut' => 'rut_unique:testing.users'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_unique_fails_when_not_unique(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        static::assertEquals('The rut has already been taken.', $validator->getMessageBag()->first('rut'));
    }

    public function test_unique_fails_when_invalid_rut(): void
    {
        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }

}