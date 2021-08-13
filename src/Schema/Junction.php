<?php

declare(strict_types=1);

namespace Jasny\Persist\Schema;

/**
 * Relationship using a junction table.
 *
 * new Relationship(
 *   'team',
 *   'user',
 *   new Junction(['id' => 'team_id'], 'team_member', ['user_id' => 'id'])
 */
final class Junction implements JoinInterface
{
    protected string $table;
    protected Join $left;
    protected Join $right;

    /**
     * @param array<string,string>|Join $left   Join for the left-hand table to the junction table
     * @param string                    $table  Junction table name
     * @param array<string,string>|Join $right  Join for the junction table to the rightt-hand table
     */
    public function __construct(array|Join $left, string $table, array|Join $right)
    {
        $this->table = $table;
        $this->left = $left instanceof Join ? $left : new Join($left);
        $this->right = $right instanceof Join ? $right : new Join($right);
    }

    /**
     * Get the junction table name.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the join for the left table to the junction table.
     */
    public function getLeft(): Join
    {
        return $this->left;
    }

    /**
     * Get the join for the junction table to the right table.
     */
    public function getRight(): Join
    {
        return $this->right;
    }

    /**
     * @inheritDoc
     */
    public function swapped(): static
    {
        return new self(
            $this->right->swapped(),
            $this->table,
            $this->left->swapped(),
        );
    }

    /**
     * @inheritDoc
     */
    public function isOnField(string ...$fields): bool
    {
        return $this->left->isOnField(...$fields);
    }
}
