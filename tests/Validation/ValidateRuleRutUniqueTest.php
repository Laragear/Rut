<?php

namespace Tests\Validation;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\Rut;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;


class ValidateRuleRutUniqueTest extends TestCase
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

    public function test_validation_rule_rut_unique(): void
    {
        do {
            $rut = Generator::makeOne();
        } while (User::where('rut_num', $rut->num)->exists());

        $validator = Validator::make([
            'rut' => $rut->format()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_rut_unique_ignoring_id(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($user->getKey())
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_rut_unique_ignoring_model(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignoreModel($user)
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_rut_unique_ignoring_model_in_ignore_method(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->ignore($user)
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_rut_unique_where(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', 'Anything that is not John')
        ]);

        static::assertFalse($validator->fails());

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', $user->name)
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::rutUnique('testing.users', 'rut_num', 'rut_vd')
                ->where('name', $user->name)
        ]);

        static::assertEquals('The rut has already been taken.', $validator->getMessageBag()->first('rut'));
    }
}