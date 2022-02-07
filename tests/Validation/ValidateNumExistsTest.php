<?php

namespace Tests\Validation;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\Format;
use Laragear\Rut\Rut;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class ValidateNumExistsTest extends TestCase
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

    public function test_num_exists(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_returns_message(): void
    {
        $validator = Validator::make([
            'rut' => 'invalid_rut'
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        static::assertEquals('The rut must be a valid RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function test_num_exists_with_column_guessing(): void
    {
        $user = User::query()->inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->format()
        ], [
            'rut' => 'num_exists:testing.users'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_num_exists_fails_when_doesnt_exists(): void
    {
        $user = User::query()->inRandomOrder()->first();

        do {
            $rut = Generator::makeOne();
        } while ($rut === Rut::parse($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->format(Rut::FORMAT_STRICT)
        ], [
            'rut' => 'num_exists:testing.users,rut_num'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_num_exists_fails_when_invalid_rut(): void
    {
        User::make()->forceFill([
            'name' => 'Alice',
            'email' => 'alice.doe@email.com',
            'password' => '123456',
            'rut_num' => 18765432,
            'rut_vd' => 1,
        ])->save();

        $validator = Validator::make([
            'rut' => '18.765.432-1'
        ], [
            'rut' => 'num_exists:testing.users,rut_num'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_num_exists_fails_when_invalid_column(): void
    {
        $user = User::query()->inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->format()
        ], [
            'rut' => 'num_exists:testing.users,invalid_column'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_num_exists_fails_when_absent_one_parameters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $user = User::query()->inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => 'num_exists'
        ]);

        static::assertTrue($validator->fails());
    }
}