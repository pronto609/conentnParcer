<?php

namespace App\Services\Helper;

use Illuminate\Support\Facades\File;

class TmpSaveHandler
{
    private $inputHandler;
    private $outputHandler;

    /**
     * @param string $filePath
     * @return resource|null
     */
    public function openFileForRead(string $filePath)
    {
        try {
            $this->inputHandler = fopen($filePath, 'r');
            if (!$this->inputHandler) {
                throw new \Exception("Не вдалося відкрити файл для читання.");
            }
            return $this->inputHandler;
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function openFileForWrite(string $outputPath)
    {
        try {
            $this->outputHandler = fopen($outputPath, 'w');
            if (!$this->outputHandler) {
                throw new \Exception("Не вдалося відкрити JSON файл для запису.");
            }
            return $this->outputHandler;
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function readLine()
    {
        return fgets($this->inputHandler);
    }

    public function writeContent(string $content)
    {
        fwrite($this->outputHandler, $content);
    }

    public function writeJson(array $data)
    {
        if (!$this->outputHandler) {
            throw new \Exception("Файл для запису не відкритий.");
        }
        fwrite($this->outputHandler, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
    }

    public function closeFiles()
    {
        if ($this->inputHandler) fclose($this->inputHandler);
        if ($this->outputHandler) fclose($this->outputHandler);
    }

    public function deleteOutputFile()
    {
        if ($this->outputFilePath && File::exists($this->outputFilePath)) {
            File::delete($this->outputFilePath);
            echo "Файл {$this->outputFilePath} успішно видалено.\n";
        } else {
            echo "Файл для видалення не знайдено.\n";
        }
    }
}
