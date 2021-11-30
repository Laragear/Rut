<?php

namespace Laragear\Rut;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use JetBrains\PhpStorm\Pure;
use JsonSerializable;
use Stringable;

use function array_reverse;
use function json_encode;
use function max;
use function number_format;
use function preg_filter;
use function str_split;
use function strlen;
use function strtolower;
use function strtoupper;

class Rut implements JsonSerializable, Stringable, Jsonable
{
    use Macroable;

    /**
     * The minimum RUT number to be considered valid.
     *
     * @var int
     */
    public const MIN = 100000;

    /**
     * The maximum RUT number to be considered valid.
     *
     * @var int
     */
    public const MAX = 100000000;

    /**
     * Where to draw the line between person and company RUTs.
     *
     * @const int
     */
    public const COMPANY_BASE = 50000000;

    /**
     * The default string format for the RUT.
     *
     * @var \Laragear\Rut\Format
     * @internal
     */
    public static Format $format = Format::DEFAULT;

    /**
     * Determine if all RUT should be uppercase at instancing.
     *
     * @var bool
     * @internal
     */
    public static bool $uppercase = true;

    /**
     * The verification digit of the RUT.
     *
     * @var string
     */
    public readonly string $vd;

    /**
     * Create a new Rut instance.
     *
     * @param  int  $num
     * @param  string  $vd
     * @return void
     */
    public function __construct(public readonly int $num, string $vd)
    {
        $this->vd = static::$uppercase ? strtoupper($vd) : strtolower($vd);
    }

    /**
     * Check if the RUT is below 50.000.000.
     *
     * @return bool
     */
    public function isPerson(): bool
    {
        return $this->num < static::COMPANY_BASE;
    }

    /**
     * Check if the RUT is equal or above 50.000.000;
     *
     * @return bool
     */
    #[Pure]
    public function isCompany(): bool
    {
        return !$this->isPerson();
    }

    /**
     * Check if the current RUT is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return static::check($this->num, $this->vd);
    }

    /**
     * Check if the current RUT is invalid.
     *
     * @return bool
     */
    public function isInvalid(): bool
    {
        return !$this->isValid();
    }

    /**
     * Checks if the current RUT is valid, or throws an exception.
     *
     * @return $this
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public function validate(): static
    {
        return $this->isValid() ? $this : throw new Exceptions\InvalidRutException();
    }

    /**
     * Check if this RUT is equal to other RUT.
     *
     * @param  \Laragear\Rut\Rut|int|string  $rut
     * @return bool
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public function isEqual(self|int|string $rut): bool
    {
        $rut = static::parse($rut);

        return $this->num === $rut->num && $this->vd === $rut->vd;
    }

    /**
     * Check if this RUT is not equal to other RUT.
     *
     * @param  \Laragear\Rut\Rut|int|string  $rut
     * @return bool
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public function isNotEqual(self|int|string $rut): bool
    {
        return !$this->isEqual($rut);
    }

    /**
     * Formats the RUT into a given style.
     *
     * @param  \Laragear\Rut\Format|null  $format
     * @return string
     */
    #[Pure]
    public function format(Format $format = null): string
    {
        return match ($format ?? static::$format) {
            Format::Strict => $this->toStrictString(),
            Format::Basic  => $this->toBasicString(),
            default        => $this->toRawString(),
        };
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * Returns the string representation of the RUT.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns the RUT as a strictly formatted string.
     *
     * @return string
     */
    protected function toStrictString(): string
    {
        return number_format($this->num, 0, null, '.').'-'.$this->vd;
    }

    /**
     * Returns the RUT as a simple formatted string.
     *
     * @return string
     */
    protected function toBasicString(): string
    {
        return "$this->num-$this->vd";
    }

    /**
     * Returns the RUT as a raw string.
     *
     * @return string
     */
    protected function toRawString(): string
    {
        return "$this->num$this->vd";
    }

    /**
     * Returns the string representation of the RUT.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->format(static::$format ?? Format::DEFAULT);
    }

    /**
     * Returns the RUT as a JSON string
     *
     * @param  int  $options
     * @return false|string
     */
    public function toJson($options = 0): false|string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Serializes the current RUT.
     *
     * @return array
     * @internal
     */
    #[Pure]
    public function __serialize(): array
    {
        return [$this->toRawString()];
    }

    /**
     * Creates a new instance from a serialized data array.
     *
     * @param  array  $data
     * @internal
     */
    public function __unserialize(array $data): void
    {
        [$this->num, $this->vd] = str_split($data[0], strlen($data[0]) - 1);
    }

    /**
     * Parse a RUT string or numbers.
     *
     * @param  \Laragear\Rut\Rut|string|int|null  $rut
     * @return static
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function parse(self|string|int|null $rut): static
    {
        // No need to parse a Rut that is already an object.
        if ($rut instanceof static) {
            return $rut;
        }

        return (new static(...static::split($rut)));
    }

    /**
     * Creates a collection of RUTs by parsing them.
     *
     * @param  iterable<string>  $ruts
     * @return \Illuminate\Support\Collection
     */
    public static function map(iterable $ruts): Collection
    {
        return Collection::make($ruts)->map([__CLASS__, 'parse']);
    }

    /**
     * Check if the RUT string is valid.
     *
     * @param  int|string  $num
     * @param  int|string|null  $vd
     * @return bool
     */
    public static function check(int|string $num, int|string $vd = null): bool
    {
        // If the developer only issued the num, we will understand is the whole RUT.
        if (null === $vd) {
            try {
                [$num, $vd] = static::split($num);
            } catch (Exceptions\EmptyRutException) {
                return false;
            }
        }

        return $num >= static::MIN
            && $num <= static::MAX
            && strtoupper($vd) === static::getVd($num);
    }

    /**
     * Cleans and splits a RUT string into an array of the number and verification digit.
     *
     * @param  string|null  $string
     * @return array<int, string>
     *
     * @throws \Laragear\Rut\Exceptions\EmptyRutException
     */
    public static function split(string|null $string): array
    {
        $string = preg_filter('/(?!\d|k)./i', '', $string) ?? $string;

        $rut = str_split($string, max(1, strlen($string) - 1));

        if (!isset($rut[1])) {
            throw new Exceptions\EmptyRutException(
                'The RUT needs at least 7 valid characters, ' . strlen($string) . ' given.'
            );
        }

        return [(int) $rut[0], $rut[1]];
    }

    /**
     * Returns the Verification Digit from a RUT number.
     *
     * @param  int  $num
     * @return string
     */
    public static function getVd(int $num): string
    {
        $i = 2;
        $sum = 0;

        foreach (array_reverse(str_split((string) $num)) as $v) {
            if ($i === 8) {
                $i = 2;
            }
            $sum += $v * $i;
            ++$i;
        }

        $digit = 11 - ($sum % 11);

        return match ($digit) {
            11 => 0,
            10 => 'K',
            default => $digit,
        };
    }

    /**
     * Creates a new Rut from a number.
     *
     * @param  int  $num
     * @return static
     */
    #[Pure]
    public static function fromNum(int $num): static
    {
        return new static($num, static::getVd($num));
    }
}