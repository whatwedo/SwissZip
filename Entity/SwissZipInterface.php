<?php

namespace whatwedo\SwissZip\Entity;


interface SwissZipInterface
{
    const PLZ_TYP_10 = 10;
    const PLZ_TYP_20 = 20;
    const PLZ_TYP_30 = 30;
    const PLZ_TYP_40 = 40;
    const PLZ_TYP_80 = 80;

    const SPRACHCODE_1 = 1;
    const SPRACHCODE_2 = 2;
    const SPRACHCODE_3 = 3;

    public function setOnrp(int $id): self;

    public function setPostleitzahl(string $postleitzahl): self;

    public function setPlzZz(string $postleitzahl): self;

    public function setOrtbez18(string $ortbez18): self;

    public function setOrtbez27(string $ortbez27): self;

    public function setKanton(string $kanton): self;

    public function setSprachcode(int $sprachcode): self;

    public function setPlzTyp(int $plzTyp): self;

    public function setValidFrom(?\DateTimeImmutable $validFrom): self;
}