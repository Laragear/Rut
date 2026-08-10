---
name: laragear-rut-seeding
description: Generate valid Chilean RUTs for model factories and database seeders
---

Use this skill when generating mock or fake RUTs for database seeders, tests, factory definitions, or when generating fake data that requires also a fake RUT. Do not use in controllers, views, or jobs, unless the user explicitly requires it.

# Generate random RUT

Generate mathematically correct, randomized Chilean RUTs with the `Generator` class facade.

```php
use Laragear\Rut\Facades\Generator;

$rut = Generator::makeOne();
```

For targeted RUT generation (person, investors, investment companies, contingency, businesses, temporal, or definitive), use the apropiate builder method and use `makeOne()` for a single result, or `make($number)` for many.

```php
use Laragear\Rut\Facades\Generator;
    
// Generate a company RUT (> 50.000.000-*)
$companyRut = Generator::asCompanies()->makeOne();

// Generate 100 natural person RUTs (< 50,000,000) without duplicates on the request/command lifecicle.
$manyPeopleRuts = Generator::asPeople()->make(100);
```

Generate RUT constrained by numbers with `between()`.

```php
use Laragear\Rut\Facades\Generator;
    
$ruts = Generator::between(10_000_000, 30_000_000)->make(500);
```

Use `unique()` in the generator when RUT column of the model uses `unique()` or `primary()` indexes. This will avoid reusing the same RUT accidentally on generation, ensuring the database ingestion does not error because of PRIMARY/UNIQUE constraints.  

```php
use Laragear\Rut\Facades\Generator;
    
$ruts = Generator::unique()->make(100);
```

## On Model Factories

When a Model factory requires a RUT, set the rut attribute with the generator instance:

```php
use Illuminate\Support\Facades\Hash;
use Laragear\Rut\Facades\Generator;

public function definition()
{
    return [
        'name' => $this->faker->name,
        'rut' => Generator::asPeople()->makeOne(),
        'email' => $this->faker->email,
        'password' => static::$password ?? Hash::make('secret'),
    ];
}
```
