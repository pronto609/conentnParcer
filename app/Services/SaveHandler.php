<?php

namespace App\Services;

class SaveHandler
{
    private ?string $format;

    public function __construct(
        private \App\Services\Formatter\CsvHandler $csvHandler,
        private \App\Services\Formatter\TextHandler $textHandler,
        private \App\Services\Formatter\XmlHandler $xmlHandler,
    ) {
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function save(array $articles)
    {
        return $this->{$this->format . 'Handler'}->save($articles);
    }
}
