<?php

namespace Tests\Validation;

use Illuminate\Support\Facades\Validator;
use Tests\PreparesDatabase;
use Tests\TestCase;

class ValidateRutUniqueTest extends TestCase
{
    use PreparesDatabase;

    public function test_unique(): void
    {
        $validator = Validator::make([
            'rut' => $this->uniqueRut()->format(),
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_unique_with_column_guessing(): void
    {
        $validator = Validator::make([
            'rut' => $this->uniqueRut()->format(),
        ], [
            'rut' => 'rut_unique:testing.users',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_unique_fails_when_not_unique(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd',
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd',
        ]);

        static::assertEquals('The rut has already been taken.', $validator->getMessageBag()->first('rut'));
    }

    public function test_unique_fails_when_invalid_rut(): void
    {
        $validator = Validator::make([
            'rut' => '18.765.432-1',
        ], [
            'rut' => 'rut_unique:testing.users,rut_num,rut_vd',
        ]);

        static::assertTrue($validator->fails());
    }
}
