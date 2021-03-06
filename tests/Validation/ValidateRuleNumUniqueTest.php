<?php

namespace Tests\Validation;

use ArgumentCountError;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laragear\Rut\Rut;
use Tests\PreparesDatabase;
use Tests\TestCase;

class ValidateRuleNumUniqueTest extends TestCase
{
    use PreparesDatabase;

    public function test_validation_rule_num_unique(): void
    {
        $validator = Validator::make([
            'rut' => $this->uniqueRut()->format(),
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num'),
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_num_unique_ignoring_id(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num.$user->rut_vd)->format(),
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignore($user->getKey()),
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_num_unique_ignoring_model(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num.$user->rut_vd)->format(),
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->ignoreModel($user),
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_num_unique_where(): void
    {
        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', 'Anything that is not John'),
        ]);

        static::assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num.$user->rut_vd)->format(),
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', $user->name),
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num.$user->rut_vd)->format(),
        ], [
            'rut' => Rule::numUnique('testing.users', 'rut_num')
                ->where('name', $user->name),
        ]);

        static::assertEquals('The rut has already been taken.', $validator->getMessageBag()->first('rut'));
    }

    public function test_validation_rule_num_unique_fails_when_no_arguments(): void
    {
        $this->expectException(ArgumentCountError::class);

        $validator = Validator::make([
            'rut' => $this->randomRut()->format(),
        ], [
            'rut' => Rule::numUnique(),
        ]);

        static::assertFalse($validator->fails());
    }
}
