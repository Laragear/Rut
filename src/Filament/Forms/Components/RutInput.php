<?php

namespace Laragear\Rut\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

class RutInput extends TextInput
{
    /**
     * Minified JavaScript to handle RUT input.
     *
     * @const string
     *
     * @see https://unminify.com/ To unminify the script and make proper edits.
     */
    protected const JAVASCRIPT = <<<'JS'
e=>{let r=String(e).toUpperCase().replace(/[^0-9K]|(?!\d)[K](?=.*\d)/g,'').replace(/K+$/,'K');return r.length<2?0===Number(r)?'0':r:Number(r.slice(0,-1)).toLocaleString('es-CL').replace(/\./g,'.')+'-'+r.slice(-1)};
JS;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->mask(RawJs::make(static::JAVASCRIPT))
            ->minLength(7)
            ->maxLength(13)
            ->extraInputAttributes([
                'pattern' => '^[0-9]{1,3}(?:\.[0-9]{3})*-[0-9K]$',
            ])
            ->placeholder('18.765.432-1')
            ->rules('rut|between:7,13');
    }
}
