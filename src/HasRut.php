<?php

declare(strict_types=1);

namespace Laragear\Rut;

use function is_string;

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
        foreach ($this->ruts() as $attribute => $rut) {
            if (is_string($rut)) {
                [$attribute, $rut] = [$rut, RutIn::fromString($rut)];
            }

            $this->mergeCasts([$attribute => Casts\CastRut::class]);

            if ($rut->isAppendable) {
                $this->append($attribute);
            }

            // If the visibility is not set, it's hiding columns except when is the primary key.
            $rut->isShowingColumns ?? $this->getKeyName() === $attribute
                ? $this->makeVisible($rut->num, $rut->vd)
                : $this->makeHidden($rut->num, $rut->vd);
        }
    }

    /**
     * Returns the attributes that should receive a RUT, mapped to the corresponding column.
     *
     * @return (\Laragear\Rut\RutIn|string)[]
     */
    public function ruts(): array
    {
        return [
            'rut',
        ];
    }
}
