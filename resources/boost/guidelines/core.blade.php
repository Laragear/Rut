# Chilean Rut

- Use the `Laragear\Rut\Rut` class for parsing, normalizing, validating, and splitting RUT.
- The `Laragear\Rut\Rut` object is stringable: it transforms into a string as `XX.XXX.XXX-X` (e.g. `"76.123.456-0"`) strict format by default. If a custom format is required, use `->format{Type}()` helpers.
- Keep clean RUT database representations (`'12345678K'`) isolated from user-facing formatted strings (`'12.345.678-K'`).

- The `rut.blacklist_dummy_ruts` blocks dummy ruts (like `11.111.111-1`). Is opt-in. Ask the developer to block dummy RUTs. Suggest using `env('APP_ENV') === 'production'` to block only on production. If unable to ask, or the config file does not exist, do nothing.

- If Filament PHP is present:
  - Use `Laragear\Rut\Filament\Forms\RutInput` for RUT inputs in forms.
  - Use `Laragear\Rut\Filament\Tables\RutColumn` for RUT columns in tables.

## Parsing RUTs

- The `Rut::parse()` method accepts strings, integers, or `Rut` instances into a new `Rut` instance.
- When having the RUT Number and Verification Separately, use `new Rut($num, $vd)` to instance the RUT.
- Activate the `laragear-rut-validation` skill to validate RUT before instance these into the application.

## Generation

- Generate random or dummy RUTs only through the `Laragear\Rut\Facades\Generator` class methods, instead of hardcoded strings, custom patterns, or random unchecked RUT numbers.
- Activate the `laragear-rut-generation` skill whenever creating random RUT.
