<?php

declare(strict_types=1);

namespace Laragear\Rut;

use function defined;

/**
 * @method \Illuminate\Database\Eloquent\Collection|static[]|static|null findRut(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, array|string $columns = ['*'])
 * @method \Illuminate\Database\Eloquent\Collection|static[] findManyRut(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, array|string $columns = ['*'])
 * @method \Illuminate\Database\Eloquent\Collection|static[]|static findRutOrFail(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, array|string $columns = ['*'])
 * @method \Illuminate\Database\Eloquent\Model|static findRutOrNew(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, array|string $columns = ['*'])
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRut(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, string $boolean = 'and', bool $not = false)
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRut(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, string $boolean = 'and')
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutNot(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut, string $boolean = 'and')
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutNot(iterable|int|string|\Illuminate\Contracts\Support\Arrayable|\Laragear\Rut\Rut $rut)
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, string $boolean = 'and', bool $not = false)
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, bool $not = false)
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutNotIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts, string $boolean = 'and')
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutNotIn(iterable|\Illuminate\Contracts\Support\Arrayable $ruts)
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutIsPerson()
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutIsPerson()
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutIsInvestor()
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutIsInvestor()
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutIsInvestmentCompany()
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutIsInvestmentCompany()
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutIsContingency()
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutIsContingency()
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutIsCompany()
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutIsCompany()
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutIsTemporal()
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutIsTemporal()
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutLike(string $search, string $boolean = 'and', bool $not = false)
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutLike(string $search, bool $not = false)
 * @method \Illuminate\Database\Eloquent\Builder<static> whereRutNotLike(string $search, string $boolean = 'and')
 * @method \Illuminate\Database\Eloquent\Builder<static> orWhereRutNotLike(string $search)
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
