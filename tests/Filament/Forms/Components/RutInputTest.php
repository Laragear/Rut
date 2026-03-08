<?php

namespace Tests\Filament\Forms\Components;

use Laragear\Rut\Filament\Forms\Components\RutInput;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Filament\TestCase;

class RutInputTest extends TestCase
{
    public function test_has_input_configuration(): void
    {
        $input = (new RutInput('test'))->configure();

        static::assertSame(7, $input->getMinLength());
        static::assertSame(13, $input->getMaxLength());
        static::assertSame('18.765.432-1', $input->getPlaceholder());
        static::assertContains('rut', $input->getValidationRules());
        static::assertContains('between:7,13', $input->getValidationRules());
        static::assertContains('max:13', $input->getValidationRules());
        static::assertContains('min:7', $input->getValidationRules());
    }

    public function test_input_pattern_valid(): void
    {
        $pattern = (new RutInput('test'))->configure()->getExtraInputAttributeBag()->get('pattern');

        static::assertMatchesRegularExpression("/$pattern/", '111.111.111-5');
        static::assertMatchesRegularExpression("/$pattern/", '111-5');
        static::assertMatchesRegularExpression("/$pattern/", '111.111-5');
    }

    public static function providesInvalidStrings(): array
    {
        return [
            ['invalid'],
            ['11.11.11-5'],
            ['123123123-5'],
            ['1231231235'],
            ['123,123,123-5'],
            ['123123.123-5'],
            ['123.123123-5'],
            ['123.1231235'],
        ];
    }

    #[DataProvider('providesInvalidStrings')]
    public function test_input_pattern(string $invalid): void
    {
        $pattern = (new RutInput('test'))->configure()->getExtraInputAttributeBag()->get('pattern');

        static::assertDoesNotMatchRegularExpression("/$pattern/", $invalid);
    }
}
