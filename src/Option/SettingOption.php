<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;

/**
 * Generic setting for the query builder.
 *
 * Example for getting the value of the `turns` setting with `1` as default value.
 *
 *     $setting = opts\setting('turns', 1)->findIn($opts);
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
     * @param OptionInterface[] $opts
     * @param string            $type  Value must be of this (internal) type or class name.
     * @return mixed
     */
    public function findIn(array $opts, ?string $type = null)
    {
        $value = Pipeline::with($opts)
            ->filter(fn($opt) => $opt instanceof self && $opt->getName() === $this->name)
            ->map(fn(self $opt) => $opt->getValue())
            ->filter(fn($value) => $type === null || i\type_is($value, $type))
            ->last();

        return $value ?? $this->value;
    }
}
