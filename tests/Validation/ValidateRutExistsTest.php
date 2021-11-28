<?php

namespace Tests\Validation;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Laragear\Rut\Facades\Generator as RutGenerator;
use Laragear\Rut\Rut;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;


class ValidateRutExistsTest extends TestCase
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

    public function test_rut_exists(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_exists_with_column_guessing(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => 'rut_exists:testing.users'
        ]);

        static::assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => 'rut_exists:testing.users,rut_num'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_exists_fails_when_doesnt_exists(): void
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = RutGenerator::makeOne();
        } while ($rut === Rut::parse($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->format(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = RutGenerator::makeOne();
        } while ($rut === Rut::parse($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->format(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertEquals('The rut must be a valid RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function test_rut_exists_fails_when_its_invalid(): void
    {
        User::make()->forceFill([
            'name' => 'Alice',
            'email' => 'alice.doe@email.com',
            'password' => '123456',
            'rut_num' => 18765432,
            'rut_vd' => 1,
        ])->save();

        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }
}