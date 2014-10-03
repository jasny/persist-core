<?php

namespace Jasny\DB;

/**
 * Specify entity as deletable
 */
interface Deletable
{
    /**
     * Delete the entity.
     */
    public function delete();
}
