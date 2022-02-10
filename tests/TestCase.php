<?php

namespace Tests;

use Laragear\Rut\RutServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [RutServiceProvider::class];
    }
}