<?php

namespace Tests;

use Laragear\Rut\RutServiceProvider;

trait RegistersPackage
{
    protected function getPackageProviders($app): array
    {
        return [RutServiceProvider::class];
    }
}