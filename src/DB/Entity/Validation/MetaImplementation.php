<?php

namespace Jasny\DB\Entity\Validation;

use Jasny\ValidationResult,
    Jasny\DB\Entity\ChangeAware,
    Jasny\DB\Entity\SelfAware;

/**
 * Validate using meta
 */
trait MetaImplementation
{
    /**
     * Validate entity
     * 
     * @return ValidationResult
     */
    public function validate()
    {
        $validation = new ValidationResult();
        
        foreach (static::meta()->ofProperties() as $prop => $meta) {
            if ($this instanceof ChangeAware && !$this->isNew() && !$this->hasModified($prop)) {
                continue;
            }
            
            $validation->add($this->validateProperty($prop, $meta));
        }
        
        return $validation;
    }
    
    /**
     * Validate a property
     * 
     * @param string      $prop
     * @param \Jasny\Meta $meta
     * @return ValidationResult
     */
    public function validateProperty($prop, $meta)
    {
        $validation = new ValidationResult();
        
        if (isset($meta['required']) && !isset($this->$prop)) {
            $validation->addError("%s is required", $prop);
        }

        if (!isset($this->$prop)) {
            return $validation;
        }
        
        if (isset($meta['unique'])) {
            $uniqueGroup = is_string($meta['unique']) ? $meta['unique'] : null;

            if (!$this instanceof SelfAware) {
                trigger_error(static::class . " can't check if it has a unique $prop", E_USER_WARNING);
            } elseif (!$this->hasUnique($prop, $uniqueGroup)) {
                $validation->addError("There is already a %s with this %s", get_class($this), $prop);
                return $validation;
            }
        }

        if (isset($meta['immutable'])) {
            if (!$this instanceof ChangeAware) {
                trigger_error(static::class . " can't check if $prop has changed", E_USER_WARNING);
            } elseif (!$this->isNew()) {
                $validation->addError("%s shouldn't be modified", $prop);
                return $validation;
            }
        }

        $validation->add($this->validateBasics($prop, $meta));
        
        return $validation;
 }
    
    /**
     * Perform basic validation
     * 
     * @param string      $prop
     * @param \Jasny\Meta $meta
     * @return ValidationResult
     */
    protected function validateBasics($prop, $meta)
    {
        $validation = new ValidationResult();

        if (isset($meta['min']) && $this->$prop < $meta['min']) {
            $validation->addError("%s should be at least %s", $prop, $meta['min']);
        }

        if (isset($meta['max']) && $this->$prop > $meta['max']) {
            $validation->addError("%s should no at most %s", $prop, $meta['max']);
        }

        if (isset($meta['minLength']) && strlen($this->$prop) > $meta['minLength']) {
            $validation->addError("%s should be at least %d characters", $prop, $meta['minLength']);
        }

        if (isset($meta['maxLength']) && strlen($this->$prop) > $meta['maxLength']) {
            $validation->addError("%s should be at most %d characters", $prop, $meta['maxLength']);
        }
        
        if (isset($meta['options'])) {
            $options = array_map('trim', explode(',', $meta['options']));
            if (!in_array($this->$prop, $options)) {
                $validation->addError("%s should be one of: %s", $prop, $meta['options']);
            }
        }
        
        if (isset($meta['type']) && !$this->validateType($prop, $meta['type'])) {
            $validation->addError("%s isn't a valid %s", $prop, $meta['type']);
        }

        if (isset($meta['pattern']) && !$this->validatePattern($prop, $meta['pattern'])) {
            $validation->addError("%s isn't valid", $prop);
        }
        
        return $validation;
    }
        
    
    /**
     * Validate for a property type
     * 
     * @param string $prop
     * @param string $type
     * @return boolean
     */
    protected function validateType($prop, $type)
    {
        $value = $this->$prop;
        
        switch ($type) {
            case 'color':
                return strlen($value) === 7 && $value[0] === '#' && ctype_xdigit(substr($value, 1));
            case 'number':
                return is_int($value) || ctype_digit((string)$value);
            case 'range':
                return is_numeric($value);
            case 'url':
                $pos = strpos($value, ':');
                return $pos !== false && ctype_alpha(substr($value, 0, $pos));
            case 'email':
                return preg_match('/^[\w\-\.\+]+@[\w\-\.]*\w$/', $value);
            
            default:
                trigger_error("Unknown property type '$type'", E_USER_WARNING);
                return true;
        }
    }
    
    /**
     * Validate the value of the control against a regex pattern.
     * 
     * @param string $prop
     * @param string $pattern
     * @return boolean
     */
    protected function validatePattern($prop, $pattern)
    {
        return preg_match('/^(?:' . str_replace('/', '\/', $pattern) . ')$/', $this->$prop);
    }
}
