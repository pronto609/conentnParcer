<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

class SaveHandler
{
    private ?string $format;
    private ?int $merged;

    public function __construct(
        private \App\Services\Formatter\CsvHandler $csvHandler,
        private \App\Services\Formatter\TextHandler $textHandler,
        private \App\Services\Formatter\XmlHandler $xmlHandler,
        private \App\Services\Helper\TmpSaveHandler $tmpSaveHandler
    ) {
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function setMerged(int $merged)
    {
        $this->merged = $merged;
    }

    public function save(): void
    {
        $files = File::files(storage_path(SaveConfig::RES_DUMPS_PATH));
        foreach ($files as $file) {
            $data = Items::fromFile($file->getPathname(), ['decoder' => new ExtJsonDecoder(true)]);
            $this->{$this->format . 'Handler'}->save($data, $this->merged, $file, SaveConfig::COLUMNS_SAVED);
        }
        $this->{$this->format . 'Handler'}->closeFile();
        $this->tmpSaveHandler->cleanDirectory(storage_path(SaveConfig::RES_DUMPS_PATH));
    }
}
