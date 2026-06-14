---
name: laragear-rut-seeding
description: Generate valid Chilean RUTs for model factories and database seeders.
---

## Laragear RUT Seeding and Testing

### When to use this skill

Use this skill when generating mock RUTs for database seeders, tests, or factory definitions.

### Features

#### Random RUT Generation

Generate mathematically correct, randomized Chilean RUTs.

```php
use Laragear\Rut\Facades\Generator;

$rut = Generator::makeOne();
```

#### Targeted RUT Generation

Generate valid company or natural person RUT ranges dynamically.

```php
use Laragear\Rut\Facades\Generator;
    
// Generate a company RUT (> 50,000,000)
$companyRut = Generator::asCompanies()->makeOne();

// Generate 100 natural person RUTs (< 50,000,000) without duplicates.
$personRut = Generator::asPeople()->unique()->make(100);
```
