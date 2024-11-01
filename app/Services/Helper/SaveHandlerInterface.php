<?php

namespace App\Services\Helper;

interface SaveHandlerInterface
{
    public function openFile(string $filePath): void;
    public function writeContent(array $content): void;
    public function closeFile(): void;
    public function save(\JsonMachine\Items $data, bool $merged = false, \Symfony\Component\Finder\SplFileInfo $file, array $savedFields = []): array;
}
