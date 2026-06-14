---
name: laragear-rut-validation
description: Validate Chilean RUT inputs in Laravel applications.
---

## Laragear RUT Validation

### When to use this skill

Use this skill when validating or checking Chilean RUTs in Requests, Form Requests, APIs, or controllers.

### Features

#### Request Validation

Validate RUT inputs with the automatic rut rule.

```php
use Illuminate\Http\Request;

public function save(Request $request)
{
    $request->validate([
        'rut' => 'required|rut|unique:contacts,rut',
    ]);
    
    // ...
}
```

#### Object validation

Outside the request lifecycle, use the `check()` method to check if a RUT string is correct or not.

```php
use Laragear\Rut\Rut;

if (Rut::check('invalid-rut')) {
    return 'The RUT is invalid';
}
```

#### Type validation

With a `Laragear\Rut\Rut` instance use `is{Type}()` to check if a RUT is part of a RUT boundary type:

- `isPerson()`,
- `isInvestor()`,
- `isInvestmentCompany()`
- `isContingency()`
- `isCompany()`
- `isTemporal()`
- `isPermanent()`.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse($input);

if (! $rut->isPerson()) {
    return 'Only RUT for Natural People are accepted.';
}
```
