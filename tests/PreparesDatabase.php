<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Laragear\Rut\Facades\Generator;

trait PreparesDatabase
{
    protected function prepareDatabase(): void
    {
        $this->loadLaravelMigrations();

        $this->app->make('db')
            ->connection()
            ->getSchemaBuilder()
            ->table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('rut_num')->nullable();
                $table->string('rut_vd')->nullable();
            });

        User::make()->forceFill([
            'id' => 1,
            'name' => 'John',
            'email' => 'john.doe@email.com',
            'password' => '123456',
            'rut_num' => ($rut = Generator::makeOne())->num,
            'rut_vd' => strtoupper($rut->vd),
        ])->save();

        User::make()->forceFill([
            'id' => 2,
            'name' => 'Michael',
            'email' => 'michael.doe@email.com',
            'password' => '123456',
            'rut_num' => ($rut = Generator::makeOne())->num,
            'rut_vd' => strtoupper($rut->vd),
        ])->save();

        User::make()->forceFill([
            'id' => 3,
            'name' => 'Carmen',
            'email' => 'carmen.doe@email.com',
            'password' => '123456',
            'rut_num' => 20490006,
            'rut_vd' => strtoupper('k'),
        ])->save();
    }
}