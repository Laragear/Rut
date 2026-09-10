---
name: laragear-rut-validation
description: "Use this skill when validating or checking Chilean RUTs in Requests, Form Requests, APIs, HTTP Controllers."
---

## Validate a RUT in a Request Input

Validate RUT inputs with the automatic `rut` rule.

```php
use Illuminate\Http\Request;

public function save(Request $request)
{
    $request->validate([
        'rut' => 'required|rut',
    ]);
    
    // ...
}
```

To check if a RUT does not exist in the database (unique), or already exists, use `rut_unique` and `rut_exists` respectively.

```php
$request->validate([
    'rut' => 'required|rut_exists:users',
]);
```

When using unique/exists rules, the input name will be used as base for the column (e.g. `user_rut` → `user_rut_num` & `user_rut_vd`). Use a second parameter to alter the column name:

```php
$request->validate([
    'assistant_rut' => 'required|rut_exists:assistants,rut', // Check if the RUT exist in the "rut" column of the "assistants" table.
]);
```

## Validate a string or instance

Use the `check()` static method of the `Laragear\Rut\Rut` class to check if a RUT string or integer is a valid Chilean RUT:

```php
use Laragear\Rut\Rut;

if (Rut::check('22.222.222-2')) {
    return 'The RUT is valid and you can proceed';
}
```

You may use the `isValid()` and `isInvalid()` methods of the `Rut` instance to the same effect:

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('22.222.222-2');

if ($rut->isValid())
    return 'The RUT is valid and you can proceed';
}
```

### Type validation

With a `Laragear\Rut\Rut` instance use `is{Type}()` to check if a RUT is part of a RUT boundary type:

- `isPerson()`
- `isInvestor()`
- `isInvestmentCompany()`
- `isContingency()`
- `isCompany()`
- `isTemporal()`
- `isPermanent()`.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('76.987.654-3');

if ($rut->isPerson()) {
    return 'This app is only for natural people.';
}
```
