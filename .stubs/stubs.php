<?php

namespace Illuminate\Database\Schema
{
    class Blueprint
    {
        /**
         * Create new compound RUT columns.
         *
         * @param  string  $prefix
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        public function rut(string $prefix = 'rut'): ColumnDefinition
        {
            //
        }

        /**
         * Create new compound nullable RUT columns.
         *
         * @param  string  $prefix
         * @return \Illuminate\Database\Schema\ColumnDefinition
         */
        public function rutNullable(string $prefix = 'rut'): ColumnDefinition
        {
            //
        }
    }
}

namespace Illuminate\Http
{
    use Illuminate\Support\Collection;
    use Laragear\Rut\Rut;

    class Request
    {
        /**
         * Returns a RUT or a collection of RUTs from the request.
         *
         * @param  iterable|string  ...$input
         * @return \Laragear\Rut\Rut|\Illuminate\Support\Collection
         */
        public function rut(iterable|string ...$input): Rut|Collection
        {
            //
        }
    }
}

namespace Illuminate\Validation
{

    use Illuminate\Database\Eloquent\Model;
    use Laragear\Rut\Rules\NumExists;
    use Laragear\Rut\Rules\NumUnique;
    use Laragear\Rut\Rules\RutExists;
    use Laragear\Rut\Rules\RutUnique;

    class Rule
    {
        /**
         * Create a new RUT exists rule.
         *
         * @param  \Illuminate\Database\Eloquent\Model|string|class-string  $table
         * @param  string  $numColumn
         * @param  string  $rutColumn
         * @return \Laragear\Rut\Rules\RutExists
         */
        public static function rutExists(Model|string $table, $numColumn = 'NULL', $rutColumn = 'NULL'): RutExists
        {
            //
        }

        /**
         * Create a new RUT unique rule.
         *
         * @param  \Illuminate\Database\Eloquent\Model|string|class-string  $table
         * @param  string  $numColumn
         * @param  string  $rutColumn
         * @return \Laragear\Rut\Rules\RutUnique
         */
        public static function rutUnique(Model|string $table, $numColumn = 'NULL', $rutColumn = 'NULL'): RutUnique
        {
            //
        }

        /**
         * Create a new RUT Number exists rule.
         *
         * @param  \Illuminate\Database\Eloquent\Model|string|class-string  $table
         * @param  string  $column
         * @return \Laragear\Rut\Rules\NumExists
         */
        public static function numExists(Model|string $table, $column = 'NULL'): NumExists
        {
            //
        }

        /**
         * Create a new RUT Number unique rule.
         *
         * @param  \Illuminate\Database\Eloquent\Model|string|class-string  $table
         * @param  string  $column
         * @return \Laragear\Rut\Rules\NumUnique
         */
        public static function numUnique(Model|string $table, $column = 'NULL'): NumUnique
        {
            //
        }
    }
}