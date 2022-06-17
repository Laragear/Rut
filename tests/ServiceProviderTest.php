<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rule;
use Laragear\Rut\Generator;
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;
use Laragear\Rut\RutServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_merges_config(): void
    {
        static::assertSame(
            File::getRequire(RutServiceProvider::CONFIG),
            $this->app->make('config')->get('rut')
        );
    }

    public function test_loads_translations(): void
    {
        static::assertArrayHasKey('rut', $this->app->make('translator')->getLoader()->namespaces());
    }

    public function test_binds_generator(): void
    {
        static::assertTrue($this->app->bound(Generator::class));
    }

    public function test_registers_blueprint_macros(): void
    {
        static::assertTrue(Blueprint::hasMacro('rut'));
        static::assertTrue(Blueprint::hasMacro('rutNullable'));
    }

    public function test_registers_rule_macros(): void
    {
        static::assertTrue(Rule::hasMacro('rutExists'));
        static::assertTrue(Rule::hasMacro('rutUnique'));
        static::assertTrue(Rule::hasMacro('numExists'));
        static::assertTrue(Rule::hasMacro('numUnique'));
    }

    public function test_registers_request_macro(): void
    {
        static::assertTrue(Request::hasMacro('rut'));
    }

    public function test_publishes_config(): void
    {
        static::assertSame(
            [RutServiceProvider::CONFIG => $this->app->configPath('rut.php')],
            ServiceProvider::pathsToPublish(RutServiceProvider::class, 'config')
        );
    }

    public function test_publishes_translation(): void
    {
        static::assertSame(
            [RutServiceProvider::LANG => $this->app->langPath('vendor/rut')],
            ServiceProvider::pathsToPublish(RutServiceProvider::class, 'translations')
        );
    }

    public function test_publishes_phpstorm_meta(): void
    {
        static::assertSame(
            [RutServiceProvider::STUBS => $this->app->basePath('.stubs/rut.php')],
            ServiceProvider::pathsToPublish(RutServiceProvider::class, 'phpstorm')
        );
    }

    protected function useCustomRutDefaults($app)
    {
        $app->make('config')->set([
            'rut.format' => RutFormat::Raw,
            'rut.uppercase' => false,
            'rut.json_format' => RutFormat::Basic,
        ]);
    }

    /**
     * @define-env useCustomRutDefaults
     */
    public function test_boots_rut_defaults(): void
    {
        static::assertFalse(Rut::$uppercase);
        static::assertSame(RutFormat::Raw, Rut::$format);
        static::assertSame(RutFormat::Basic, Rut::$jsonFormat);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Rut::$format = Rut::FORMAT_STRICT;
        Rut::$uppercase = true;
        Rut::$jsonFormat = null;
    }
}
