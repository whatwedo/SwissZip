<?php

namespace whatwedo\SwissZip\Dto;

class UpdateReport
{

    public int $updated = 0;
    public int $inserted = 0;
    public int $deleted = 0;
    public string $location;
    public int $skipped = 0;

    private array $messages = [];

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function addMessage(string $message): self
    {
        $this->messages[] = $message;
        return $this;
    }


}