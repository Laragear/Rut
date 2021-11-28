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
        Blueprint::macro('rut', function(): ColumnDefinition {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            return tap($this->unsignedInteger('rut_num'), function (): void {
                /** @var \Illuminate\Database\Schema\Blueprint $this */
                $this->char('rut_vd', 1);
            });
        });

        Blueprint::macro('rutNullable', function (): ColumnDefinition {
            /** @var \Illuminate\Database\Schema\Blueprint $this */
            return tap($this->unsignedInteger('rut_num')->nullable(), function (): void {
                /** @var \Illuminate\Database\Schema\Blueprint $this */
                $this->char('rut_vd', 1)->nullable();
            });
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
        Request::macro('rut', function (string $input = 'rut'): Rut {
            /** @var \Illuminate\Http\Request $this */
            return Rut::parse($this->input($input));
        });

        Request::macro('ruts', function (array|string $input = 'ruts'): Collection {
            /** @var \Illuminate\Http\Request $this */
            return Rut::map($this->collect($input));
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'lararut');
        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->resourcePath('lang/vendor/lararut')
        ], 'translations');
    }
}