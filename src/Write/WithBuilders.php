<?php declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\QueryBuilder;

/**
 * Service has query builders for writing to data store.
 */
interface WithBuilders
{
    /**
     * Create a Writer service with a custom filter query builder.
     *
     * @param QueryBuilder $builder
     * @return static
     */
    public function withQueryBuilder(QueryBuilder $builder);

    /**
     * Create a Writer service with a custom builder pipeline for save.
     *
     * @param QueryBuilder $builder
     * @return static
     */
    public function withSaveQueryBuilder(QueryBuilder $builder);

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param QueryBuilder $builder
     * @return static
     */
    public function withUpdateQueryBuilder(QueryBuilder $builder);
}
