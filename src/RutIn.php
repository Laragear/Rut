<?php

namespace Laragear\Rut;

class RutIn
{
    /**
     * Create a new Model Rut instance.
     */
    public function __construct(
        public string $num,
        public ?string $vd = null,
        public bool $isAppendable = false,
        public bool $isQueryable = false,
        public ?bool $isShowingColumns = null,
    ) {
        //
    }

    /**
     * Should the RUT attribute be appended to the model on serialization.
     *
     * @return $this
     */
    public function append(): static
    {
        $this->isAppendable = true;

        return $this;
    }

    /**
     * Should the columns based for the RUT columns be hidden from serialization.
     *
     * @return $this
     */
    public function showColumns(): static
    {
        $this->isShowingColumns = true;

        return $this;
    }

    /**
     * Should the columns based for the RUT columns be hidden from serialization.
     *
     * @return $this
     */
    public function hideColumns(): static
    {
        $this->isShowingColumns = false;

        return $this;
    }

    /**
     * Should this RUT be used to query in the database through the included local scopes.
     *
     * @return $this
     */
    public function queryable(): static
    {
        $this->isQueryable = true;

        return $this;
    }

    /**
     * Creates a new RUT configuration for the model using the columns where it should be stored.
     */
    public static function columns(string $num, ?string $vd = null): static
    {
        return new static($num, $vd);
    }

    /**
     * Creates a new RUT configuration for the model using only the column for the number.
     */
    public static function column(string $num): static
    {
        return static::columns($num);
    }

    /**
     * Create a new RUT configuration from a single string using conventions.
     *
     * @param  string  $attribute
     * @return static
     */
    public static function fromString(string $attribute): static
    {
        return new static("{$attribute}_num", "{$attribute}_vd", true, true);
    }
}
