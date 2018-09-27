<?php

declare(strict_types=1);

namespace Jasny\DB\Exception;

/**
 * Could not fetch the entity based on specified id or filter
 */
class EntityNotFoundException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var mixed
     */
    protected $entityId;


    /**
     * Class constructor.
     *
     * @param string          $entityClass
     * @param mixed           $entityId
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $entityClass, mixed $entityId, int $code = 0, ?\Throwable $previous = null)
    {
        $this->entityClass = $entityClass;
        $this->entityId = $entityId;

        $message = sprintf("%s not found", $this->getEntityDescription());
        parent::__construct($message, $code, $previous);
    }


    /**
     * Get the class name of the entity that was not found.
     *
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * Get the id of the entity that was not found.
     *
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Get the description of the requested entity. Typically 'ClassName "id"'.
     *
     * @return string
     */
    public function getEntityDescription(): string
    {
        $id = is_scalar($this->entityId) ? (string)$this->entityId : $this->entityId;

        return $this->entityClass . ' ' . json_encode($id);
    }
}
