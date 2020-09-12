<?php

declare(strict_types=1);

namespace Persist\Option;

/**
 * Only return specified fields.
 */
class FieldsOption implements OptionInterface
{
    /** @var string[] */
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
