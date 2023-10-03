# Rut
[![Latest Version on Packagist](https://img.shields.io/packagist/v/laragear/rut.svg)](https://packagist.org/packages/laragear/rut)
[![Latest stable test run](https://github.com/Laragear/Rut/workflows/Tests/badge.svg)](https://github.com/Laragear/Rut/actions)
[![Codecov coverage](https://codecov.io/gh/Laragear/Rut/branch/1.x/graph/badge.svg?token=5COE8X0JMJ)](https://codecov.io/gh/Laragear/Rut)
[![Maintainability](https://api.codeclimate.com/v1/badges/677b55bbf19bda17e0f5/maintainability)](https://codeclimate.com/github/Laragear/Rut/maintainability)
[![Sonarcloud Status](https://sonarcloud.io/api/project_badges/measure?project=Laragear_Rut&metric=alert_status)](https://sonarcloud.io/dashboard?id=Laragear_Rut)
[![Laravel Octane Compatibility](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://laravel.com/docs/9.x/octane#introduction)

Herramientas para analizar, validar y generar RUT Chilenos en Laravel.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('18.765.432-1');

if ($rut->isValid()) {
    return 'Tu RUT es válido!';
}
```

> [This README is available in english.](README.md) 

## Mantengamos esta librería gratis

[![](.github/.assets/patreon.png)](https://patreon.com/packagesforlaravel)[![](.github/.assets/ko-fi.png)](https://ko-fi.com/DarkGhostHunter)[![](.github/.assets/buymeacoffee.png)](https://www.buymeacoffee.com/darkghosthunter)[![](.github/.assets/paypal.png)](https://www.paypal.com/paypalme/darkghosthunter)

Tu apoyo me permite mantener este paquete gratuito, actualizado y mantenible. Como alternativa, puedes **[correr la voz!](http://twitter.com/share?text=Estoy%20usando%20este%20genial%20paquete%20para%20PHP&url=https://github.com%2FLaragear%2FRut&hashtags=PHP,Laravel,Chile)**

## Requerimientos

- PHP 8.1 o más reciente
- Laravel 9.x o más reciente

## Instalación

Ve a la consola en tu proyecto e instálalo con Composer:

```bash
composer require laragear/rut
```

## [Actualizar desde 1.x (en inglés)](UPGRADE.md#from-1x-to-2x)

## Creando un RUT

Para crear un RUT desde una fuente **válida**, crea una instancia del objeto `Rut` con los números y el dígito verificador, de forma separada.

```php
use Laragear\Rut\Rut;

$rut = new Rut(5138171, 8);
```

Si no, quizás quieras usar `parse()` para crear una nueva instancia desde un pedazo de texto. El método intentará producir una instancia de `Rut` a partir de lo que recibe, o lanzará una excepción `InvalidRutException` en caso de que la cadena de texto no tenga suficientes caracteres para producir un RUT.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('5.138.171-8');
```

## RUT de personas vs empresas

Oficialmente, existen seis tipos de RUT. Para diferenciarlos, tienes acceso a los métodos `is...()`.

| Tipo de RUT              | Desde         | Hasta         | Chequeo de tipo       |
|--------------------------|---------------|---------------|-----------------------|
| Person                   | `    100.000` | ` 45.999.999` | `isPerson()`          |
| Foreign Investor Person  | ` 46.000.000` | ` 47.999.999` | `isInvestor()`        |
| Foreign Investor Company | ` 47.000.000` | ` 47.999.999` | `isInvestorCompany()` |
| Contingency              | ` 48.000.000` | ` 59.999.999` | `isContingency()`     |
| Company                  | ` 60.000.000` | ` 99.999.999` | `isCompany()`         |
| Temporal                 | `100.000.000` | `199.999.999` | `isTemporal()`        |

Adicionalmente, tienes acceso al método `isPermanent()`, que chequea si el RUT está debajo de 100.000.000.

```php
use Laragear\Rut\Rut;

Rut::parse('76.482.465-2')->isPermanent(); // "true"

Rut::parse('76.482.465-2')->isTemporal(); // "false"
```

> Este paquete considera un RUT como válido si está entre 100.000 y 100.000.000, inclusivo. La mayoría de la gente, si no es toda, que usa RUT bajo los 100.000 ya han fallecido.

## Generando RUTs

Este paquete incluye con un conveniente generador de RUT en forma de _facade_, llamado `Generator`, que puede crear cientos o millones de RUT al azar usando métodos fluidos.

El método `make()` genera una colección [Collection](https://laravel.com/docs/collections) de 15 `Rut` por defecto, pero puedes poner cualquier número. También puedes usar `makeOne()` para crear un único `Rut` al azar.

```php
use Laragear\Rut\Facades\Generator;

$ruts = Generator::make(10);

$rut = Generator::makeOne();
```

Usa `asPeople()` para crear RUT de personas, o `asCompanies()` para crear RUT de empresas.

```php
use Laragear\Rut\Facades\Generator;

$people = Generator::asPeople()->make(10);

$companies = Generator::asCompanies()->make(10);

$temporal = Generator::asTemporal()->makeOne();
```

Si planeas crear varios millones de RUT, habrá una alta probabilidad que te encuentres con duplicados. Para evitar colisiones, o mejor dicho, para asegurarse que cada RUT es único en la lista, puedes usar `unique()` a cambio de un rendimiento menor.

```php
use Laragear\Rut\Facades\Generator;

$ruts = Generator::unique()->asCompanies()->make(10000000);
```

## Serialización

Por defecto, todas las instancias de `Rut` son serializadas como texto usando el formato _estricto_. Puedes serializar una instancia de `Rut` de forma diferente usando uno de los formatos disponibles:

| Formato    | Enum                |  Ejemplo       | Descripción                                                      |
|------------|---------------------|----------------|------------------------------------------------------------------|
| Estricto   | `RutFormat::Strict` |  `5.138.171-8` | La opción predeterminada. Incluye separador de miles y guión.    |
| Básico     | `RutFormat::Basic`  |  `5138171-8`   | Sin separador de miles, sólo guión.                              |
| Bruto      | `RutFormat::Raw`    |  `51381718`    | Sin separador de miles ni guión.                                 |

Puedes usar `format()` para formatear el RUT a texto, o pasar un formato diferente vía `RutFormat` por instancia.

```php
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;

$rut = Rut::parse('5.138.171-8');

$rut->format();                  // "5.138.171-8"
$rut->format(RutFormat::Strict); // "5.138.171-8"
$rut->format(RutFormat::Basic);  // "5138171-8"
$rut->format(RutFormat::Raw);    // "51381718"
```

Puedes [cambiar esta configuración de forma global en la configuración](#formato-de-rut).

## Validar un RUT

Deberías usar las Reglas de [Validación incluidas](#reglas-de-validación) para validar RUT en tu aplicación.

Si no, aún puedes manualmente validar un RUT de forma matemática usando `isValid()` o `isInvalid()`.

```php
use Laragear\Rut\Rut;

$rut = Rut::parse('5.138.171-8');

if ($rut->isValid()) {
    return "¡El rut es válido!";
}
```

Al usar el método `validate()`, recibirás una excepción `InvalidRutException` si es inválido.

```php
use Laragear\Rut\Rut;

Rut::parse('5.138.171-K')->validate(); // InvalidRutException: "The given RUT is invalid."
```

También puedes validar RUT como texto directamente, o un RUT con números y dígito verificador por separado, usando el método `check()`.

```php
use Laragear\Rut\Rut;

if (Rut::check('5.138.171-8')) {
    return "¡El rut es válido!";
}

if (Rut::check(5138171, '8')) {
    return "¡Este también es válido!";
}
```

## Reglas de validación

Todas las reglas de validación se pueden traducir. Puedes añadir tu propia traducción para estas reglas publicando los archivos de traducción:

```shell
php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="translations"
```

### Regla `rut`

Esta regla valida el RUT recibido. Automáticamente **limpia el RUT** de todo excepto números y el dígito de verificación, para después ver si el RUT es matemáticamente válido.

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

echo $validator->passes(); // true (6.500.390-2)
```

Esta regla de validación puede ser muy útil para permitir que el usuario decida cómo escribir su RUT (con o sin puntos, con o sin guion), así que no hay necesidad de forzar un formato en específico. Posteriormente, puedes usar algunas [ayudas para recuperar el RUT desde el objeto `Request`]().

Esta regla también acepta un `array` de RUT. En este caso, `rut` pasará la validación si todos los RUT son válidos.

```php
<?php

use Illuminate\Support\Facades\Validator;

$validator = Validator::make([
    'rut' => ['14328145-0', '12.343.580-K', 'estonoesunrut']
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

### Regla `rut_strict`

Esta regla funciona igual que [`rut`](#regla-rut), pero validará que todos los RUT sigan el formato estricto: con separador de miles y guion antes del dígito verificador. No pasará validación si, a pesar de estar estrictamente escrito, es matemáticamente incorrecto.

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

Esta regla también acepta un `array` de RUT. En este caso, `rut_strict` pasará la validación si todos los RUT son escritos estrictamente y matemáticamente válidos.

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

### Regla `rut_exists` (base de datos)

En vez de usar la regla [exists](https://laravel.com/docs/master/validation#rule-exists) de Laravel, puedes usar `rut_exists` en caso de que tu base de datos tenga columnas separadas para el número de RUT y el dígito verificador de RUT.

Para que esto funciona, necesitas indicar qué tabla a buscar, la columna que contiene el número de RUT y la columna que contiene el dígito verificador de RUT. Si no, la regla intentará adivinar el nombre de las columnas añadiendo `_num` y `_vd` al nombre del atributo a validar, respectivamente.

Esta regla automáticamente valida que el RUT sea matemáticamente correcto antes de ejecutar la consulta en la base de datos.

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

Puedes configurar la consulta a la base de datos usando la clase `Rule` de Laravel con el método `rutExists`. Nota que puedes indicar una o ambas columnas del RUT si no quieres que la regla las adivine, especialmente cuando utilizas un comodín en tu regla de validación.

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

Por algunas limitaciones de Laravel, es recomendad usar `Rule` con métodos básicos de la base de datos.

> Todas las reglas de validación para la base de datos normalizarán el dígito verificador en mayúsculas.

### Regla `num_exists` (base de datos)

Esta regla de validación valida que el RUT sea correcto, no estricto, y que sólo el número del RUT exista en la base de datos, sin considerar el dígito verificador en ella. Si la base de datos tiene un índice en la columna del número de RUT, esta validación será muy rápida de ejecutar.

Esta regla automáticamente valida que el RUT sea matemáticamente correcto antes de ejecutar la consulta en la base de datos.

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

Puedes personalizar la consulta usando `Rule` y el método `numExists()`.

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

### Regla `rut_unique` (base de datos)

Esta regla funciona casi igual que la regla `rut_exists`, pero en vez de verificar que el RUT existe en la base de datos, pasará la validación si _no existe_. Esta regla funciona igual que [la regla `unique` de Laravel](https://laravel.com/docs/validation#rule-unique).

Esta regla automáticamente valida que el RUT sea matemáticamente correcto antes de ejecutar la consulta en la base de datos.

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

También puedes excluir un ID o registros de la validación. Para ello, necesitas usar la clase `Rule` y luego `ignore()`.

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

> Todas las reglas de validación para la base de datos normalizarán el dígito verificador en mayúsculas.

> **[PELIGRO]** **Nunca pases datos entregados por el usuario al método `ignore()`. En vez de eso, solo entrega algún ID generado por tu aplicación o UUID, desde una instancia de modelo Eloquent. Si no, tu aplicación será vulnerable a inyecciones SQL.**

### Regla `num_unique` (base de datos)

Este regla valida que sólo el número del RUT no exista en la base de datos, algo útil si la tabla tiene un índice sólo en la columna que contiene el número de RUT. Esta regla funciona igual que [la regla `unique` de Laravel](https://laravel.com/docs/validation#rule-unique).

Esta regla automáticamente valida que el RUT sea matemáticamente correcto antes de ejecutar la consulta en la base de datos.

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

También puedes excluir un ID o registros de la validación. Para ello, necesitas usar la clase `Rule` y luego `ignore()`.


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

> Todas las reglas de validación para la base de datos normalizarán el dígito verificador en mayúsculas.

> **[PELIGRO]** **Nunca pases datos entregados por el usuario al método `ignore()`. En vez de eso, solo entrega algún ID generado por tu aplicación o UUID, desde una instancia de modelo Eloquent. Si no, tu aplicación será vulnerable a inyecciones SQL.**

## Ayuda para migraciones

Si estás creando una base de datos, no necesitas crear las columnas de RUT manualmente. Sólo usa el método `rut()` para producirlas, o `rutNullable()` para permitir que acepten valores nulos.

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

> El método `rutNullable()` hace ambas columnas acepten valores nulos.

Si planeas crear un índice en el número del RUT, simplemente añade `index()`, `primary()` o `unique()`, dependiendo de lo que necesites. Tiene mucho sentido sólo crear el índice en el número en vez de todo el RUT si consideras que sólo RUT válidos entrarán a la base de datos.

```php
Schema::create('users', function (Blueprint $table) {
    // $table->unsignedInteger('rut_num')->primary();
    // $table->char('rut_vd', 1);

    $table->rut()->primary();
    
    // ...
});
```

## Ayuda de RUT en el object Request

Este paquete incluye el macro `rut()` en la instancia `Request`, que permite recibir un RUT desde los datos de una consulta HTTP.

```php
use Illuminate\Http\Request;

public function show(Request $request)
{
    $request->validate([
        'persona' => 'required|rut'
    ]);
    
    $rut = $request->rut('persona');
    
    // ...
}
```

Si la clave a recibir es _iterable_, como un lista `array` o una instancia de `Collection`, recibirás una colección `Collection` de instancias de `Rut`.

```php
$request->validate([
    'gente'   => 'required|array',
    'gente.*' => 'rut'
]);

$ruts = $request->rut('gente');
```

También puedes recibir múltiples claves desde la petición, que también entregarán una colección `Collection`.

```php
$request->validate([
    'mama'        => 'required|rut',
    'papa'        => 'required|rut',
    'hijos'       => 'required|array'
    'hijos.*'     => 'required|rut',
]);

$parents = $request->rut('mama', 'papa'); // También puede ser $request->rut(['mama', 'papa']);
$children = $request->rut('hijos');
```

> Es imperativo que valides los datos de la petición HTTP antes de recibir los RUT. Si no lo haces, y un RUT está malformado, recibirás una excepción.

## Traits para modelos Eloquent

Este paquete incluye el trait (o trato, como quieras) `HasRut` que puedes usar en modelos Eloquent para tablas que tienen el RUT separado en número y dígito verificador.

Este trato añade convenientes métodos para construir tu consulta a la base de datos con RUT, además de incluir el atributo `rut` dentro del modelo, el cual contiene una instancia de `Rut`.

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

Con ello, algunas convenientes ayudas para construir consultas usando RUT:

| Method name         | Description                                                              |
|---------------------|--------------------------------------------------------------------------|
| `findRut()`         | Encuentra un registro por el RUT.                                        |
| `findManyRut()`     | Encuentra varios registros que contengan los RUT                         |
| `findRutOrFail()`   | Encuentra un registro por el RUT o falla.                                |
| `findRutOrNew()`    | Encuentra un registro por el RUT o crea una nueva instancia del modelo.  |
| `whereRut()`        | Crea una cláusula `WHERE` donde el RUT es igual al entregado.            |
| `whereRutNot()`     | Crea una cláusula `WHERE` excluyendo uno o más RUT.                      |
| `orWhereRut()`      | Crea una cláusula `OR WHERE` donde el RUT es igual al entregado.         |
| `orWhereRutNot()`   | Crea una cláusula `OR WHERE` excluyendo uno o más RUT.                   |
| `whereRutIn()`      | Crea una cláusula `WHERE IN` donde el RUT está en la lista entregada.    |
| `whereRutNotIn()`   | Crea una cláusula `WHERE NOT IN` excluyendo uno o más RUT.               |
| `orWhereRutIn()`    | Crea una cláusula `OR WHERE IN` donde el RUT está en la lista entregada. |
| `orWhereRutNotIn()` | Crea una cláusula `OR WHERE NOT IN` excluyendo uno o más RUT.            |

> Estas cláusulas y ayudas para la consulta en la base de datos sólo trabajan sobre la columna del número de RUT por conveniencia, ya que el dígito verificador de RUT sólo debería ser verificado al persistir el RUT.

La propiedad `rut` es dinámicamente creada a partir de ambas columnas de RUT usando un [Cast](https://laravel.com/docs/eloquent-mutators#attribute-casting).

```php
echo $user->rut; // "20490006-K"
```

#### Configurar las columnas de RUT

Por convención, el _trait_ usa `rut_num` and `rut_vd` como el nombre del número y dígito verificador de RUT, respectivamente. Puedes fácilmente cambiarlos por cualquier otro con los que estés trabajando en la tabla, para un modelo específico.

```php
class User extends Authenticatable
{
    use HasRut;
    
    protected const RUT_NUM = 'numero_rut';
    protected const RUT_VD = 'digito_rut';
    
    // ...    
}
```

#### Propiedad `rut` añadida

Por defecto, un modelo con las columnas de RUT serializará el RUT como una única propiedad, y esconderá las columnas de RUT. Esto permite que sea compatible con la [validación en tiempo-real de Livewire](https://laravel-livewire.com/docs/2.x/input-validation#real-time-validation).

```json
{
    "id": 1,
    "name": "Taylor",
    "email": "taylor@laravel.com",
    "rut": "16.887.941-5"
}
```

Para mostrar las columnas de RUT, y a cambio esconder la propiedad `rut`, sobreescribe el método `shouldAppendRut()` en el modelo a elección para que entregue `false`.

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

Esto efectivamente removerá la clave `rut`, mostrando las columnas de RUT como cualquier otra.

```json
{
    "id": 1,
    "name": "Taylor",
    "email": "taylor@laravel.com",
    "rut_num": 16887941,
    "rut_vd": "5"
}
```

Si necesitas hacer que `rut` y las columnas de RUT se muestren simultáneamente, sobreescribe el método `shouldAppendRut()` de la siguiente forma:

```php
public function shouldAppendRut(): bool
{
   $this->append('rut');

   return false;
}
```

## Configuración

Este paquete funciona de maravillas después de instalarlo, pero quizás quieras cambiar cómo se formatea un `Rut` cuando pasa a ser texto. Para ello, publica el archivo de configuración con Artisan.

```shell
php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="config"
```

Recibirás un archivo de configuración en `config/rut.php`.

```php
use Laragear\Rut\RutFormat;

return [
    'format' => RutFormat::Strict,
    'json_format' => null,
    'uppercase' => true,
];
```

### Formato de RUT

```php
use Laragear\Rut\RutFormat;

return [
    'format' => RutFormat::DEFUALT,
];
```

Los RUTS son formateados estrictamente por defecto. Esta configuración altera cómo los RUT deben ser formateados en toda tu aplicación.

### Format JSON de RUT

Para el caso de JSON, los RUT son transformados a cadenas de texto usando el formato global cuando es `null`. Puedes cambiar qué formato usar cuando se serializan exclusivamente como JSON.

```php
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;

config()->set('rut.format_json', RutFormat::Raw)

Rut::parse('5.138.171-8');           // "5.138.171-8"
Rut::parse('5.138.171-8')->toJson(); // "5138171-8"
```

Como alternativa, puedes usar una función para crear tu propio formato en JSON. La función acepta la instancia de `Rut` que será transformada, y debe devolver un `array` o `string` para ser serializado en JSON. Un buen lugar para poner esta lógica es en el método `boot()` del archivo `AppServiceProvider`.

```php
use Laragear\Rut\Rut;

Rut::$jsonFormat = function (Rut $rut) {
    return ['num' => $rut->num, 'vd' => $rut->vd];
}

Rut::parse('5.138.171-8')->toJson(); // "{"num":5138171,"vd":"8"}"
```

### Mayúscula o minúscula

```php
return [
    'uppercase' => true,
];
```

Dado que el dígito de verificación puede ser `K`, es usualmente buena idea siempre trabajar con mayúsculas o minúsculas en toda la aplicación.

La instancia `Rut` por defecto usará mayúsculas `K`, pero puedes cambiarlo de forma global colocando `false` en esta clave, lo que afectará a todas las instancias y la serialización en JSON.

```php
use Laragear\Rut\Format;
use Laragear\Rut\Rut;

config()->set('rut.uppercase', false)

$rut = Rut::parse('12351839-K');

$rut->format(); // "12.351.839-k"
$rut->toJson(); // "12.351.839-k"
```

> Esto no afecta las reglas de base de datos, puesto que normalizan el dígito verificador automáticamente.

## Auto-completar en PhpStorm

Para usuarios de PhpStorm, hay un archivo "stub" para ayudarte a autocompletar con código disponible en este paquete. Puedes publicarlo usando el tag `phpstorm`:

```shell
php artisan vendor:publish --provider="Laragear\Rut\RutServiceProvider" --tag="phpstorm"
```

El archivo se publica dentro del directorio `.stubs` en tu proyecto. Deberías [apuntar PhpStorm a esta carpeta stubs](https://www.jetbrains.com/help/phpstorm/php.html#advanced-settings-area) para que funcione.

## Compatibilidad con Laravel Octane

- No hay objetos únicos usando la instancia maestra de la aplicación.
- No hay objetos únicos usando la instancia maestra de la configuración.
- No hay objetos únicos usando la instancia maestra de la petición HTTP.
- Las propiedades estáticas de `Rut` sólo se escriben una vez durante el inicio de la aplicación.

No deberías tener problemas al usar este paquete como es indicado junto a Laravel Octane.

## Seguridad

Si descubres algún problema de seguridad o vulnerabilidad, manda un correo a darkghosthunter@gmail.com en vez hacerlo público en el repositorio.

# Licencia

Este paquete está licenciado por los términos descritos en la [Licencia MIT](LICENSE.md) (en inglés).

[Laravel](https://laravel.com) es una marca registrada de [Taylor Otwell](https://github.com/TaylorOtwell/). Copyright © 2011-2022 Laravel LLC.
