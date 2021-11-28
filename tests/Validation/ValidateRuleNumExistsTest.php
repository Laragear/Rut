<?php

namespace Tests\Validation;

use ArgumentCountError;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\Rut;
use Orchestra\Testbench\TestCase;
use Tests\PreparesDatabase;
use Tests\RegistersPackage;

class ValidateRuleNumExistsTest extends TestCase
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

    public function test_validation_rule_rut_exists(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
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
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        static::assertEquals('The rut must be a valid RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function test_validation_rule_num_exists_with_column_guessing(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->format()
        ], [
            'rut' => Rule::numExists('testing.users')
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_num_exists_with_where(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
                ->where('name', $user->name)
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_num_exists_fails_with_no_arguments(): void
    {
        $this->expectException(ArgumentCountError::class);

        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => Rut::parse($user->rut_num . $user->rut_vd)->format()
        ], [
            'rut' => Rule::numExists()
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_validation_rule_num_exists_fail_when_rut_invalid(): void
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
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_validation_rule_num_exists_fail_when_rut_doesnt_exists(): void
    {
        $user = User::inRandomOrder()->first();

        do {
            $rut = Generator::makeOne();
        } while ($rut === Rut::parse($user->rut_num . $user->rut_vd));

        $validator = Validator::make([
            'rut' => $rut->format()
        ], [
            'rut' => Rule::numExists('testing.users', 'rut_num')
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_validation_rule_num_exists_fail_when_absent_column(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->format()
        ], [
            'rut' => Rule::numExists('testing.users', 'absent_num')
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_validation_rule_num_exists_fail_when_invalid_column(): void
    {
        $user = User::inRandomOrder()->first();

        $validator = Validator::make([
            'rut' => (new Rut($user->rut_num, $user->rut_vd))->format()
        ], [
            'rut' => Rule::numExists('testing.users', 'absent_num')
        ]);

        static::assertTrue($validator->fails());
    }
}