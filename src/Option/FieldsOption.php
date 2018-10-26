<?php declare(strict_types=1);

namespace Jasny\DB\Option;

/**
 * Only return the specified fields.
 */
class FieldsOption implements QueryOptionInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $fields;

    /**
     * Class constructor.
     *
     * @param string   $type
     * @param string[] $fields
     */
    public function __construct(string $type, array $fields)
    {
        $this->type = $type;
        $this->fields = $fields;
    }


    /**
     * Get the option type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
}
