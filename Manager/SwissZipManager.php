<?php

namespace whatwedo\SwissZip\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use whatwedo\SwissZip\Dto\UpdateReport;
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

    public function update(bool $delete = false, bool $online = false): UpdateReport
    {
        $entityClass = $this->getSwissZipEntity();
        $updateReport = new UpdateReport();
        if ($delete) {
            foreach ($this->swissZipRepository->findAll() as $item) {
                $this->entityManager->remove($item);
                $this->eventDispatcher->dispatch(
                    new Event($item),
                    Event::DELETE
                );
                $updateReport->deleted++;
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $dataLoction = $this->getLocation($online);
        $updateReport->location = $dataLoction;
        $zipData = $this->getData($dataLoction);

        foreach ($zipData->records as $dataSet) {
            $isNew = false;
            if (isset($dataSet->fields->plz_coff) && $dataSet->fields->plz_coff = 'J') {
                /** @var SwissZipInterface $swissZip */
                $swissZip = $this->swissZipRepository->find($dataSet->recordid);

                if (!$swissZip) {
                    $swissZip = new $entityClass;

                    $this->eventDispatcher->dispatch(
                        new Event($swissZip),
                        Event::CREATE
                    );

                    $isNew = true;
                }

                $swissZip->setId($dataSet->recordid);
                $swissZip->setPostleitzahl($dataSet->fields->postleitzahl);
                $swissZip->setPlzZz($dataSet->fields->plz_zz);
                $swissZip->setOrtbez18($dataSet->fields->ortbez18);
                $swissZip->setOrtbez27($dataSet->fields->ortbez27);
                $swissZip->setKanton($dataSet->fields->kanton);
                $swissZip->setPlzTyp($dataSet->fields->plz_typ);
                $swissZip->setSprachcode($dataSet->fields->sprachcode);
                $swissZip->setValidFrom(new \DateTimeImmutable($dataSet->fields->gilt_ab_dat));

                $this->eventDispatcher->dispatch(
                    new Event($swissZip),
                    Event::UPDATE
                );

                if ($isNew) {

                    $this->eventDispatcher->dispatch(
                        new Event($swissZip),
                        Event::PERSIST
                    );
                    $this->entityManager->persist($swissZip);
                    $updateReport->inserted++;
                } else {
                    $updateReport->updated++;
                }
            }
        }
        $this->entityManager->flush();
        return $updateReport;
    }


    /**
     * @return SwissZipInterface[]
     */
    public function suggest(string $input): array
    {
        $result = $this->swissZipRepository->findSuggested($input);

        if ($result)  {
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


    private function getSwissZipEntity(): string  {
        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            if (in_array(SwissZipInterface::class, class_implements($meta->getName()))) {
                return $meta->getName();
            }
        }
        throw new \Exception('no Entity implents the interface SwissZipInterface');
    }

    /**
     * @return mixed
     */
    private function getData(string $location): object
    {
        $data = json_decode(file_get_contents($location));
        return $data;
    }

    /**
     * @param bool $online
     * @return string
     */
    private function getLocation(bool $online): string
    {
        if ($online) {
            $location = 'https://swisspost.opendatasoft.com/api/records/1.0/search/?dataset=plz_verzeichnis_v2&q=&rows=10000';
        } else {
            $dir = $this->kernel->locateResource('@whatwedoSwissZipBundle/Resources/data');
            $location = $dir . '/plz_verzeichnis_v2.json';

        }
        return $location;
    }


}