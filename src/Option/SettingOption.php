<?php

declare(strict_types=1);

namespace Persist\Option;

use Improved as i;

/**
 * Generic setting for the query builder.
 *
 * Example for getting the value of the `turns` setting with `1` as default value.
 *
 *     $setting = opt\setting('turns', 1)->findIn($opts);
 *
 */
class SettingOption implements OptionInterface
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
     * Get the value of the setting from opts.
     *
     * Returns `null` if the setting isn't present in opts.
     * If a setting option with the name appears twice in opts, the last value is given.
     *
     * {@internal Not using iterable pipeline to optimize performance.}}
     *
     * @param OptionInterface[] $opts
     * @param string|null       $type  Value must be of this (internal) type or class name.
     * @return mixed
     */
    public function findIn(array $opts, ?string $type = null)
    {
        foreach (array_reverse($opts) as $opt) {
            $found = $opt instanceof self &&
                $opt->getName() === $this->name &&
                ($type === null || i\type_is($opt->getValue(), $type));

            if ($found) {
                return $opt->getValue();
            }
        }

        return $this->value;
    }
}
