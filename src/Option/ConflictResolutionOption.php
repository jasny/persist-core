<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

/**
 * How to handle existing items for save queries?
 *
 * - conflict - results in db error
 * - ignore   - skip existing
 * - replace  - replace existing, missing fields are removed or set to default value
 * - update   - update existing, missing fields are not changed
 *
 * @immutable
 */
class ConflictResolutionOption implements OptionInterface
{
    public const SUPPORTED = ['conflict', 'ignore', 'replace', 'update'];

    protected string $resolution;

    /**
     * ConflictResolutionOption constructor.
     */
    public function __construct(string $resolution)
    {
        if (!in_array($resolution, self::SUPPORTED, true)) {
            throw new \UnexpectedValueException("Unsupported conflict resolution option '$resolution'");
        }

        $this->resolution = $resolution;
    }

    /**
     * Get the conflict resolution.
     */
    public function getResolution(): string
    {
        return $this->resolution;
    }
}
