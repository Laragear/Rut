<?php

namespace Tests\Validation;

use ArgumentCountError;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laragear\Rut\Rut;
use Tests\PreparesDatabase;
use Tests\TestCase;

class ValidateRuleRutExistsTest extends TestCase
{
    use PreparesDatabase;

    public function test_validation_rule_rut_exists(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_rut_exists_with_column_guessing(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format()
        ], [
            'rut' => Rule::rutExists('testing.users')
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_rut_exists_with_where(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num.$user->rut_vd)->format()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
                ->where('name', $user->name)
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_rut_exists_fails_when_no_arguments(): void
    {
        $this->expectException(ArgumentCountError::class);

        $validator = Validator::make([
            'rut' => $this->randomRut()->format()
        ], [
            'rut' => Rule::rutExists()
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_returns_message(): void
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
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        static::assertTrue($validator->fails());
        static::assertEquals('The rut must be a valid RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function test_validation_rule_rut_exists_fail_when_rut_invalid(): void
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
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_validation_rule_rut_exists_fail_when_rut_doesnt_exists(): void
    {
        $validator = Validator::make([
            'rut' => $this->uniqueRut()->format()
        ], [
            'rut' => Rule::rutExists('testing.users', 'rut_num', 'rut_vd')
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_validation_rule_rut_exists_fail_when_invalid_column(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format()
        ], [
            'rut' => Rule::rutExists('testing.users', 'absent_num', 'absent_vd')
        ]);

        static::assertTrue($validator->fails());
    }
}