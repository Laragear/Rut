<?php

declare(strict_types=1);

namespace Laragear\Rut;

use Closure;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Stringable;

use function array_reverse;
use function json_encode;
use function max;
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
     */
    public const MIN = 100000;

    /**
     * The maximum RUT number to be considered valid.
     */
    public const MAX = 200000000;

    /**
     * Where to draw the line between person and investor RUTs.
     *
     * @see https://www.sii.cl/documentos/resoluciones/2000b/reso5412.htm
     */
    public const INVESTOR_BASE = 46000000;

    /**
     * Where to draw the line between investor and investment companies RUTs.
     *
     * @see https://www.sii.cl/documentos/resoluciones/2000b/reso5412.htm
     */
    public const INVESTMENT_COMPANY_BASE = 47000000;

    /**
     * Where to draw the line between investment companies and contingency RUTs.
     *
     * @see https://www.sii.cl/documentos/resoluciones/2000b/reso5412.htm
     */
    public const CONTINGENCY_BASE = 48000000;

    /**
     * Where to draw the line between person and company RUTs.
     */
    public const COMPANY_BASE = 60000000;

    /**
     * Where to separate between company and temporal RUTs.
     */
    public const TEMPORAL_BASE = 100000000;

    /**
     * The default string format for the RUT.
     */
    public static RutFormat $format = RutFormat::DEFAULT;

    /**
     * Determine if all RUT should be uppercase at instancing.
     */
    public static bool $uppercase = true;

    /**
     * Use a callback to format the Rut instance as JSON.
     *
     * @var (\Closure(\Laragear\Rut\Rut):(string|array))|\Laragear\Rut\RutFormat|null
     */
    public static Closure|RutFormat|null $jsonFormat = null;

    /**
     * The RUT verification digit.
     */
    public readonly string $vd;

    /**
     * Create a new Rut instance.
     */
    final public function __construct(public readonly int $num, string $vd)
    {
        $this->vd = static::$uppercase ? strtoupper($vd) : strtolower($vd);
    }

    /**
     * Check if the RUT is below 46.000.000.
     */
    public function isPerson(): bool
    {
        return $this->num < static::INVESTMENT_COMPANY_BASE;
    }

    /**
     * Check if the RUT is between 46.000.000 and 46.999.999, inclusive.
     */
    public function isInvestor(): bool
    {
        return $this->num >= static::INVESTOR_BASE && $this->num < static::CONTINGENCY_BASE;
    }

    /**
     * Check if the RUT is between 47.000.000 and 47.999.999, inclusive.
     */
    public function isInvestmentCompany(): bool
    {
        return $this->num >= static::INVESTMENT_COMPANY_BASE && $this->num < static::CONTINGENCY_BASE;
    }

    /**
     * Check if the RUT is between 48.000.000 and 59.999.999, inclusive.
     */
    public function isContingency(): bool
    {
        return $this->num >= static::CONTINGENCY_BASE && $this->num < static::COMPANY_BASE;
    }

    /**
     * Check if the RUT is between 60.000.000 and 999.999.999, inclusive.
     */
    public function isCompany(): bool
    {
        return $this->num >= static::COMPANY_BASE && $this->num < static::TEMPORAL_BASE;
    }

    /**
     * Check if the RUT is between 100.000.000 and 199.999.999, inclusive.
     */
    public function isTemporal(): bool
    {
        return $this->num >= static::TEMPORAL_BASE && $this->num < static::MAX;
    }

    /**
     * Check if the current RUT is valid.
     */
    public function isValid(): bool
    {
        return static::check($this->num, $this->vd);
    }

    /**
     * Check if the current RUT is invalid.
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
     * Check if this RUT is equal to another RUT.
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
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public function isNotEqual(self|int|string $rut): bool
    {
        return ! $this->isEqual($rut);
    }

    /**
     * Formats the RUT to a string using the default style or the given style.
     */
    public function format(RutFormat $format = null): string
    {
        $format ??= static::$format;

        return $format->format($this);
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize(): mixed
    {
        return static::$jsonFormat instanceof Closure
            ? (static::$jsonFormat)($this)
            : $this->format(static::$jsonFormat);
    }

    /**
     * Returns the string representation of the RUT.
     *
     * @internal
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns the string representation of the RUT.
     */
    public function toString(): string
    {
        return $this->format();
    }

    /**
     * Returns the RUT as a JSON string.
     *
     * @param  int  $options
     */
    public function toJson($options = 0): false|string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Serializes the current RUT.
     *
     * @return array{0: string}
     *
     * @internal
     */
    public function __serialize(): array
    {
        return [RutFormat::Raw->format($this)];
    }

    /**
     * Creates a new instance from a serialized data array.
     *
     * @param  array{0: string}  $data
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
     * @throws \Laragear\Rut\Exceptions\InvalidRutException
     */
    public static function parse(self|string|int|null $rut): static
    {
        // No need to parse a Rut that is already a Rut object.
        return $rut instanceof static ? $rut : new static(...static::split($rut));
    }

    /**
     * Creates a collection of RUTs by parsing them.
     *
     * @param  iterable<array-key, string>  $ruts
     * @return \Illuminate\Support\Collection<array-key, \Laragear\Rut\Rut>
     */
    public static function map(iterable $ruts): Collection
    {
        return Collection::make($ruts)->map(static::parse(...));
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
     * @return array{int, string}
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
     * @return "1"|"2"|"3"|"4"|"5"|"6"|"7"|"8"|"9"|"0"|"K"
     */
    public static function getVd(int $num): string
    {
        $i = 2;
        $sum = 0;

        foreach (array_reverse(str_split((string) $num)) as $v) {
            if ($i === 8) {
                $i = 2;
            }
            $sum += (int) $v * $i;
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
     */
    public static function fromNum(int $num): static
    {
        return new static($num, static::getVd($num));
    }
}
