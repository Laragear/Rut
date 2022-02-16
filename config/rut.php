<?php

use Laragear\Rut\Rut;

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

    'format' => Rut::FORMAT_STRICT,

    /*
    |--------------------------------------------------------------------------
    | Default JSON format
    |--------------------------------------------------------------------------
    |
    | By default, a RUT is cast to a JSON string using the same global format.
    | can change the style here, which will affect only JSON strings. If the
    | value of `json_format` is `null` it will mirror the `format` config.
    |
    */

    'json_format' => null,

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
];
