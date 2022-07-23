<?php

declare(strict_types=1);

namespace Laragear\Rut;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use function count;
use function is_iterable;

/**
 * @internal
 */
class RutServiceProvider extends ServiceProvider
{
    public const CONFIG = __DIR__.'/../config/rut.php';
    public const LANG = __DIR__.'/../lang';
    public const STUBS = __DIR__.'/../.stubs/stubs.php';

    /**
     * Rules to register into the validator.
     *
     * @var array
     */
    public const RULES = [
        ['rut', 'validateRut', 'rut::validation.rut'],
        ['rut_strict', 'validateRutStrict', 'rut::validation.strict'],
        ['rut_exists', 'validateRutExists', 'rut::validation.exists'],
        ['rut_unique', 'validateRutUnique', 'rut::validation.unique'],
        ['num_exists', 'validateNumExists', 'rut::validation.exists'],
        ['num_unique', 'validateNumUnique', 'rut::validation.unique'],
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(static::CONFIG, 'rut');
        $this->loadTranslationsFrom(static::LANG, 'rut');

        $this->registerGenerator();
        $this->registerRules();
        $this->macroRules();
        $this->macroRequest();
        $this->macroBlueprint();
    }

    /**
     * Registers the random generator.
     *
     * @return void
     */
    protected function registerGenerator(): void
    {
        $this->app->bind(Generator::class, static function (): Generator {
            return new Generator();
        });
    }

    /**
     * Register the Validator rules.
     *
     * @return void
     */
    protected function registerRules(): void
    {
        $this->callAfterResolving('validator', static function (Factory $validator, Application $app): void {
            $translator = $app->make('translator');

            foreach (static::RULES as [$rule, $extension, $key]) {
                $validator->extend($rule, ValidatesRut::{$extension}(...), $translator->get($key));
            }
        });
    }

    /**
     * Register the RUT helper for the blueprint.
     *
     * @return void
     */
    protected function macroBlueprint(): void
    {
        Blueprint::macro('rut', function (string $prefix = 'rut'): ColumnDefinition {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            $column = $this->unsignedInteger("{$prefix}_num");

            $this->char("{$prefix}_vd", 1);

            return $column;
        });

        Blueprint::macro('rutNullable', function (string $prefix = 'rut'): ColumnDefinition {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            $column = $this->unsignedInteger("{$prefix}_num")->nullable();

            $this->char("{$prefix}_vd", 1)->nullable();

            return $column;
        });
    }

    /**
     * Register the macro for the Rule class.
     *
     * @return void
     */
    protected function macroRules(): void
    {
        Rule::macro(
            'rutExists',
            static function (Model|string $table, $numColumn = 'NULL', $rutColumn = 'NULL'): Rules\RutExists {
                return new Rules\RutExists($table, $numColumn, $rutColumn);
            }
        );

        Rule::macro(
            'rutUnique',
            static function (Model|string $table, $numColumn = 'NULL', $rutColumn = 'NULL'): Rules\RutUnique {
                return new Rules\RutUnique($table, $numColumn, $rutColumn);
            }
        );

        Rule::macro(
            'numExists',
            static function (Model|string $table, $column = 'NULL'): Rules\NumExists {
                return new Rules\NumExists($table, $column);
            }
        );

        Rule::macro(
            'numUnique',
            static function (Model|string $table, $column = 'NULL'): Rules\NumUnique {
                return new Rules\NumUnique($table, $column);
            }
        );
    }

    /**
     * Adds a macro to the Request.
     *
     * @return void
     */
    protected function macroRequest(): void
    {
        Request::macro('rut', function (iterable|string ...$input): Rut|Collection {
            /** @var \Illuminate\Http\Request $this */
            if (empty($input)) {
                $input[0] = 'rut';
            } elseif (is_iterable($input[0])) {
                $input = $input[0];
            }

            $data = count($input) === 1 ? $this->input($input[0]) : $this->collect($input);

            return is_iterable($data) ? Rut::map($data) : Rut::parse($data);
        });
    }

    /**
     * Bootstrap any package services.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @return void
     */
    public function boot(Repository $config): void
    {
        Rut::$format = $this->normalizeFormat($config->get('rut.format', RutFormat::DEFAULT));
        Rut::$uppercase = $config->get('rut.uppercase', true);
        Rut::$jsonFormat = $this->normalizeFormat($config->get('rut.json_format'));

        if ($this->app->runningInConsole()) {
            $this->publishes([static::CONFIG => $this->app->configPath('rut.php')], 'config');
            $this->publishes([static::LANG => $this->app->langPath('vendor/rut')], 'translations');
            $this->publishes([static::STUBS => $this->app->basePath('.stubs/rut.php')], 'phpstorm');
        }
    }

    /**
     * Normalize an int into an Enum.
     *
     * @param  \Laragear\Rut\RutFormat|int|null  $format
     * @return \Laragear\Rut\RutFormat|null
     *
     * @deprecated This helper will be removed in the next version as there will no need to use it.
     */
    protected function normalizeFormat(RutFormat|int|null $format): ?RutFormat
    {
        return match ($format) {
            0 => RutFormat::Raw,
            1 => RutFormat::Basic,
            2 => RutFormat::Strict,
            default => $format
        };
    }
}
