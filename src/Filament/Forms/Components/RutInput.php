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
     * @example "(e) => {
     *      // 1. Strip everything except numbers and 'K' to find the true length
     *      let r = String(e).toUpperCase().replace(/[^0-9K]/g, '');
     *
     *      // 2. Handle empty or single-character states
     *      if (r.length === 0) return '';
     *      if (r.length === 1) return '*'; // '*' allows a number or 'K'
     *
     *      // 3. Generate a string of '9's representing the body of the RUT
     *      let bodyMask = '9'.repeat(r.length - 1);
     *
     *      // 4. Inject literal dots into the string of '9's
     *      let formattedBodyMask = bodyMask.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
     *
     *      // 5. Append the literal hyphen and the final wildcard
     *      return formattedBodyMask + '-*';
     * }"
     *
     * @see https://unminify.com/ To unminify the script and make proper edits.
     */
    protected const JAVASCRIPT = <<<'JS'
e=>{let t=String(e).toUpperCase().replace(/[^0-9K]/g,"");return 0===t.length?"":1===t.length?"*":"9".repeat(t.length-1).replace(/\B(?=(\d{3})+(?!\d))/g,".")+"-*"};
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
