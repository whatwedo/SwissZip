<?php

namespace whatwedo\SwissZip\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use whatwedo\SwissZip\Dto\UpdateReportDto;
use whatwedo\SwissZip\Event\Event;
use whatwedo\SwissZip\Repository\SwissZipRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use whatwedo\SwissZip\Entity\SwissZipInterface;

class SwissZipManager
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
        $dryRun = false;
        $online = true;
        $entityClass = $this->getSwissZipEntity();
        $updateReport = new UpdateReportDto();
        if ($delete) {
            $this->deleteEntities($updateReport, $dryRun);
        }

        $dataLocation = $this->getDataLocation($online);
        $updateReport->location = $dataLocation;
        $zipData = $this->getData($dataLocation);

        foreach ($zipData->records as $dataSet) {
            $isNew = false;
            if (isset($dataSet->fields->plz_coff) && $dataSet->fields->plz_coff == 'J') {
                continue;
            }
            /** @var SwissZipInterface $swissZip */
            $swissZip = $this->swissZipRepository->find($dataSet->fields->onrp);

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

            $swissZip->setOnrp($dataSet->fields->onrp);
            $swissZip->setPostleitzahl($dataSet->fields->postleitzahl);
            $swissZip->setPlzZz($dataSet->fields->plz_zz);
            $swissZip->setOrtbez18($dataSet->fields->ortbez18);
            $swissZip->setOrtbez27($dataSet->fields->ortbez27);
            $swissZip->setKanton($dataSet->fields->kanton);
            $swissZip->setPlzTyp($dataSet->fields->plz_typ);
            $swissZip->setSprachcode($dataSet->fields->sprachcode);
            $swissZip->setValidFrom(new \DateTimeImmutable($dataSet->fields->gilt_ab_dat));

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
                if (!$dryRun) {
                    $this->entityManager->persist($swissZip);
                }

                $updateReport->inserted++;
            } else {
                $updateReport->updated++;
            }
        }


        if (!$dryRun) {
            $this->entityManager->flush();
        }

        return $updateReport;
    }


    /**
     * @return SwissZipInterface[]
     */
    public function suggest(string $input): array
    {
        $result = $this->swissZipRepository->findSuggested($input);

        if ($result) {
            usort($result, function (SwissZipInterface $a, SwissZipInterface $b) use ($input) {
                if (strtolower($b->getOrtbez27()) == strtolower($input)) {
                    return strcmp($a->getPostleitzahl(), $b->getPostleitzahl());
                }

                return strcmp($a->getOrtbez27(), $b->getOrtbez27());
            });
        }

        return $result ? $result : [];
    }


    /**
     * @return SwissZipInterface[]
     */
    public function find(string $zip): array
    {
        $result = $this->swissZipRepository->findByZip($zip);

        return $result ? $result : [];
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
    private function getData(string $location): object
    {

        // use yeild


        $contents = file_get_contents($location);
        $data = json_decode($contents);

        return $data;
    }

    /**
     * @param bool $online
     * @return string
     */
    public function getDataLocation(bool $online): string
    {
        if ($online) {
            $location = 'https://swisspost.opendatasoft.com/api/records/1.0/search/?dataset=plz_verzeichnis_v2&q=&rows=10000';
        } else {
            $dir = $this->kernel->locateResource('@whatwedoSwissZipBundle/Resources/data');
            $location = $dir . '/plz_verzeichnis_v2.json';

        }
        return $location;
    }

    /**
     * @param UpdateReportDto $updateReport
     */
    private function deleteEntities(UpdateReportDto $updateReport, bool $dryRun): void
    {
        foreach ($this->swissZipRepository->createQueryBuilder()->getQuery()->toIterable() as $item) {
            $event = new Event($item, $updateReport);
            $this->eventDispatcher->dispatch(
                $event,
                Event::DELETE
            );
            if ($event->isBlocked()) {
                $updateReport->skipped++;
                continue;
            }


            if (!$dryRun) {
                $this->entityManager->remove($item);
            }


            $updateReport->deleted++;
        }
        if (!$dryRun) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }


}