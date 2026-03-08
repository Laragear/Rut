<?php

namespace Tests\Filament;

use Tests\TestCase as PackageTestCase;

use function array_merge;
use function class_exists;

class TestCase extends PackageTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkippedUnless(
            class_exists(\Filament\Support\SupportServiceProvider::class),
            'Filament PHP was not installed.'
        );

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(
            parent::getPackageProviders($app),
            [
                \BladeUI\Icons\BladeIconsServiceProvider::class, // Add this
                \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class, // And this
                // Filament Core and required UI providers
                \Filament\Support\SupportServiceProvider::class,
                \Filament\Schemas\SchemasServiceProvider::class, // New in 5.0
                \Filament\Actions\ActionsServiceProvider::class,
                \Filament\Forms\FormsServiceProvider::class,
                \Filament\Tables\TablesServiceProvider::class,
                \Filament\Notifications\NotificationsServiceProvider::class,
            ]
        );
    }
}
