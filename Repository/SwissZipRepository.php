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
        $result = $this->createQueryBuilder('swiss_zip')
            ->where('swiss_zip.postleitzahl = :zip')
            ->orderBy('swiss_zip.plzZz', 'asc')
            ->setParameter('zip', $zip)
            ->getQuery()
            ->enableResultCache()
            ->getResult();

        return $result ? $result : [];
    }

    /**
     * @return array|SwissZipInterface[]
     */
    public function findSuggested(string $input): ?array
    {
        $result = $this->createQueryBuilder('swiss_zip')
            ->where('LOWER(swiss_zip.ortbez27) LIKE :inputOrt')
            ->orWhere('swiss_zip.postleitzahl LIKE :inputPlz')
            ->setParameter('inputOrt', strtolower('%' . $input . '%'))
            ->setParameter('inputPlz', strtolower('%' . $input . '%'))
            ->getQuery()
            ->enableResultCache()
            ->getResult()
        ;

        if ($result) {
            usort($result, function (SwissZipInterface $a, SwissZipInterface $b) use ($input) {
                if (strtolower($b->getOrtbez27()) == strtolower($input) &&
                    strtolower($a->getOrtbez27()) != strtolower($input)) {
                    return 1;
                }
                if (strtolower($a->getOrtbez27()) == strtolower($input) &&
                    strtolower($b->getOrtbez27()) != strtolower($input)) {
                    return -1;
                }

                $result = strcmp($a->getOrtbez27(), $b->getOrtbez27());

                if (0 == $result) {
                    return strcmp($a->getPostleitzahl(), $b->getPostleitzahl());
                }

                return $result;
            });
        }

        return $result ? $result : [];
    }
}
