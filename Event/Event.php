<?php

namespace whatwedo\SwissZip\Event;

use whatwedo\SwissZip\Entity\SwissZipInterface;

class Event
{

    public const UPDATE = 'swisszip.update.update';
    public const CREATE = 'swisszip.update.create';
    public const DELETE = 'swisszip.update.delete';
    public const PERSIST = 'swisszip.update.persit';

    private SwissZipInterface $entity;

    public function __construct(SwissZipInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return SwissZipInterface
     */
    public function getEntity(): SwissZipInterface
    {
        return $this->entity;
    }

}