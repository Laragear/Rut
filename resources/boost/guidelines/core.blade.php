## Laragear Rut Boost AI Guideline

Strict directives for AI code generation involving Chilean RUT management with the laragear/rut package.

### 1. Core Principles

- **Check functions and signatures:** Do not rely on internal knowledge, proactively read the source code of this package for correct method names and signatures.
- **No Manual Calculation:** Never write custom regex, math loops, or module 11 algorithms to validate or format Chilean RUTs. Always rely on the `Laragear\Rut\Rut` class or package features.
- **Strict Format Handling:** Keep clean RUT database representations (`'123456789'`) isolated from user-facing formatted strings (`'12.345.678-9'`).

### 2. Model Casting

- **Cast Attribute:** Always use `Laragear\Rut\Casts\CastRut` in the Eloquent model `$casts` array or `cast()` method.
- **Database Storage:** Store RUTs by using the `rut()` macro available in the `Illuminate\Database\Schema\Builder` class.
- **Model Storage:** Use valid RUT string or number to set on the model property, like `$model->rut = '15.518.258-K'`.

### 3. Validation

- **Rules:** Use the `rut` rule for general validation, and `rut:strict` only when the exact punctuation format (dots and dash) is strictly required.
- **Pre-validation:** When RUT inputs are split into number and verification digit, standardize inputs inside `prepareForValidation()` using `Rut::parse($value)->formatStrict()`.

### 4. Testing & Seeding

- **Mocking:** Always use `Laragear\Rut\Facades\Generator` class methods instead of hardcoded strings or custom faker patterns.
- **Generators:** Use the explicit `asCompany()` or `asPerson()` genrator builder methods to target specific testing scenarios.

### 5. Production environments

- **Block dummy RUTs:** The `rut.blacklist_dummy_ruts` config is opt-in. Ask the developer to block dummy RUTs. Suggest using `env('APP_ENV') === 'production'` to block only on production. If unable to ask, or the config file does not exist, do nothing.
