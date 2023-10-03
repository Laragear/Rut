# Upgrading

The following is a somewhat-detailed way to upgrade this package from prior versions.

## From 1.x to 2.x

### Format Enum

Formatting a RUT is now done using the `RutFormat` enum. While using the `Rut` constants and integers will still work, these will be removed the next major version.

```php
use Laragear\Rut\Rut
use Laragear\Rut\RutFormat;

// Before
Rut::parse('187654321')->format(Rut::FORMAT_BASIC);

// Now
Rut::parse('187654321')->format(RutFormat::Basic);
```

While not mandatory, in your config file you should change the `format` key, and `json_format` if it's not `null`, to these enums.

```php
use Laragear\Rut\RutFormat;

return [
    'format' => RutFormat::DEFAULT,
    'json_format' => RutFormat::Raw,
]
```

### Eloquent Model Append

By default, the Model appends the `rut` property on serialization, and hides `rut_num` and `rut_vd` columns. The name of these columns may change depending on the Model RUT configuration, but usually it's not. Anyway, this behavior can be reverted by overriding `shouldAppendRut()` to return `false`.

```php
/**
 * If the `rut` key should be appended, while hiding the underlying RUT columns.
 *
 * @return bool
 */
public function shouldAppendRut(): bool
{
    return false;
}
```

### RUT boundaries fixes 

The new version contains new boundaries to check RUTs, which is a breaking change. Previously, there were to boundaries:

- Person RUT from 100.000 to 49.999.999
- Company RUT from 50.000.000 to 100.000.000

The new version modifies these boundaries to those [informed by IRS (SII)](https://www.sii.cl/documentos/resoluciones/2000b/reso5412.htm) and [by the press](https://web.archive.org/web/20231003163533/https://www.publimetro.cl/cl/noticias/2018/04/25/podria-colapsar-sistema-actual-registro-civil-otorga-mil-numeros-diarios-run-extranjeros.html):

- Person RUT from 100.000 to 45.999.999
- Foreign Investor Person RUT from 46.000.000 to 46.999.999
- Foreign Investor Company RUT from 47.000.000 to 47.999.999
- Contingency RUT from 48.000.000 to 59.999.999
- Company RUT from 60.000.000 to 99.999.999
- Temporal RUT from 100.000.000 to 199.999.999

Given that, there are new validation methods and generator methods for each new type of RUT.
