<?php

namespace Tests\Validation;

use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use Tests\RegistersPackage;

class ValidateRutStrictTest extends TestCase
{
    use RegistersPackage;

    public function test_rut_strict(): void
    {
        $validator = Validator::make([
            'rut_1' => '14.328.145-0',
            'rut_2' => '19.743.721-9',
        ], [
            'rut_1' => 'rut_strict',
            'rut_2' => 'rut_strict',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_strict_in_array(): void
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9']
        ], [
            'rut' => 'rut_strict',
        ]);

        static::assertFalse($validator->fails());
    }

    public function test_rut_strict_fails_on_invalid_format(): void
    {
        $validator = Validator::make([
            'rut' => '14328145-0'
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_returns_message(): void
    {
        $validator = Validator::make([
            'rut' => '14328145-0'
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertEquals('The rut must be a properly formatted RUT.', $validator->getMessageBag()->first('rut'));
    }

    public function test_rut_fails_on_invalid_rut(): void
    {
        $validator = Validator::make([
            'rut' => '14.328.145-K'
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_rut_strict_fails_on_single_invalid_format_rut_array(): void
    {
        $validator = Validator::make([
            'rut' => ['14328145-K', '14.328.145-0', '19.743.721-9']
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_rut_strict_fails_on_all_invalid_format_rut_array(): void
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9', '19743721-9']
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }

    public function test_rut_fails_on_all_rut_array_with_empty_child(): void
    {
        $validator = Validator::make([
            'rut' => ['14.328.145-0', '19.743.721-9', '']
        ], [
            'rut' => 'rut_strict'
        ]);

        static::assertTrue($validator->fails());
    }
}