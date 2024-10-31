<?php

namespace App\Services;

use JsonMachine\Items;
use Illuminate\Support\Facades\File;
use JsonMachine\JsonDecoder\ExtJsonDecoder;

class JsonParser
{
    public function __construct(
        private \App\Services\Helper\CutterAttributes $cutterAttributes,
        private \App\Services\Helper\TmpSaveHandler $fileHandler
    ) {
    }

    public function parse()
    {
        $tmpPath = storage_path(SaveConfig::TMP_DUMPS_PATH);
        $files = File::files($tmpPath);

        foreach ($files as $file) {
            $data = Items::fromFile($file->getPathname(), ['decoder' => new ExtJsonDecoder(true)]);
            $resPath = storage_path(SaveConfig::RES_DUMPS_PATH) . '/' . $file->getFilename();

            $this->fileHandler->openFileForWrite($resPath);
            $this->fileHandler->writeContent("[\n");

            $this->processJson($data);

            $this->fileHandler->writeContent("\n]");
            $this->fileHandler->closeFiles();
        }
    }

    private function processJson(\JsonMachine\Items $data)
    {
        $isFirst = true;
        foreach ($data as $name => $info) {
            if (preg_match('/' . TableParseConfig::TABLE_NAME_LIKE . '/', $name)) {
                if (!$isFirst) {
                    $this->fileHandler->writeContent(",\n");
                }
                $isFirst = false;

                $this->prepareAndWriteData($info);
            }
        }
    }

    private function prepareAndWriteData(array $tableInfo)
    {
        $allRows = count($tableInfo);
        $counter = 0;
        foreach ($tableInfo as $row) {
            $counter++;
            $dataRow = [];

            foreach (TableParseConfig::COLUMNS_PARSE as $columnName => $cutTags) {
                if (isset($row[$columnName])) {
                    if (!$cutTags) {
                        $dataRow[$columnName] = $row[$columnName];
                    } else {
                        $dataRow[$columnName] = $this->cutterAttributes->cut($row[$columnName], $cutTags);
                    }
                }
            }

            $this->fileHandler->writeContent(json_encode($dataRow, JSON_UNESCAPED_UNICODE));
            if ($counter !== $allRows) {
                $this->fileHandler->writeContent(",\n");
            }
            unset($dataRow);
        }
    }
}
