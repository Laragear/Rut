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
 *
 * @property-read \Laragear\Rut\Rut $rut
 */
trait HasRut
{
    /**
     * Boot the HasRut trait.
     *
     * @return void
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
     * @return void
     *
     * @internal
     */
    public function initializeHasRut(): void
    {
        $this->mergeCasts(['rut' => Casts\CastRut::class]);

        if ($this->shouldAppendRut()) {
            $this->append('rut');
            $this->makeHidden($this->getRutNumColumn(), $this->getRutVdColumn());
        }
    }

    /**
     * If the `rut` key should be appended, while hidding the underlying RUT columns.
     *
     * @return bool
     */
    public function shouldAppendRut(): bool
    {
        return true;
    }

    /**
     * Get the name of the "rut number" column.
     *
     * @return string
     */
    public function getRutNumColumn(): string
    {
        return defined('static::RUT_NUM') ? static::RUT_NUM : 'rut_num';
    }

    /**
     * Get the name of the "rut verificarion digit" column.
     *
     * @return string
     */
    public function getRutVdColumn(): string
    {
        return defined('static::RUT_NUM') ? static::RUT_VD : 'rut_vd';
    }

    /**
     * Get the fully qualified "rut number" column.
     *
     * @return string
     */
    public function getQualifiedRutNumColumn(): string
    {
        return $this->qualifyColumn($this->getRutNumColumn());
    }
}
