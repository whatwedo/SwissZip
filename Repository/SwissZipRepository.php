<?php

namespace whatwedo\SwissZip\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use whatwedo\SwissZip\Entity\SwissZipInterface;

/**
 * @method SwissZipInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method SwissZipInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method SwissZipInterface[]    findAll()
 * @method SwissZipInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class SwissZipRepository extends ServiceEntityRepository implements SwissZipRepositoryInterface
{
    /**
     * @return array|SwissZipInterface[]
     */
    public function findByZip(string $zip): ?array
    {
        return $this->createQueryBuilder('swiss_zip')
            ->where('swiss_zip.postleitzahl = :zip')
            ->orderBy('swiss_zip.plzZz', 'asc')
            ->setParameter('zip', $zip)
            ->getQuery()
            ->enableResultCache()
            ->getResult();
    }

    /**
     * @return array|SwissZipInterface[]
     */
    public function findSuggested(string $input): ?array
    {
        return $this->createQueryBuilder('swiss_zip')

            ->where('LOWER(swiss_zip.ortbez27) LIKE :inputOrt')
            ->orWhere('swiss_zip.postleitzahl LIKE :inputPlz')
            ->orderBy('swiss_zip.ortbez27', 'asc')
            ->addOrderBy('swiss_zip.postleitzahl', 'asc')
            ->addOrderBy('swiss_zip.plzZz', 'asc')
            ->setParameter('inputOrt', strtolower('%'.$input.'%'))
            ->setParameter('inputPlz', strtolower('%'.$input.'%'))
            ->getQuery()
            ->enableResultCache()
            ->getResult()
            ;
    }


}
