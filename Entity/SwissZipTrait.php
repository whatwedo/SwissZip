<?php

namespace whatwedo\SwissZip\Entity;

use Doctrine\ORM\Mapping as ORM;

trait SwissZipTrait
{
    /**
     * @ORM\Column(type="integer", unique=true, nullable=false)
     * @ORM\Id
     */
    protected int $onrp = 0;

    /**
     * @ORM\Column(type="string", length=4, nullable=false)
     */
    protected string $postleitzahl = '';

    /**
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    protected string $plzZz = '';

    /**
     * @ORM\Column(type="string", length=18,  nullable=false)
     */
    protected string $ortbez18 = '';

    /**
     * @ORM\Column(type="string", length=27,  nullable=false)
     */
    protected string $ortbez27 = '';

    /**
     * @ORM\Column(type="string", length=2,  nullable=false)
     */

    protected string $kanton = '';

    /**
     * @ORM\Column(type="smallint",  nullable=false)
     */
    protected int $sprachcode = SwissZipInterface::SPRACHCODE_1;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected int $plzTyp = SwissZipInterface::PLZ_TYP_20;


    /**
     * @ORM\Column(type="date_immutable", nullable=false)
     */
    protected ?\DateTimeImmutable $validFrom = null;

    public function getOnrp(): int
    {
        return $this->onrp;
    }

    public function setOnrp(int $onrp): self
    {
        $this->onrp = $onrp;
        return $this;
    }

    public function getPostleitzahl(): string
    {
        return $this->postleitzahl;
    }

    public function getPlzZz(): string
    {
        return $this->plzZz;
    }

    public function setPlzZz(string $plzZz): self
    {
        $this->plzZz = $plzZz;
        return $this;
    }

    public function setPostleitzahl(string $postleitzahl): self
    {
        $this->postleitzahl = $postleitzahl;
        return $this;
    }

    public function getOrtbez18(): string
    {
        return $this->ortbez18;
    }

    public function setOrtbez18(string $ortbez18): self
    {
        $this->ortbez18 = $ortbez18;
        return $this;
    }

    public function getOrtbez27(): string
    {
        return $this->ortbez27;
    }

    public function setOrtbez27(string $ortbez27): self
    {
        $this->ortbez27 = $ortbez27;
        return $this;
    }

    public function getKanton(): string
    {
        return $this->kanton;
    }

    public function setKanton(string $kanton): self
    {
        $this->kanton = $kanton;
        return $this;
    }

    public function getSprachcode(): int
    {
        return $this->sprachcode;
    }

    public function setSprachcode(int $sprachcode): self
    {
        $this->sprachcode = $sprachcode;
        return $this;
    }

    public function getPlzTyp(): int
    {
        return $this->plzTyp;
    }

    public function setPlzTyp(int $plzTyp): self
    {
        $this->plzTyp = $plzTyp;
        return $this;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(?\DateTimeImmutable $validFrom): self
    {
        $this->validFrom = $validFrom;
        return $this;
    }
}