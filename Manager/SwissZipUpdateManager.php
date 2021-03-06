<?php

namespace whatwedo\SwissZip\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use whatwedo\SwissZip\Dto\UpdateReportDto;
use whatwedo\SwissZip\Entity\SwissZipInterface;
use whatwedo\SwissZip\Event\CreateEvent;
use whatwedo\SwissZip\Event\DeleteEvent;
use whatwedo\SwissZip\Event\UpdateEvent;
use whatwedo\SwissZip\Repository\SwissZipRepository;

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

    public function find(string $zip): array
    {
        return $this->swissZipRepository->findByZip($zip);
    }

    public function suggest(string $input): array
    {
        return $this->swissZipRepository->findSuggested($input);
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

            if (! $swissZip) {
                $swissZip = new $entityClass();

                $createEvent = new CreateEvent($swissZip, $updateReport);
                $this->eventDispatcher->dispatch($createEvent, CreateEvent::class);

                if ($createEvent->isBlocked()) {
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

            $updateEvent = new UpdateEvent($swissZip, $updateReport);
            $this->eventDispatcher->dispatch($updateEvent, UpdateEvent::class);

            if ($updateEvent->isBlocked()) {
                ++$updateReport->skipped;
                continue;
            }

            if ($isNew) {
                $this->entityManager->persist($swissZip);
                ++$updateReport->inserted;
            } else {
                ++$updateReport->updated;
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

        $row = 200;
        $start = 0;

        do {
            $location = sprintf(
                'https://swisspost.opendatasoft.com/api/records/1.0/search/?dataset=plz_verzeichnis_v2&q=&rows=%s&start=%s',
                $row,
                $row * $start
            );
            $contents = file_get_contents($location);
            $zipData = json_decode($contents);

            foreach ($zipData->records as $dataSet) {
                $isNew = false;
                if (! isset($dataSet->fields->plz_coff)) {
                    continue;
                }
                yield $dataSet->fields;
            }

            ++$start;
        } while (0 != count($zipData->records));

        return $results;
    }

    private function deleteEntities(UpdateReportDto $updateReport): void
    {
        $dql = sprintf('select s from %s s', $this->getSwissZipEntity());
        $query = $this->entityManager->createQuery($dql);
        foreach ($query->toIterable() as $item) {
            $deleteEvent = new DeleteEvent($item, $updateReport);
            $this->eventDispatcher->dispatch($deleteEvent, DeleteEvent::class);
            if ($deleteEvent->isBlocked()) {
                ++$updateReport->skipped;
                continue;
            }

            $this->entityManager->remove($item);

            ++$updateReport->deleted;
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
