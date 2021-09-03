<?php

namespace whatwedo\SwissZip\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use whatwedo\SwissZip\Dto\UpdateReportDto;
use whatwedo\SwissZip\Event\Event;
use whatwedo\SwissZip\Repository\SwissZipRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use whatwedo\SwissZip\Entity\SwissZipInterface;

class SwissZipUpdateManager
{
    private KernelInterface $kernel;
    private SwissZipRepository $swissZipRepository;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
        $entityClass = $this->getSwissZipEntity();
        $this->swissZipRepository = $entityManager->getRepository($entityClass);
        $this->eventDispatcher = $eventDispatcher;
    }

    public function update(bool $delete = false): UpdateReportDto
    {
        $entityClass = $this->getSwissZipEntity();
        $updateReport = new UpdateReportDto();
        if ($delete) {
            $this->deleteEntities($updateReport);
        }


        foreach ($this->getData() as $dataSet) {
            $isNew = false;
            /** @var SwissZipInterface $swissZip */
            $swissZip = $this->swissZipRepository->find($dataSet->onrp);

            if (!$swissZip) {
                $swissZip = new $entityClass;

                $event = new Event($swissZip, $updateReport);
                $this->eventDispatcher->dispatch(
                    $event,
                    Event::CREATE
                );

                if ($event->isBlocked()) {
                    continue;
                }
                $isNew = true;
            }

            $swissZip->setOnrp($dataSet->onrp);
            $swissZip->setPostleitzahl($dataSet->postleitzahl);
            $swissZip->setPlzZz($dataSet->plz_zz);
            $swissZip->setOrtbez18($dataSet->ortbez18);
            $swissZip->setOrtbez27($dataSet->ortbez27);
            $swissZip->setKanton($dataSet->kanton);
            $swissZip->setPlzTyp($dataSet->plz_typ);
            $swissZip->setSprachcode($dataSet->sprachcode);
            $swissZip->setValidFrom(new \DateTimeImmutable($dataSet->gilt_ab_dat));

            $eventUpdate = new Event($swissZip, $updateReport);
            $this->eventDispatcher->dispatch(
                $eventUpdate,
                Event::UPDATE
            );

            if ($eventUpdate->isBlocked()) {
                $updateReport->skipped++;
                continue;
            }

            if ($isNew) {

                $eventPersist = new Event($swissZip, $updateReport);
                $this->eventDispatcher->dispatch(
                    $eventPersist,
                    Event::PERSIST
                );
                if ($eventPersist->isBlocked()) {
                    continue;
                }
                $this->entityManager->persist($swissZip);

                $updateReport->inserted++;
            } else {
                $updateReport->updated++;
            }
        }


        $this->entityManager->flush();

        return $updateReport;
    }

    private function getSwissZipEntity(): string
    {
        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            if (in_array(SwissZipInterface::class, class_implements($meta->getName()))) {
                return $meta->getName();
            }
        }
        throw new \Exception('no Entity implements the interface SwissZipInterface');
    }

    /**
     * @return mixed
     */
    private function getData(): \Generator
    {
        $results = [];

        $row = 1000;
        $start = 0;

        do {
            $location = sprintf('https://swisspost.opendatasoft.com/api/records/1.0/search/?dataset=plz_verzeichnis_v2&q=&rows=%s&start=%s',
                $row,
                $row * $start
            );
            $contents = file_get_contents($location);
            $zipData = json_decode($contents);

            foreach ($zipData->records as $dataSet) {
                $isNew = false;
                if (!isset($dataSet->fields->plz_coff)) {
                    continue;
                }
                yield $dataSet->fields;
            }


            $start++;
        } while (count($zipData->records) != 0);

        return $results;
    }


    /**
     * @param UpdateReportDto $updateReport
     */
    private function deleteEntities(UpdateReportDto $updateReport): void
    {
        $dql = sprintf('select s from %s s', $this->getSwissZipEntity());
        $query = $this->entityManager->createQuery($dql);
        foreach ($query->toIterable() as $item) {

            $event = new Event($item, $updateReport);
            $this->eventDispatcher->dispatch(
                $event,
                Event::DELETE
            );
            if ($event->isBlocked()) {
                $updateReport->skipped++;
                continue;
            }


            $this->entityManager->remove($item);


            $updateReport->deleted++;
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }


}