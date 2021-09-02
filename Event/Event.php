<?php

namespace whatwedo\SwissZip\Event;

use whatwedo\SwissZip\Dto\UpdateReportDto;
use whatwedo\SwissZip\Entity\SwissZipInterface;

class Event
{

    public const UPDATE = 'swisszip.update.update';
    public const CREATE = 'swisszip.update.create';
    public const DELETE = 'swisszip.update.delete';
    public const PERSIST = 'swisszip.update.persist';

    private SwissZipInterface $entity;
    private bool $block = false;
    private UpdateReportDto $updateReport;

    public function __construct(SwissZipInterface $entity, UpdateReportDto $updateReport)
    {
        $this->entity = $entity;
        $this->updateReport = $updateReport;
    }

    /**
     * @return SwissZipInterface
     */
    public function getEntity(): SwissZipInterface
    {
        return $this->entity;
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->block;
    }

    /**
     * @param bool $block
     * @return Event
     */
    public function setBlock(bool $block): self
    {
        $this->block = $block;
        return $this;
    }

    public function getUpdateReport(): UpdateReportDto
    {
        return $this->updateReport;
    }

}