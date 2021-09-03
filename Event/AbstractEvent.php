<?php

namespace whatwedo\SwissZip\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use whatwedo\SwissZip\Dto\UpdateReportDto;
use whatwedo\SwissZip\Entity\SwissZipInterface;

abstract class AbstractEvent extends GenericEvent
{
    private bool $block = false;
    private UpdateReportDto $updateReport;

    public function __construct(SwissZipInterface $entity, UpdateReportDto $updateReport)
    {
        parent::__construct($entity, []);
        $this->updateReport = $updateReport;
    }

    /**
     * @return SwissZipInterface
     */
    public function getSubject(): SwissZipInterface
    {
        return parent::getSubject();
    }

    public function isBlocked(): bool
    {
        return $this->block;
    }

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