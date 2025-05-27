<?php

namespace Tests\Livewire\Synthesizers;

use Laragear\Rut\Livewire\Synthesizers\RutSynth;
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Tests\TestCase;

class RutSynthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Rut::$format = RutFormat::DEFAULT;
        RutSynth::$key = RutSynth::DEFAULT_KEY;
        RutSynth::$format = null;
    }

    public function test_uses_default_key(): void
    {
        static::assertSame(RutSynth::DEFAULT_KEY, RutSynth::getKey());
    }

    public function test_uses_custom_key(): void
    {
        static::assertSame(RutSynth::$key = 'testKey', RutSynth::getKey());
    }

    public function test_matches(): void
    {
        $rut = new Rut(14328145, 0);

        static::assertTrue(RutSynth::match($rut));

        static::assertFalse(RutSynth::match(143281450));
        static::assertFalse(RutSynth::match(14328145));
        static::assertFalse(RutSynth::match('14328145-0'));
        static::assertFalse(RutSynth::match([14328145, 0]));
    }

    public function test_hydrates_from_string_and_number(): void
    {
        $synth = new RutSynth(new ComponentContext(null), 'test');

        static::assertNull($synth->hydrate(null));
        static::assertNull($synth->hydrate(''));
        static::assertNull($synth->hydrate(0));
        static::assertNull($synth->hydrate('INVALID'));

        static::assertEquals($synth->hydrate('14328145-0'), new Rut(14328145, 0));
        static::assertEquals($synth->hydrate(143281450), new Rut(14328145, 0));
    }

    public function test_dehydrates_to_default_format(): void
    {
        Rut::$format = RutFormat::Basic;

        $synth = new RutSynth(new ComponentContext(null), 'test');

        static::assertSame([null, []], $synth->dehydrate(null));
        static::assertSame(['14328145-0', []], $synth->dehydrate(new Rut(14328145, 0)));
    }

    public function test_dehydrates_to_custom_format(): void
    {
        Rut::$format = RutFormat::DEFAULT;
        RutSynth::$format = RutFormat::Raw;

        $synth = new RutSynth(new ComponentContext(null), 'test');

        static::assertSame([null, []], $synth->dehydrate(null));
        static::assertSame(['143281450', []], $synth->dehydrate(new Rut(14328145, 0)));
    }
}
