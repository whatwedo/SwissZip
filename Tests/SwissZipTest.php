<?php

namespace whatwedo\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\SwissZip\Manager\SwissZipManager;
use whatwedo\SwissZip\Repository\SwissZipRepository;

class SwissZipTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testByExistingZip() {

        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);

        $locations = $manager->find('3011');
        $this->assertCount(1, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
    }

    public function testMultipleZip() {

        /** @var SwissZipManager $manager */
        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);

        $locations = $manager->find('4436');
        $this->assertCount(2, $locations);
        $this->assertEquals('Oberdorf BL', $locations[0]->getOrtbez27());
        $this->assertEquals('Liedertswil', $locations[1]->getOrtbez27());
    }

    public function testByNotExistingZip() {

        /** @var SwissZipManager $manager */
        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);

        $locations = $manager->find('0011');
        $this->assertCount(0, $locations);
    }

    public function testSuggestBern() {
        /** @var SwissZipManager $manager */
        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);
        $locations = $manager->suggest('Bern');
        $this->assertCount(33, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
        $this->assertEquals('3000', $locations[0]->getPostleitzahl());
        $this->assertEquals('Allmendingen b. Bern', $locations[16]->getOrtbez27());
        $this->assertEquals('Wohlen b. Bern', $locations[32]->getOrtbez27());
    }

    public function testSuggestBernLower() {
        /** @var SwissZipManager $manager */
        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);
        $locations = $manager->suggest('bern');
        $this->assertCount(33, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
        $this->assertEquals('3000', $locations[0]->getPostleitzahl());
        $this->assertEquals('Allmendingen b. Bern', $locations[16]->getOrtbez27());
        $this->assertEquals('Wohlen b. Bern', $locations[32]->getOrtbez27());
    }

    public function testSuggestOrb() {
        /** @var SwissZipManager $manager */
        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);
        $locations = $manager->suggest('orb');
        $this->assertCount(16, $locations);
        $this->assertEquals('Arnex-sur-Orbe', $locations[0]->getOrtbez27());
        $this->assertEquals('Morbio Superiore', $locations[7]->getOrtbez27());
        $this->assertEquals('Worblaufen', $locations[15]->getOrtbez27());
    }

    public function testSuggest3000() {
        /** @var SwissZipManager $manager */
        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);
        $locations = $manager->suggest('3000');
        $this->assertCount(1, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
    }

    public function testSuggest18() {
        /** @var SwissZipManager $manager */
        $manager = $this->getContainer()->get(\whatwedo\SwissZip\Manager\SwissZipManager::class);
        $locations = $manager->suggest('018');
        $this->assertCount(5, $locations);
        $this->assertEquals('Bern', $locations[0]->getOrtbez27());
        $this->assertEquals('3018', $locations[0]->getPostleitzahl());
        $this->assertEquals('Lausanne', $locations[4]->getOrtbez27());
    }

}