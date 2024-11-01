<?php

namespace App\Services\Formatter;

class TextHandler implements \App\Services\Helper\SaveHandlerInterface
{
    private $fileHandle;
    private ?string $handlerPath = null;

    public function openFile(string $filePath): void
    {
        $this->fileHandle = fopen($filePath, 'w');
        if (!$this->fileHandle) {
            throw new \Exception("Не вдалося відкрити файл для запису.");
        }
    }

    public function writeContent(array $content): void
    {
        $text = '';
        foreach ($content as $kay => $value) {
            $text .= "$kay: $value\n";
        }
        $text .= "\n\n";
        fwrite($this->fileHandle, $text);
    }

    public function closeFile(): void
    {
        fclose($this->fileHandle);
    }

    /**
     * @param \JsonMachine\Items $data
     * @param bool $merged
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @param array $savedFields
     * @return array
     */
    public function save(\JsonMachine\Items $data, bool $merged = false, \Symfony\Component\Finder\SplFileInfo $file, array $savedFields = []): array
    {
        try {
            $generatedFilesPath = [];
            $savedPath = storage_path(\App\Services\SaveConfig::RESULT_FILES_PATH) . '/' .  time() . '_' .pathinfo($file->getFilename(), PATHINFO_FILENAME) . '.txt';
            if (!$this->handlerPath) {
                $this->handlerPath = $savedPath;
                $generatedFilesPath[] = $savedPath;
                $this->openFile($savedPath);
            }

            if ($savedPath !== $this->handlerPath && !$merged) {
                $this->closeFile();
                $generatedFilesPath[] = $savedPath;
                $this->handlerPath = $savedPath;
                $this->openFile($savedPath);
            }
            foreach ($data as $info) {
                $this->writeContent($info);
            }
            return $generatedFilesPath;
        } catch (\Exception $exception) {
            return [];
        }
    }
}
