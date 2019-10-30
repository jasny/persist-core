<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Improved as i;

/**
 * Generic setting for the query builder.
 *
 * Example for getting the value of the `turns` setting with `1` as default value.
 *
 *     $setting = opts\setting('turns', 1)->findIn($opts);
 *
 * @immutable
 */
class SettingOption
{
    protected string $name;

    /** @var mixed $value */
    protected $value;

    /**
     * FlagOption constructor.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get the setting name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the setting name.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the value of the setting from the set.
     * Returns `null` if the setting isn't present in the set
     *
     * @param OptionInterface[] $opts
     * @return mixed
     */
    public function findIn(array $opts)
    {
        $opt = i\iterable_find($opts, (fn($opt) => $opt instanceof self && $opt->getName() === $this->name));

        return $opt !== null ? $opt->getValue() : $this->value;
    }
}
