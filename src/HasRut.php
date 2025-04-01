<?php

declare(strict_types=1);

namespace Laragear\Rut;

use function defined;

/**
 * @method static \Illuminate\Database\Eloquent\Collection|static[]|static|null findRut(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|static[] findManyRut(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|static[]|static findRutOrFail(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Model|static findRutOrNew(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, array|string $columns = ['*'])
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRut(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, string $boolean = 'and', bool $not = false)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRut(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, string $boolean = 'and')
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutNot(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, string $boolean = 'and')
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutNot(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, string $boolean = 'and', bool $not = false)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, bool $not = false)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutNotIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, string $boolean = 'and')
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutNotIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts)
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutIsPerson()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutIsPerson()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutIsInvestor()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutIsInvestor()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutIsInvestmentCompany()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutIsInvestmentCompany()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutIsContingency()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutIsContingency()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutIsCompany()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutIsCompany()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder whereRutIsTemporal()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder orWhereRutIsTemporal()
 *
 * @property-read \Laragear\Rut\Rut $rut
 */
trait HasRut
{
    /**
     * Boot the HasRut trait.
     *
     * @internal
     */
    public static function bootHasRut(): void
    {
        static::addGlobalScope(new Scopes\RutScope());
    }

    /**
     * Initialize the HasRut trait.
     *
     * @internal
     */
    public function initializeHasRut(): void
    {
        $this->mergeCasts(['rut' => Casts\CastRut::class]);

        if ($this->shouldAppendRut()) {
            $this->append('rut');

            if ($this->shouldShowPrimaryKeyIfIsRutNum()) {
                $this->makeHidden($this->getRutVdColumn());
            } else {
                $this->makeHidden($this->getRutNumColumn(), $this->getRutVdColumn());
            }
        }
    }

    /**
     * If the `rut` key should be appended, while hiding the underlying RUT columns.
     */
    public function shouldAppendRut(): bool
    {
        return true;
    }

    /**
     * If the primary key of the model should be hidden if it's the RUT Num.
     */
    public function shouldShowPrimaryKeyIfIsRutNum(): bool
    {
        return $this->getKeyName() === $this->getRutNumColumn();
    }

    /**
     * Get the name of the "rut number" column.
     */
    public function getRutNumColumn(): string
    {
        return defined(static::class.'::RUT_NUM') ? static::RUT_NUM : 'rut_num';
    }

    /**
     * Get the name of the "rut verification digit" column.
     */
    public function getRutVdColumn(): string
    {
        return defined(static::class.'::RUT_VD') ? static::RUT_VD : 'rut_vd';
    }

    /**
     * Get the fully qualified "rut number" column.
     */
    public function getQualifiedRutNumColumn(): string
    {
        return $this->qualifyColumn($this->getRutNumColumn());
    }
}
