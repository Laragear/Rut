<?php

namespace Tests\Validation;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Laragear\Rut\RutFormat;
use Tests\PreparesDatabase;
use Tests\TestCase;

class ValidateNumExistsTest extends TestCase
{
    use PreparesDatabase;

    public function test_num_exists(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => 'num_exists:testing.users',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_returns_message(): void
    {
        $validator = Validator::make([
            'rut' => 'invalid_rut',
        ], [
            'rut' => 'num_exists:testing.users',
        ]);

        static::assertEquals('The rut must be a valid RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function test_num_exists_with_column_guessing(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => 'num_exists:testing.users',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_num_exists_fails_when_doesnt_exists(): void
    {
        $validator = Validator::make([
            'rut' => $this->uniqueRut()->format(RutFormat::Strict),
        ], [
            'rut' => 'num_exists:testing.users,rut_num',
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
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'num_exists:testing.users,rut_num',
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_num_exists_fails_when_invalid_column(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => 'num_exists:testing.users,invalid_column',
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_num_exists_fails_when_absent_one_parameters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => 'num_exists',
        ]);

        static::assertTrue($validator->fails());
    }
}
