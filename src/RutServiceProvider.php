<?php

namespace Laragear\Rut;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;

use function is_iterable;
use function is_string;

class RutServiceProvider extends ServiceProvider
{
    /**
     * Rules to register into the validator.
     *
     * @var array
     */
    protected const RULES = [
        ['rut', 'validateRut', 'lararut::validation.rut'],
        ['rut_strict', 'validateRutStrict', 'lararut::validation.strict'],
        ['rut_exists', 'validateRutExists', 'lararut::validation.exists'],
        ['rut_unique', 'validateRutUnique', 'lararut::validation.unique'],
        ['num_exists', 'validateNumExists', 'lararut::validation.exists'],
        ['num_unique', 'validateNumUnique', 'lararut::validation.unique'],
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
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
        $this->app->resolving('validator', static function (Factory $validator, Application $app): void {
            $translator = $app->make('translator');

            foreach (static::RULES as [$rule, $extension, $key]) {
                $validator->extend($rule, [ValidatesRut::class, $extension], $translator->get($key));
            }
        });
    }

    /**
     * Register the RUT helper for the blueprint
     *
     * @return void
     */
    protected function macroBlueprint(): void
    {
        Blueprint::macro('rut', function(string $prefix = 'rut'): ColumnDefinition {
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
     * Register the macro for the Rule class
     *
     * @return void
     */
    protected function macroRules(): void
    {
        Rule::macro('rutExists', static function ($table, $numColumn = 'NULL', $rutColumn = 'NULL'): Rules\RutExists {
            return new Rules\RutExists($table, $numColumn, $rutColumn);
        });

        Rule::macro('rutUnique', static function ($table, $numColumn = 'NULL', $rutColumn = 'NULL'): Rules\RutUnique {
            return new Rules\RutUnique($table, $numColumn, $rutColumn);
        });

        Rule::macro('numExists', static function ($table, $column = 'NULL'): Rules\NumExists {
            return new Rules\NumExists($table, $column);
        });

        Rule::macro('numUnique', static function ($table, $column = 'NULL'): Rules\NumUnique {
            return new Rules\NumUnique($table, $column);
        });
    }

    /**
     * Adds a macro to the Request.
     *
     * @return void
     */
    protected function macroRequest(): void
    {
        Request::macro('rut', function (iterable|string $input = 'rut'): Rut|Collection {
            /** @var \Illuminate\Http\Request $this */

            // Get a collection only if the user is passing multiple keys.
            $data = is_string($input) ? $this->input($input) : $this->collect($input);

            // If the returned data is iterable, map it, otherwise return a single Rut.
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
        Rut::$format = $config->get('rut.format', Format::DEFAULT);
        Rut::$uppercase = $config->get('rut.uppercase', true);
        Rut::$jsonFormat = $config->get('rut.json_format');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'lararut');
        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->resourcePath('lang/vendor/lararut')
        ], 'translations');
    }
}