<?php

namespace Tests\Validation;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Tests\PreparesDatabase;
use Tests\TestCase;

class ValidateRutExistsTest extends TestCase
{
    use PreparesDatabase;

    public function test_rut_exists(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format()
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_exists_with_column_guessing(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format()
        ], [
            'rut' => 'rut_exists:testing.users'
        ]);

        static::assertFalse($validator->fails());

        $validator = Validator::make([
            'rut' => $this->randomRut()->format()
        ], [
            'rut' => 'rut_exists:testing.users,rut_num'
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_exists_fails_when_doesnt_exists(): void
    {
        $validator = Validator::make([
            'rut' => $this->uniqueRut()->format(),
        ], [
            'rut' => 'rut_exists:testing.users,rut_num,rut_vd'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $validator = Validator::make([
            'rut' => $this->uniqueRut(),
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