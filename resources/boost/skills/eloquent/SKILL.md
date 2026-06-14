---
name: laragear-rut-eloquent
description: Manage Chilean RUTs in Eloquent models with custom casting and scopes.
---

## Laragear RUT Eloquent Integration

### When to use this skill

Use this skill when storing, casting, or querying Chilean RUT attributes inside Eloquent models.

### Set up

Locate the model that will use RUTs and add the `Laragear\Rut\HasRut` trait.

```php
use Illuminate\Database\Eloquent\Model;
use Laragear\Rut\HasRut;

class User extends Model
{
    use HasRut;
    
    // ...
}
```

Ensure the Model migration contains the RUT columns:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // ...
            
            $table->rut(); // Add the RUT number and verification digit columns
            
            // ...
        });
    }    
}
```

### Features

#### RUT Casting

Cast database strings automatically into `Rut` value objects for formatting helpers.

```php
use Laragear\Rut\Casts\CastRut;

protected function casts()
{
    return [
        'rut' => CastRut::class,
    ];
}
```

#### RUT Query Scopes

When using the `Laragear\Rut\HasRut` trait in the model, the model query builder will have access to many RUT Local Scopes. Read that trait PHPDoc for a list of available methods.

```php
use App\Models\User;

$user = User::findByRut('15.518.258-K');
``` 
