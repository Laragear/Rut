# Rut
[![Latest Version on Packagist](https://img.shields.io/packagist/v/laragear/rut.svg)](https://packagist.org/packages/laragear/rut)
[![Latest stable test run](https://github.com/Laragear/Rut/workflows/Tests/badge.svg)](https://github.com/Laragear/Rut/actions)
[![Codecov coverage](https://codecov.io/gh/Laragear/Rut/branch/1.x/graph/badge.svg?token=5COE8X0JMJ)](https://codecov.io/gh/Laragear/Rut)
[![Maintainability](https://api.codeclimate.com/v1/badges/677b55bbf19bda17e0f5/maintainability)](https://codeclimate.com/github/Laragear/Rut/maintainability)
[![Sonarcloud Status](https://sonarcloud.io/api/project_badges/measure?project=Laragear_Rut&metric=alert_status)](https://sonarcloud.io/dashboard?id=Laragear_Rut)
[![Laravel Octane Compatibility](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://laravel.com/docs/11.x/octane#introduction)

Tools to parse, validate and generate Chilean RUT in Laravel.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('18.765.432-1');

if ($rut->isValid()) {
    return 'Your RUT is valid!';
}
```

## Keep this package free

[![](.github/assets/support.png)](https://github.com/sponsors/DarkGhostHunter)

Your support allows me to keep this package free, up-to-date and maintainable. Alternatively, you can **[spread the word!](http://twitter.com/share?text=I%20am%20using%20this%20cool%20PHP%20package&url=https://github.com%2FLaragear%2FRut&hashtags=PHP,Laravel,Chile)**

## Requirements

- Laravel 10 or later

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

## RUT Types

Officially, there are six types of RUT. To differentiate between them, you have access to `is...()` methods.

| RUT Type                 | From          | To            | Type Check            |
|--------------------------|---------------|---------------|-----------------------|
| Person                   | `    100.000` | ` 45.999.999` | `isPerson()`          |
| Foreign Investor Person  | ` 46.000.000` | ` 47.999.999` | `isInvestor()`        |
| Foreign Investor Company | ` 47.000.000` | ` 47.999.999` | `isInvestorCompany()` |
| Contingency              | ` 48.000.000` | ` 59.999.999` | `isContingency()`     |
| Company                  | ` 60.000.000` | ` 99.999.999` | `isCompany()`         |
| Temporal                 | `100.000.000` | `199.999.999` | `isTemporal()`        |

Additionally, you have access to the `isPermanent()` method, which checks if the RUT is below 100.000.000.

```php
use Laragear\Rut\Rut;

Rut::parse('76.482.465-2')->isPermanent(); // "true"

Rut::parse('76.482.465-2')->isTemporal(); // "false"
```

> [!IMPORTANT]
> 
> This package considers RUT as valid when between 100.000 and 200.000.000, inclusive. Most (if not all) people using 99.999 or lower RUT numbers are deceased.

## Generating RUTs

The package comes with a convenient RUT `Generator` facade to create thousands or millions of random RUTs using fluid methods.

The `make()` method generates a [Collection](https://laravel.com/docs/collections) of 15 `Rut` by default, but you can set any number you want. Alternatively, you can use `makeOne()` to create just one random `Rut`.

```php
use Laragear\Rut\Facades\Generator;

$ruts = Generator::make(10);

$rut = Generator::makeOne();
```

You can use `as...()` to make a given type of RUTs. 

```php
use Laragear\Rut\Facades\Generator;

$people = Generator::asPeople()->make(10);

$companies = Generator::asCompanies()->make(10);

$temporal = Generator::asTemporal()->makeOne();
```

If you plan to create several millions of RUTs, there is a high change you will come with duplicates. To avoid collisions, use the `unique()` method in exchange for a small performance hit to remove duplicates.

```php
use Laragear\Rut\Facades\Generator;

$ruts = Generator::unique()->asCompanies()->make(10000000);
```

## Serialization

By default, all `Rut` instances are serialized into text using a _strict_ format. You can serialize a `Rut` instance differently using one of the three formats available:

| Formatting | Enum                | Example       | Description                                                      |
|------------|---------------------|---------------|------------------------------------------------------------------|
| Strict     | `RutFormat::Strict` | `5.138.171-8` | Default option. Serializes with a thousand separator and hyphen. |
| Basic      | `RutFormat::Basic`  | `5138171-8`   | No thousand separator, only the hyphen.                          |
| Raw        | `RutFormat::Raw`    | `51381718`    | No thousand separator nor hyphen.                                |

You can use `format()` with any `RutFormat` enum as argument to serialize the RUT into text.

```php
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;

$rut = Rut::parse('5.138.171-8');

$rut->format();                  // "5.138.171-8"
$rut->format(RutFormat::Strict); // "5.138.171-8"
$rut->format(RutFormat::Basic);  // "5138171-8"
$rut->format(RutFormat::Raw);    // "51381718"
```

You may [change this globally in the configuration](#default-rut-format).

## Validating a RUT

You should use the included [Validation Rules](#validation-rules) to validate RUTs in your input.

Otherwise, you can manually validate a RUT using `isValid()` or `isInvalid()` to check if it's mathematically valid or not, respectively.

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

Rut::parse('5.138.171-K')->validate(); // InvalidRutException: "The given RUT is invalid."
```

You can also validate RUT strings directly, or an already separated RUT, by using `check()` method.

```php
use Laragear\Rut\Rut;

if (Rut::check('5.138.171-8')) {
    return "This RUT is valid!";
}

if (Rut::check(5138171, '8')) {
    return "This RUT is also valid!";
}
```

## Validation rules

All validation rules messages can be translated. You can add your own translation to these rules by publishing the translation files:

```shell
php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="translations"
```

### `rut` rule

This checks if the RUT being passed is a valid RUT string. This automatically **cleans the RUT** from anything except numbers and the verification digit. Only then it checks if the resulting RUT is mathematically valid.

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

### `rut_strict` rule

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

### `rut_exists` database rule

Instead of using Laravel's [exists](https://laravel.com/docs/master/validation#rule-exists), you can use `rut_exists` in case your database has separated columns for the RUT Number and Verification Digit.

For this to work you need to set the table to look for, the *RUT number* column and *RUT verification digit* column, otherwise the rule will *guess* the column names by the attribute key and appending `_num` and `_vd`, respectively.

This rule automatically validates the RUT before doing the query.

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

> [!TIP]
>
> Database rules will normalize the verification _digit_ as uppercase in the database for search queries.

### `num_exists` database rule

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

### `rut_unique` database rule

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

> [!TIP]
>
> Database rules will normalize the verification _digit_ as uppercase in the database for search queries.

> [!CAUTION]
> 
> **You should never pass any user controlled request input into the ignore method. Instead, you should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model instance. Otherwise, your application will be vulnerable to an SQL injection attack.**

### `num_unique` database rule

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

> [!TIP]
> 
> Database rules will normalize the verification _digit_ in the database for search queries.

> [!CAUTION]
>
> **You should never pass any user controlled request input into the ignore method. Instead, you should only pass a system generated unique ID such as an auto-incrementing ID or UUID from an Eloquent model instance. Otherwise, your application will be vulnerable to an SQL injection attack.**

## Database Blueprint helper

If you're creating your database from the ground up, you don't need to manually create the RUT columns. Just use the `rut()` or `rutNullable()` helpers in the Blueprint:

```php
Schema::create('users', function (Blueprint $table) {
    // $table->unsignedInteger('rut_num');
    // $table->char('rut_vd', 1);
    
    $table->rut();
    
    // ...
});

Schema::create('company', function (Blueprint $table) {
    // $table->unsignedInteger('rut_num')->nullable();
    // $table->char('rut_vd', 1)->nullable();
    
    $table->rutNullable();
    
    // ...
});
```

> [!TIP]
> 
> The `rutNullable()` method creates **both** Number and Verification Digit columns as nullable.

If you plan to use the RUT Number as an index, which may speed up queries to look for RUTs, you can just index the Number column by fluently adding `primary()`, `index()` or `unique()` depending on your database needs. This is because it has more performance sense to index only the Number rather than the whole RUT.

```php
Schema::create('users', function (Blueprint $table) {
    // $table->unsignedInteger('rut_num')->primary();
    // $table->char('rut_vd', 1);

    $table->rut()->primary();
    
    // ...
});
```

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
    'children.*' => 'required|rut',
]);

$parents = $request->rut('mom', 'dad'); // Or $request->rut(['mom', 'dad']);
$children = $request->rut('children');
```

> [!IMPORTANT]
>
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

| Method name         | Description                                                              |
|---------------------|--------------------------------------------------------------------------|
| `findRut()`         | Finds a record by the given RUT.                                         |
| `findManyRut()`     | Finds many records by the given RUTs.                                    |
| `findRutOrFail()`   | Finds a record by the RUT or fails.                                      |
| `findRutOrNew()`    | Finds a record by the RUT or creates one.                                |
| `whereRut()`        | Creates a `WHERE` clause with the RUT number equal to the issued one.    |
| `whereRutNot()`     | Creates a `WHERE` clause excluding the given RUT.                        |
| `orWhereRut()`      | Creates a `OR WHERE` clause with the RUT number equal to the issued one. |
| `orWhereRutNot()`   | Creates a `OR WHERE` clause excluding the given RUT.                     |
| `whereRutIn()`      | Creates a `WHERE IN` clause with the given RUTs.                         |
| `whereRutNotIn()`   | Creates a `WHERE NOT IN` clause excluding the given RUTs.                |
| `orWhereRutIn()`    | Creates a `OR WHERE IN` clause with the given RUTs.                      |
| `orWhereRutNotIn()` | Creates a `OR WHERE NOT IN` clause excluding the given RUTs.             |

> [!IMPORTANT]
> 
> These RUT queries work over the RUT Number for convenience, as the RUT Verification Digit should be verified only on persistence.

These scopes can be used in your queries easily:

```php
use App\Models\User;

$user = User::whereRut('20490006-K')->where('is_active', true)->find();
```

The `rut` property is dynamically created from the RUT Number and RUT Verification Digit columns, which uses a [Cast](https://laravel.com/docs/eloquent-mutators#attribute-casting) underneath.

```php
echo $user->rut; // "20490006-K"
```

#### Setting the RUT columns

By convention, the trait uses `rut_num` and `rut_vd` as the default columns to retrieve and save the RUT Number and RUT Verification Digit, respectively.

You can easily change it to anything your database is working with for the given Model:

```php
class User extends Authenticatable
{
    use HasRut;
    
    protected const RUT_NUM = 'numero_rut';
    protected const RUT_VD = 'digito_rut';
    
    // ...    
}
```

#### RUT Appended and columns hidden

By default, the `rut` property is appended, and the underlying columns containing the RUT information are hidden. This enables compatibility with [Livewire real-time validation](https://laravel-livewire.com/docs/2.x/input-validation#real-time-validation).

```json
{
    "id": 1,
    "name": "Taylor",
    "email": "taylor@laravel.com",
    "rut": "16.887.941-5"
}
```

To show the underlying RUT columns instead of the RUT string, simply make `shouldAppendRut()` in the model to return `false`.

```php
/**
 * If the `rut` key should be appended, and hide the underlying RUT columns.
 *
 * @return bool
 */
public function shouldAppendRut(): bool
{
    return false;
}
```

This will effectively return both columns as normal properties.

```json
{
    "id": 1,
    "name": "Taylor",
    "email": "taylor@laravel.com",
    "rut_num": 16887941,
    "rut_vd": "5"
}
```

If you need to make the `rut` key and the underlying columns visible, you may override the `shouldAppendRut()` method and return `false`.

```php
public function shouldAppendRut(): bool
{
   $this->append('rut');

   return false;
}
```

## Configuration

This package works flawlessly out of the box, but you may want to change how a `Rut` is formatted as a string using the global configuration. You can publish it using Artisan:

```shell
php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="config"
```

You will receive the `config/rut.php` config file like this:

```php
use Laragear\Rut\RutFormat;

return [
    'format' => RutFormat::Strict,
    'json_format' => null,
    'uppercase' => true,
];
```

### Default RUT Format

```php
use Laragear\Rut\RutFormat;

return [
    'format' => RutFormat::DEFAULT,
];
```

By default, RUTs are _strictly_ formatted. This config alters how RUTs are serialized as string in your application globally.

### JSON format

```php
use Laragear\Rut\RutFormat;

return [
    'json_format' => null,
];
```

For the case of JSON, RUT are cast as a string using the global format when this is `null`. You can set any format to use when serializing into JSON exclusively.

```php
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;

config()->set('rut.format_json', RutFormat::Raw)

Rut::parse('5.138.171-8')->format(); // "5.138.171-8"
Rut::parse('5.138.171-8')->toJson(); // "51381718"
```

Alternatively, you can override the configuration by using a callback to create your own JSON format. The callback accepts the `Rut` instance, and it should return an `array` or a `string` to be serialized into JSON. A good place to put this logic is in the `boot()` method of your `AppServiceProvider` file.

```php
use Laragear\Rut\Rut;

Rut::$jsonFormat = function (Rut $rut) {
    return ['num' => $rut->num, 'vd' => $rut->vd];
}

Rut::parse('5.138.171-8')->toJson(); // "{"num":5138171,"vd":"8"}"
```

### Verification Digit Case

```php
return [
    'uppercase' => true,
];
```

Since the Verification Digit can be either a single digit or the letter `K`, it's usually good idea to keep the case consistent; to always work with uppercase or lowercase across all the application.

The `Rut` instance by default will use uppercase `K`, but you can change it to lowercase globally by setting this to `false`. This will affect all `Rut` instances.

```php
use Laragear\Rut\RutFormat;
use Laragear\Rut\Rut;

config()->set('rut.uppercase', false)

$rut = Rut::parse('12351839-K');

$rut->format(); // "12.351.839-k"
$rut->toJson(); // "12.351.839-k"
```

> [!TIP]
> 
> This doesn't affect database rules, as the verification digit is normalized automatically.

## PhpStorm stubs

For users of PhpStorm, there is a stub file to aid in macro autocompletion for this package. You can publish it using the `phpstorm` tag:

```shell
php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="phpstorm"
```

The file gets published into the `.stubs` folder of your project. You should point your [PhpStorm to these stubs](https://www.jetbrains.com/help/phpstorm/php.html#advanced-settings-area).

## Laravel Octane compatibility

- There are no singletons using a stale application instance.
- There are no singletons using a stale config instance.
- There are no singletons using a stale request instance.
- `Rut` static properties are only written once at boot time from config.

There should be no problems using this package with Laravel Octane.

## Security

If you discover any security related issues, please email darkghosthunter@gmail.com instead of using the issue tracker.

# License

This specific package version is licensed under the terms of the [MIT License](LICENSE.md), at time of publishing.

[Laravel](https://laravel.com) is a Trademark of [Taylor Otwell](https://github.com/TaylorOtwell/). Copyright © 2011-2024 Laravel LLC.
