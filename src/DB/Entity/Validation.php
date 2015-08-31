<?php

namespace Jasny\DB\Entity;

/**
 * Entity supports validation
 */
interface Validation extends \Jasny\DB\Entity
{
    /**
     * Validate the entity
     * 
     * @return Jasny\ValidationResult
     */
    public function validate();
}
