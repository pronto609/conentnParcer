<?php

namespace App\Services\Formatter;

use App\Services\TableParseConfig;

class XmlHandler implements \App\Services\Helper\SaveHandlerInterface
{
    private $fileHandle;
    private ?string $handlerPath = null;

    public function openFile(string $filePath): void
    {
        $this->fileHandle = fopen($filePath, 'w');
        if (!$this->fileHandle) {
            throw new \Exception("Не вдалося відкрити файл для запису.");
        }
        fwrite($this->fileHandle, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<posts>\n");
    }

    public function writeContent(array $content): void
    {
        $xml = "\t<post>\n";
            foreach ($content as $key => $value) {
                $xml .= "\t\t<$key>" . htmlspecialchars($value) . "</$key>\n";
            }
        $xml .= "\t</post>\n";
        fwrite($this->fileHandle, $xml);
    }

    public function closeFile(): void
    {
        fwrite($this->fileHandle, "</posts>");
        fclose($this->fileHandle);
    }

    public function save(\JsonMachine\Items $data, bool $merged = false, \Symfony\Component\Finder\SplFileInfo $file, array $savedFields = []): void
    {
        try {
            $savedPath = storage_path(\App\Services\SaveConfig::RESULT_FILES_PATH) . '/' .  time() . '_' .pathinfo($file->getFilename(), PATHINFO_FILENAME) . '.xml';
            if (!$this->handlerPath) {
                $this->handlerPath = $savedPath;
                $this->openFile($savedPath);
            }

            if ($savedPath !== $this->handlerPath && !$merged) {
                $this->closeFile();
                $this->handlerPath = $savedPath;
                $this->openFile($savedPath);
            }
            foreach ($data as $info) {
                $this->writeContent($info);
            }
        } catch (\Exception $exception) {
            //TODO lod exception
        }
    }
}
