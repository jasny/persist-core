<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

/**
 * Only return specified fields.
 * @immutable
 */
class FieldsOption implements OptionInterface
{
    protected array $fields;
    protected bool $negate;

    /**
     * Class constructor.
     *
     * @param string[] $fields
     * @param bool     $negate  Exclude the fields instead.
     */
    public function __construct(array $fields, bool $negate = false)
    {
        $this->fields = $fields;
        $this->negate = $negate;
    }

    /**
     * Get the fields
     *
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * The fields should be excluded instead of included.
     */
    public function isNegated(): bool
    {
        return $this->negate;
    }
}
