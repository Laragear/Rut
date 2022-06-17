# Upgrading

The following is a somewhat-detailed way to upgrade this package.

## From 1.x to 2.x

### Format Enum

Formatting a RUT is now done using the `RutFormat` enum. While using the `Rut` constant and `int`, will still work, these will be removed in later versions.

```php
use Laragear\Rut\Rut
use Laragear\Rut\RutFormat;

// Before
Rut::parse('187654321')->format(Rut::FORMAT_BASIC);

// Now
Rut::parse('187654321')->format(RutFormat::Basic);
```

In your config file, you should change the `format` key, and `json_format` if it's not `null`, to these enums.

```php
use Laragear\Rut\RutFormat;

return [
    'format' => RutFormat::DEFAULT,
    'json_format' => RutFormat::Raw,
]
```

## Eloquent Model Append

By default, the Model appends the `rut` property on serialization, and hides `rut_num` and `rut_vd` columns. The name of these columns may change depending on the Model RUT configuration. This behavior can be reverted by overriding `shouldAppendRut()` to return `false`.

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

## 
