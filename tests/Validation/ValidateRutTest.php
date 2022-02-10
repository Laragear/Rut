<?php

namespace Tests\Validation;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidateRutTest extends TestCase
{
    public function test_rut(): void
    {
        $validator = Validator::make([
            'rut_1' => '14328145-0',
            'rut_2' => '143281450',
            'rut_3' => '19.743.721-9',
            'rut_4' => '1974WD!37ASDASD219.',
        ], [
            'rut_1' => 'rut',
            'rut_2' => 'rut',
            'rut_3' => 'rut',
            'rut_4' => 'rut',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_in_array(): void
    {
        $validator = Validator::make([
            'rut' => ['14328145-0', '143281450', '19.743.721-9', 197437219, '1974WD!37ASDASD219.']
        ], [
            'rut' => 'rut',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_fails_on_invalid_rut(): void
    {
        $validator = Validator::make([
            'rut' => '14328145-K'
        ], [
            'rut' => 'rut'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $validator = Validator::make([
            'rut' => '14328145-K'
        ], [
            'rut' => 'rut'
        ]);

        static::assertEquals('The rut must be a valid RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function test_rut_fails_on_single_invalid_rut_array(): void
    {
        $validator = Validator::make([
            'rut' => ['14328145-0', '14328145K', '19.743.721-9', 197437219, '1974WD!37ASDASD219.']
        ], [
            'rut' => 'rut'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_rut_fails_on_all_invalid_rut_array(): void
    {
        $validator = Validator::make([
            'rut' => ['invalid', '14328145K', '18.765.432-1', '1974WD!37ASDASD219.K', '']
        ], [
            'rut' => 'rut'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_rut_fails_on_all_rut_array_with_empty_child(): void
    {
        $validator = Validator::make([
            'rut' => ['14328145-0', '143281450', '19.743.721-9', '1974WD!37ASDASD219.', '']
        ], [
            'rut' => 'rut'
        ]);

        static::assertTrue($validator->fails());
    }
}