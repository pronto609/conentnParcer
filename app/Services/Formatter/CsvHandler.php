<?php

namespace App\Services\Formatter;

use Illuminate\Support\Facades\File;
use JsonMachine\Items;

class CsvHandler implements \App\Services\Helper\SaveHandlerInterface
{
    private $fileHandler;
    private ?string $handlerPath = null;

    public function openFile(string $filePath, array $headers = []): void
    {
        $this->fileHandler = fopen($filePath, 'w');
        if (!$this->fileHandler) {
            throw new \Exception("Не вдалося відкрити файл для запису.");
        }
        // Запис заголовків
        fputcsv($this->fileHandler, [...$headers]);
    }

    public function writeContent(array $content): void
    {
        fputcsv($this->fileHandler, $content);
    }

    public function closeFile(): void
    {
        fclose($this->fileHandler);
    }

    /**
     * @param Items $data
     * @param bool $merged
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @param array $savedFields
     * @return void
     */
    public function save(\JsonMachine\Items $data, bool $merged = false, \Symfony\Component\Finder\SplFileInfo $file, array $savedFields = []): void
    {
        try {
            $savedPath = storage_path(\App\Services\SaveConfig::RESULT_FILES_PATH) . '/' .  time() . '_' .pathinfo($file->getFilename(), PATHINFO_FILENAME) . '.csv';
            if (!$this->handlerPath) {
                $this->handlerPath = $savedPath;
                $this->openFile($savedPath, [...$savedFields]);
            }

            if ($savedPath !== $this->handlerPath && !$merged) {
                $this->closeFile();
                $this->handlerPath = $savedPath;
                $this->openFile($savedPath, [...$savedFields]);
            }
            foreach ($data as $info) {
                $this->writeContent($info);
            }
        } catch (\Exception $exception) {
            //TODO lod exception
        }

    }
}
