<?php

namespace Jasny\DB\Entity;

/**
 * Entity can be validate
 */
interface Validatable extends \Jasny\DB\Entity
{
    /**
     * Validate 
     */
    public function validate();
}
