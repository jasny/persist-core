<?php declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\QueryBuilder\QueryBuilding;

/**
 * Service has query builders for writing to data store.
 */
interface WithBuilders
{
    /**
     * Create a Writer service with a custom filter query builder.
     *
     * @param QueryBuilding $builder
     * @return static
     */
    public function withQueryBuilder(QueryBuilding $builder);

    /**
     * Create a Writer service with a custom builder pipeline for save.
     *
     * @param QueryBuilding $builder
     * @return static
     */
    public function withSaveQueryBuilder(QueryBuilding $builder);

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param QueryBuilding $builder
     * @return static
     */
    public function withUpdateQueryBuilder(QueryBuilding $builder);
}
