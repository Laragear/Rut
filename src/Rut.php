<?php

declare(strict_types=1);

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
     * Sets RUT representation with only the characters.
     *
     * @example "187654321"
     */
    public const FORMAT_RAW = 0;

    /**
     * Sets RUT representation with basic format.
     *
     * @example "18765432-1"
     */
    public const FORMAT_BASIC = 1;

    /**
     * Sets RUT representation properly formatted.
     *
     * @example "18.765.432-1"
     */
    public const FORMAT_STRICT = 2;

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
     * @var int
     *
     * @internal
     */
    public static int $format = self::FORMAT_STRICT;

    /**
     * Determine if all RUT should be uppercase at instancing.
     *
     * @var bool
     *
     * @internal
     */
    public static bool $uppercase = true;

    /**
     * Use a callback to format the Rut instance as JSON.
     *
     * @var int|null
     */
    public static ?int $jsonFormat = null;

    /**
     * Create a new Rut instance.
     *
     * @param  int  $num
     * @param  string  $vd
     * @return void
     */
    public function __construct(public int $num, public string $vd)
    {
        $this->vd = static::$uppercase ? strtoupper($this->vd) : strtolower($this->vd);
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
     * Check if the RUT is equal or above 50.000.000;.
     *
     * @return bool
     */
    #[Pure]
    public function isCompany(): bool
    {
        return ! $this->isPerson();
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
        return ! $this->isValid();
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
     *
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
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public function isNotEqual(self|int|string $rut): bool
    {
        return ! $this->isEqual($rut);
    }

    /**
     * Formats the RUT into a given style.
     *
     * @param  int|null  $format
     * @return string
     */
    #[Pure]
    public function format(int $format = null): string
    {
        return match ($format ?? static::$format) {
            static::FORMAT_STRICT => $this->toStrictString(),
            static::FORMAT_BASIC => $this->toBasicString(),
            default => $this->toRawString(),
        };
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return static::$jsonFormat !== null
            ? $this->format(static::$jsonFormat)
            : $this->toString();
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
        return $this->format(static::$format);
    }

    /**
     * Returns the RUT as a JSON string.
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
     *
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
     *
     * @internal
     */
    public function __unserialize(array $data): void
    {
        [$num, $vd] = str_split($data[0], strlen($data[0]) - 1);

        [$this->num, $this->vd] = [(int) $num, $vd];
    }

    /**
     * Parse a RUT string or numbers.
     *
     * @param  \Laragear\Rut\Rut|string|int|null  $rut
     * @return static
     *
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function parse(self|string|int|null $rut): static
    {
        // No need to parse a Rut that is already a Rut object.
        if ($rut instanceof static) {
            return $rut;
        }

        return new static(...static::split($rut));
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
                [$num, $vd] = static::split((string) $num);
            } catch (Exceptions\EmptyRutException) {
                return false;
            }
        }

        return $num >= static::MIN
            && $num <= static::MAX
            && strtoupper((string) $vd) === static::getVd($num);
    }

    /**
     * Cleans and splits a RUT string into an array of the number and verification digit.
     *
     * @param  static|string|int|null  $string
     * @return array
     *
     * @throws \Laragear\Rut\Exceptions\EmptyRutException
     */
    public static function split(self|string|int|null $string): array
    {
        if ($string instanceof static) {
            return [$string->num, $string->vd];
        }

        $string = (string) $string;

        $string = preg_filter('/[^\dkK]/', '', $string) ?? $string;

        $rut = str_split($string, max(1, strlen($string) - 1));

        if (! isset($rut[1])) {
            throw new Exceptions\EmptyRutException(
                'The RUT needs at least 7 valid characters, '.strlen($string).' given.'
            );
        }

        return [(int) $rut[0], (string) $rut[1]];
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
            $sum += ((int) $v) * $i;
            $i++;
        }

        $digit = 11 - ($sum % 11);

        return (string) match ($digit) {
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
