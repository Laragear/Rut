---
name: laragear-rut-eloquent
description: Store, cast, or query Chilean RUT attributes inside Eloquent models
---

Use this skill when creating migrations for Models that require one or many RUT columns, and query these models by their RUT. Do not use it outside Eloquent Models, or on external APIs.

## Set up migrations with RUT

- Ensure the target Model migration contains the RUT columns by using `$table->rut()`.
- When the model requires handling more than one RUT column, the `rut()` method can be used with different names to avoid collisions, e.g. `$table->rut('child')`, `$table->rut('parent_rut')`, etc.
- Add `->index()` if the RUT is expected to be part of search queries, likely to find records matching the RUT.

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
            
            $table->rut(); // Add the RUT number and verification digit columns as `rut_num` and `rut_vd`.
            
            $table->rut('child'): // Adds `child_num` and `child_vd` columns.

            $table->rut('business')->index(); // Adds the rut columns and indexes the `_num` column.
            
            // ...
        });
    }    
}
```

## Set up a Model with RUT

Locate the model that will use single RUTs and add the `Laragear\Rut\HasRut` trait. 

```php
use Illuminate\Database\Eloquent\Model;
use Laragear\Rut\HasRut;

class User extends Model
{
    use HasRut;
    
    // ...
}
```

The trait only supports one RUT per model. If the model contains more than one RUT as set in its migration, resort to using Attributes instead for each RUT columns:

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laragear\Rut\Rut;

/**
 * @propery \Laragear\Rut\Rut $child_rut 
 */
class User extends Model
{
    protected function childRut(): Attribute
    {
        return Attribute::make(
            get: fn (null $val, array $attrs) => new Rut($attrs['child_rut_num'], $attrs['child_rut_vd']),
            set: fn (mixed $val) => Rut::parse($v)->toString(),
        );
    }
}
```

The RUT can then be set and retrieved in the model using the `rut` attribute. It accepts integers (e.g. `111111111`) or strings (e.g. `22.222.222-2`). It always returns a `Laragear\Rut\Rut` instance when there is a rut.

```php
use App\Models\User;

$rut = User::find(1)->rut;
```

## RUT Query Scopes

When using the `Laragear\Rut\HasRut` trait in the model, the model query builder will have access to many RUT Local Scopes. Read the `Laragear\Rut\HasRut` PHPDoc for an updated list of available methods to properly query the RUT column. 

```php
use App\Models\User;

// Find the user by the exact RUT
$user = User::findByRut('15.518.258-K');
``` 
