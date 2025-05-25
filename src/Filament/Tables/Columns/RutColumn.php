<?php

namespace Laragear\Rut\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laragear\Rut\Rut;
use Laragear\Rut\RutFormat;
use function data_get;

class RutColumn extends TextColumn
{
    protected ?RutFormat $formatAs = null;

    /**
     * @var "string"|"array"|"int"
     */
    protected string $from = 'string';

    protected function formatAs(RutFormat $format): static
    {
        $this->formatAs = $format;

        return $this;
    }

    public function formatAsStrict(): static
    {
        return $this->formatAs(RutFormat::Strict);
    }

    public function formatAsBasic(): static
    {
        return $this->formatAs(RutFormat::Basic);
    }

    public function formatAsRaw(): static
    {
        return $this->formatAs(RutFormat::Raw);
    }

    public function getFormatAs(): ?RutFormat
    {
        return $this->formatAs;
    }

    protected function from(string $type): static
    {
        $this->from = $type;

        return $this;
    }

    public function fromRutArray(): static
    {
        return $this->from('array');
    }

    public function fromRutNumber(): static
    {
        return $this->from('int');
    }

    public function getFromRutData(): string
    {
        return $this->from;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getStateUsing = static function (self $column): ?string {
            if (!$data = $column->getStateFromRecord()) {
                return null;
            }

            $data = match($column->getFromRutData()) {
                'array' => new Rut(...Arr::wrap($data)),
                'int' => Rut::fromNum($data),
                default => Rut::parse($data),
            };

            return $data->format($column->getFormatAs());
        };
    }
}
