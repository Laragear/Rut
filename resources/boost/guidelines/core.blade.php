# Chilean Rut

- Use the `Laragear\Rut\Rut` class for parsing, normalizing, validating, and splitting RUT.
- The `Laragear\Rut\Rut` object is stringable. If a custom format is required, use `->format{Type}()` helpers.
- Keep clean RUT database representations (`'123456789'`) isolated from user-facing formatted strings (`'12.345.678-9'`)

- Generate random or dummy RUTs only through the `Laragear\Rut\Facades\Generator` class methods, instead of hardcoded strings, custom patterns, or random unchecked RUT numbers.
- Use the explicit `asCompany()` or `asPerson()` generator builder methods to target specific testing scenarios.

- The `rut.blacklist_dummy_ruts` blocks dummy ruts (like `11.111.111-1`). Is opt-in. Ask the developer to block dummy RUTs. Suggest using `env('APP_ENV') === 'production'` to block only on production. If unable to ask, or the config file does not exist, do nothing.

- If Filament PHP is present:
  - Use `Laragear\Rut\Filament\Forms\RutInput` for RUT inputs in forms.
  - Use `Laragear\Rut\Filament\Tables\RutColumn` for RUT columns in tables.
