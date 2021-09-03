<?php

namespace whatwedo\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SwissZip\Entity\SwissZipInterface;
use whatwedo\SwissZip\Manager\SwissZipManager;
use whatwedo\SwissZip\Repository\SwissZipRepository;

class SwissZipTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    private function getRopo(): SwissZipRepository
    {
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $metas = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            if (in_array(SwissZipInterface::class, class_implements($meta->getName()))) {
                $className = $meta->getName();
                break;
            }
        }

        return $entityManager->getRepository($className);
    }

    public function testByExistingZip() {

        $locations = $this->getRopo()->findByZip('3011');
        $this->assertCount(1, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
    }

    public function testMultipleZip() {

        $locations = $this->getRopo()->findByZip('4436');
        $this->assertCount(2, $locations);
        $this->assertEquals('Oberdorf BL', $locations[0]->getOrtbez27());
        $this->assertEquals('Liedertswil', $locations[1]->getOrtbez27());
    }

    public function testByNotExistingZip() {

        $locations = $this->getRopo()->findByZip('0011');
        $this->assertCount(0, $locations);
    }

    public function testSuggestBern() {
        $locations = $this->getRopo()->findSuggested('Bern');
        $this->assertCount(33, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
        $this->assertEquals('3000', $locations[0]->getPostleitzahl());
        $this->assertEquals('Allmendingen b. Bern', $locations[16]->getOrtbez27());
        $this->assertEquals('Wohlen b. Bern', $locations[32]->getOrtbez27());
    }

    public function testSuggestBernLower() {
        $locations = $this->getRopo()->findSuggested('bern');
        $this->assertCount(33, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
        $this->assertEquals('3000', $locations[0]->getPostleitzahl());
        $this->assertEquals('Allmendingen b. Bern', $locations[16]->getOrtbez27());
        $this->assertEquals('Wohlen b. Bern', $locations[32]->getOrtbez27());
    }

    public function testSuggestOrb() {
        $locations = $this->getRopo()->findSuggested('orb');
        $this->assertCount(16, $locations);
        $this->assertEquals('Arnex-sur-Orbe', $locations[0]->getOrtbez27());
        $this->assertEquals('Morbio Superiore', $locations[7]->getOrtbez27());
        $this->assertEquals('Worblaufen', $locations[15]->getOrtbez27());
    }

    public function testSuggest3000() {
        $locations = $this->getRopo()->findSuggested('3000');
        $this->assertCount(1, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
    }

    public function testSuggest18() {
        $locations = $this->getRopo()->findSuggested('018');
        $this->assertCount(5, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
        $this->assertEquals('3018', $locations[0]->getPostleitzahl());
        $this->assertEquals('Lausanne', $locations[4]->getOrtbez27());
    }

}