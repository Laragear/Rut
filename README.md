![Loïc Mermilliod - Unsplash (UL) #H6KJ2D0LphU](https://images.unsplash.com/photo-1490782300182-697b80ad4293?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1280&h=400&q=80)

[![Latest Stable Version](https://poser.pugx.org/laragear/rut/v/stable)](https://packagist.org/packages/laragear/rut) [![License](https://poser.pugx.org/laragear/rut/license)](https://packagist.org/packages/laragear/rut) ![](https://img.shields.io/packagist/php-v/laragear/rut.svg) ![](https://github.com/Laragear/Rut/workflows/PHP%20Composer/badge.svg) [![Coverage Status](https://coveralls.io/repos/github/Laragear/Rut/badge.svg?branch=master)](https://coveralls.io/github/Laragear/Rut?branch=master) [![Laravel Octane Compatible](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://github.com/laravel/octane)

# Rut

Tools to parse, validate and generate Chilean RUT in Laravel.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('18.765.432-1');

if ($rut->isValid()) {
    return 'Your RUT is valid!';
}
```

## Requirements

- PHP 8.1 or later
- Laravel 8.x

## Installation

Fire up Composer and require it into your project:

```bash
composer require laragear/rut
```

## Creating a RUT

To create a RUT from an already **valid** source, instance a `Rut` object with the numbers and the verification digit, separately.

```php
use Laragear\Rut\Rut;

$rut = new Rut(5138171, 8);
```

Otherwise, you may want to use `parse()` to create it from a single string. It will try its best to create a RUT instance from what is given, or throw an `InvalidRutException` if the string doesn't have the necessary characters to create a RUT.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('5.138.171-8');
```

## Validating a RUT

You should use the included [Validation Rules](#validation-rules) to validate RUTs in your input.

Otherwise, you  can manually validate a RUT using `isValid()` or `isInvalid()` to check if it's mathematically valid or not, respectively.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('5.138.171-8');

if ($rut->isValid()) {
    return "The Rut is valid!";
}
```

Using the `validate()` method will throw a `InvalidRutException` if it's invalid.

```php
use Laragear\Rut\Rut;

Rut::parse('5.138.171-K')->validate(); // "The given RUT is invalid."
```

You can also validate RUT strings directly, or an already separated RUT, by using `check()` method.

```php
use Laragear\Rut\Rut;

Rut::check('5.138.171-8')

Rut::check(5138171, '8');
```

To differentiate between a person RUT and a company RUT, you can use `isPerson()` or `isCompany()`, respectively. The "cut" is done at 50.000.000, so is usually safe to assume a RUT like `76.543.210-K` is for a company.

```
$rut = Rut::parse('76.543.210-3');

if ($rut->isCompany()) {
    return 'If you are a company, use our B2B solution instead.';
}
```

> A RUT is considered valid if its between 99.000 and 100.000.001. Most people using 999.999 or lower RUT numbers are deceased, and 100.000.000 RUTs are still decades away.

## Generating RUTs

The package comes with a convenient RUT `Generator` facade to create thousands or millions of random RUTs using fluid methods.

The `make()` method generates a [Collection](https://laravel.com/docs/collections) of 15 `Rut` by default, but you can set any number you want. Alternatively, you can use `makeOne()` to create just one random `Rut`.

```php
use Laragear\Rut\Facades\Generator;

$ruts = Generator::make(10);

$rut = Generator::makeOne();
```

You can use `asPeople()` to make lesser RUTs numbers, or `asCompanies()` to create greater RUTs numbers.

```php
use Laragear\Rut\Facades\Generator;

$ruts = Generator::asPeople()->make(10);

$rut = Generator::asCompanies()->makeOne();
```

If you plan to create several millions of RUTs, there is a high change you will come with duplicates. To avoid collisions, use the `unique()` method in exchange for a performance hit to remove duplicates.

```php
use Laragear\Rut\Facades\Generator;

$ruts = Generator::unique()->asCompanies()->make(10000000);
```

## Validation rules

All validation rules messages are translated. You can add your own translation by publishing the files:

    php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="translations"

> Database rules will normalize the verification _digit_ as uppercase in the database for search queries.

### `rut`

This checks if the RUT being passed is a valid RUT string. This automatically **cleans the RUT** from anything except numbers and verification digit, and then checks if the resulting RUT is valid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '14328145-0'
], [
    'rut' => 'rut'
]);

echo $validator->passes(); // true

$validator = Validator::make([
    'rut' => '65.00!!!390XXXX2'
], [
    'rut' => 'rut'
]);

echo $validator->passes(); // true
```

This may come handy in situations when the user presses a wrong button into an RUT input, so there is no need to ask the user to properly format a RUT. Afterwards, you can use the [Request RUT helpers](#request-rut-helper) to retrieve the RUT from the Request input or query.

The rule also accepts an `array` of RUTs. In that case, `rut` will succeed if all the RUTs are valid. This may come in handy when a user is registering a lot of people into your application.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', 'thisisnotarut']
], [
    'rut' => 'rut'
]);

echo $validator->passes(); // false

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', '20881410-9']
], [
    'rut' => 'rut'
]);

echo $validator->passes(); // true
```

### `rut_strict`

This works the same as `rut`, but it will validate RUTs that are also using the Strict RUT format: with a thousand separator and a hyphen before the Validation Digit.

It will return `false` even if there is one misplaced character or an invalid one.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '14.328.145-0'
], [
    'rut' => 'rut_strict'
]);

echo $validator->passes(); // true

$validator = Validator::make([
    'rut' => '1.4328.145-0'
], [
    'rut' => 'rut_strict'
]);

echo $validator->passes(); // false
```

This rule also accepts an `array` of RUTs. In that case, `rut_strict` will return true if all the RUTs are properly formatted and valid.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => ['1.4328.145-0', '12.343.580-K']
], [
    'rut.*' => 'required|rut_strict',
]);

echo $validator->paases(); // false
```

### `rut_exists` (Database)

Instead of using Laravel's [exists](https://laravel.com/docs/master/validation#rule-exists), you can use `rut_exists` in case your database has separated columns for the RUT Number and Verification Digit.

For this to work you need to set the table to look for, the *RUT number* column and *RUT verification digit* column, otherwise the rule will *guess* the column names by the attribute key and appending `_num` and `_vd`, respectively.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_exists:mysql.users,rut_num,rut_vd'
]);

echo $validator->passes(); // false
```

Since this also checks if the RUT is valid (not strict), it will fail if it's not, or the RUT doesn't exist in the database.

To customize the query, you can use the `Rule` class of Laravel with the method `rutExists`. Note that you can input the number and verification digit columns, or both, if you don't want to let the rule guess them, as it may incorrectly guess when using a wildcard.
 
```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => [
        'rut_1' => '12.343.580-K',
        'rut_2' => '13.871.792-5',
    ],
], [
    'rut' => [
        'required',
        Rule::rutExists('mysql.users', 'rut_num', 'rut_vd')->where('account_id', 1),
    ]
]);

echo $validator->passes(); // true
```

### `num_exists` (Database)

This validation rule checks if only the number of the RUT exists, without taking into account the verification digit. This is handy when the Database has an index in the number of the RUT, thus making this verification blazing fast.

This rule automatically validates the RUT before doing the query.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|num_exists:mysql.users,rut_num' 
]);

echo $validator->passes(); // false
```

You can customize the underlying query using the `numExists`. 
 
```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => '12.343.580-K',
], [
    'rut' => [
        'required',
        Rule::numExists('mysql.users', 'rut_num')->where('account_id', 1),
    ]
]);

echo $validator->passes(); // false
```

### `rut_unique` (Database)

This works the same as the `rut_exists` rule, but instead of checking if the RUT exists in the Database, it will detect if it doesn't. This rule works just like the [Laravel's `unique` rule works](https://laravel.com/docs/validation#rule-unique).

This rule automatically validates the RUT before doing the query.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|rut_unique:mysql.users,rut_num,rut_vd' 
]);

echo $validator->passes(); // false
```

You can also exclude a certain ID or records from the Unique validation. For this, you need to use the `Rule` class.

```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => '12.343.580-K',
], [
    'rut' => [
        'required',
        Rule::rutUnique('mysql.users', 'rut_num')->ignore(request()->user()),
    ]
]);

echo $validator->passes(); // false
```

> **[Warning]** **You should never pass any user controlled request input into the ignore method. Instead, you should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model instance. Otherwise, your application will be vulnerable to an SQL injection attack.**

### `num_unique` (Database)

This rule will check only if the **number** of the RUT doesn't exists already in the database, which is useful for Databases with an index solely on the number of the RUT. This rule also matches the [Laravel's `unique` rule works](https://laravel.com/docs/validation#rule-unique).

This rule automatically validates the RUT before doing the query.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => '12.343.580-K'
], [
    'rut' => 'required|num_unique:mysql.users,rut_num' 
]);

echo $validator->passes(); // false
```

You can also exclude a certain ID or records from the Unique validation. For this, you need to use the `Rule` class.

```php
<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

$validator = Validator::make([
    'rut' => '12.343.580-K',
], [
    'rut' => [
        'required',
        Rule::numUnique('mysql.users')->ignore(request()->user()->id),
    ]
]);

echo $validator->passes(); // false
```

> **[Warning]** **You should never pass any user controlled request input into the ignore method. Instead, you should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model instance. Otherwise, your application will be vulnerable to an SQL injection attack.**

## Database Blueprint helper

If you're creating your database from the ground up, you don't need to manually create the RUT columns. Just use the `rut()` or `rutNullable()` helpers in the Blueprint:

```php
Schema::create('users', function (Blueprint $table) {
    $table->rut();
    
    // ...
});

Schema::create('company', function (Blueprint $table) {
    $table->rutNullable();
    
    // ...
});
```

> The `rutNullable()` method creates both Number and Verification Digit columns as nullable.

If you plan to use the Number as an index, which may speed up queries to look for RUTs, you can just index the Number column by fluently adding `primary()`, `index()` or `unique()` depending on your database needs. This is because it has more performance sense to index only the Number rather than the whole RUT.

## Request RUT helper

This package includes the `rut()` macro helper for the `Request` instance, which retrieves a single RUT from an input or query.

```php
use Illuminate\Http\Request;

public function show(Request $request)
{
    $request->validate([
        'person' => 'required|rut'
    ]);
    
    $rut = $request->rut('person');
    
    // ...
}
```

If the input is _iterable_, like an `array` or even a `Collection` instance, you will receive a `Collection` of `Rut` instances.

```php
$request->validate([
    'people'   => 'required|array',
    'people.*' => 'rut'
]);

$ruts = $request->rut('people');
```

You can also retrieve multiple keys from the Request, which will also return a `Collection`.

```php
$request->validate([
    'mom'        => 'required|rut',
    'dad'        => 'required|rut',
    'children'   => 'required|array'
    'children.*' => 'required|rut'
]);

$parents = $request->rut(['mom', 'dad']);
$children = $request->rut('children');
```

> It's imperative you validate your input before retrieving RUTs. If there is a malformed RUT, an exception will be thrown.

## RUT traits for Eloquent Models

This package contains the `HasRut` trait to use in Laravel Eloquent Models with tables that have separate RUT Number and RUT Verification digit. 

This trait conveniently adds a RUT Scope to a model that has a RUT in its columns, and the `rut` property which returns a `Rut` instance.

```php
<?php

namespace App\Models;

use Laragear\Rut\HasRut;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRut;
    
    // ...
}
```

With that, you will have access to convenient RUT queries shorthands:

* `findRut()`: Finds a record by the given RUT.
* `findManyRut()`: Finds many records by the given RUTs.
* `findRutOrFail()`: Finds a record by the RUT or fails.
* `findRutOrNew()`: Finds a record by the RUT or creates one.
* `whereRut()`: Creates a `WHERE` clause with the RUT number equal to the issued one.
* `orWhereRut()`: Creates a `OR WHERE` clause with the RUT number equal to the issued one.

> These RUT queries work over the RUT Number for convenience, as the RUT Verification Digit should be verified on persistence.

The `rut` property is dynamically created from the RUT Number and RUT Verification Digit columns, which uses a [Cast](https://laravel.com/docs/eloquent-mutators#attribute-casting) underneath.

```php
echo $user->rut; // "20490006-K"
```

#### Setting the RUT columns

By convention, the trait uses `rut_num` and `rut_vd` as the default columns to retrieve and save the RUT Number and RUT Verification Digit, respectively.

You can easily change it to anything your database is working with:

```php
class User extends Authenticatable
{
    use HasRut;
    
    protected const RUT_NUM = 'numero_rut';
    protected const RUT_VD = 'digito_rut';
    
    // ...    
}
```

## Configuration

This package works flawlessly out of the box, but you may want to change how a `Rut` is formatted as a string using the global configuration. You can publish it using Artisan:

    php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="config"

You will receive a config file like this:

```php
use Laragear\Rut\Format;

return [
    'format' => Format::DEFAULT,
    'uppercase' => true,
];
```

### Formatting a RUT

```php
use Laragear\Rut\Format;

return [
    'format' => Format::DEFAULT,
];
```

By default, a RUT is strictly formatted. This config alters how RUTs are formatted as string in your application.

| Formatting | Example | Description
|---|---|---|
| Strict    | `5.138.171-8` | Default option. Serializes with a thousand separator and hyphen.
| Basic     | `5138171-8`   | No thousand separator, only the hyphen.
| Raw       | `51381718`    | No thousand separator nor hyphen.

You can use `format()` to format the RUT using the default, or using a given Formatting option overriding the global configuration.

```php
use Laragear\Rut\Format;
use Laragear\Rut\Rut;

$rut = Rut::parse('5.138.171-8');

$rut->format(); // "5.138.171-8"
$rut->format(Format::Strict); // "5.138.171-8"
$rut->format(Format::Basic); // "5138171-8"
$rut->format(Format::Raw); // "51381718"
```

For the case of JSON, you can set a custom callback

### Verification Digit Case

```php
return [
    'uppercase' => true,
];
```

Since the Verification Digit can be `K`, it's usually good idea to work with uppercase or lowercase across all the application.

The `Rut` instance by default will use uppercase `K`, but you can change it globally using the `Rut::$uppercase` static property. This will affect all `Rut` instance, and once set, it cannot be changed.

```php
use Laragear\Rut\Format;
use Laragear\Rut\Rut;

Rut::$uppercase = false;

$rut = Rut::parse('12351839-K');

$rut->format(); // "12.351.839-k"
```

## License

This package is licenced by the [MIT License](LICENSE).
