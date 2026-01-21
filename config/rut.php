<?php

use Laragear\Rut\RutFormat;

return [

    /*
    |--------------------------------------------------------------------------
    | Default format
    |--------------------------------------------------------------------------
    |
    | When a RUT is cast to a string, it will be formatted using a given style.
    | The default style is "Strict", which is fine to present to the user, but
    | you can change it to use the string with Javascript frameworks or else.
    |
    */

    'format' => RutFormat::Strict,

    /*
    |--------------------------------------------------------------------------
    | Default JSON format
    |--------------------------------------------------------------------------
    |
    | By default, a RUT is cast to a JSON string using the same global format.
    | can change the style here, which will affect only JSON strings. If the
    | value of `json_format` is `null` it will mirror the `format` config.
    |
    | Alternatively, you can set a custom Closure in `Rut::$jsonFormat`.
    |
    */

    'json_format' => null,

    /*
    |--------------------------------------------------------------------------
    | Blacklist Dummy Ruts
    |--------------------------------------------------------------------------
    |
    |
    | To avoid dummy RUTs being used in production environments, you can enable
    | a "blacklist". This blacklist will take effect when using the validation
    | rules like "num_unique" or "rut", not the RUT validation itself.
    |
    */

    'blacklist_dummy_ruts' => false, // env('APP_ENV') === 'production'

    /*
    |--------------------------------------------------------------------------
    | Verification Digit case
    |--------------------------------------------------------------------------
    |
    | A Verification Digit sometimes can be the letter `K`. By default, this is
    | handled as uppercase at all times. Some databases may be already using
    | lowercase. This config allows changing this character to lowercase.
    |
    */

    'uppercase' => true,

    /*
    |--------------------------------------------------------------------------
    | Livewire Synthesizer
    |--------------------------------------------------------------------------
    |
    | When using Livewire, this library will register a Synthesizer to handle
    | serialization of Rut instances back-and-forth the frontend, otherwise
    | it won't work. You may disable it if you want to register your own.
    |
    | @see https://livewire.laravel.com/docs/synthesizers
    |
    */

    'synthesizer' => true,
];
