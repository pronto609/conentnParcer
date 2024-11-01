<?php

namespace App\Services;

use App\Services\Helper\TmpSaveHandler;

class ContentParser
{
    public function __construct(
        private \App\Services\JsonParser $jsonParser,
        private TmpSaveHandler $fileHandler
    ) {
    }

    public function parse($databasePaths)
    {
        foreach ($databasePaths as $dbPath) {
            $this->parseInsertToJson($dbPath);
            $this->jsonParser->parse();
            $this->fileHandler->cleanDirectory(storage_path(\App\Services\SaveConfig::TMP_DUMPS_PATH));
        }
    }

    /**
     * @param string $dbPath
     * @return void
     */
    private function parseInsertToJson(string $dbPath): void
    {
        $this->fileHandler->openFileForRead($dbPath);

        $this->fileHandler->openFileForWrite( storage_path(SaveConfig::TMP_DUMPS_PATH) .'/'.pathinfo($dbPath, PATHINFO_FILENAME) . '.json');

        $this->fileHandler->writeContent("{\n");

        $currentQuery = '';
        $isInInsert = false;
        $firstTableEntry = true;

        while (($line = $this->fileHandler->readLine()) !== false) {
            if (stripos($line, 'INSERT INTO') !== false) {
                $isInInsert = true;
            }

            if ($isInInsert) {
                $currentQuery .= $line;

                if (preg_match('/\);\s*$/', trim($line))) {
                    if (!$firstTableEntry) {
                        $this->fileHandler->writeContent(",\n");
                    }
                    $firstTableEntry = false;

                    $this->processInsertQuery($currentQuery);
                    $currentQuery = '';
                    $isInInsert = false;
                }
            }
        }

        $this->fileHandler->writeContent("\n}");

        $this->fileHandler->closeFiles();
    }

    private function processInsertQuery(string $query)
    {
        if (preg_match('/^INSERT INTO `([^`]+)` \(([^)]+)\) VALUES/i', $query, $matches)) {
            $tableName = $matches[1];
            $columns = explode(', ', str_replace('`', '', $matches[2]));

            $this->fileHandler->writeContent('"' . $tableName . '": [');

            $firstRow = true;

            preg_match_all('/(?<=VALUES).*?(?<=;$)/s', $query, $valueMatches);

            foreach ($valueMatches[0] as $valueString) {
                $valueString = preg_replace('/^\n\(|\);\s*$/', '', $valueString);
                $rows = preg_split('/\)(\\n|\\t)?,(\\n|\\t)?\(/', $valueString);
                foreach ($rows as $row) {
                    if (!$firstRow) {
                        $this->fileHandler->writeContent(",");
                    }
                    $firstRow = false;

                    $values = preg_split('/,\t/', $row);
                    $values = array_map(function ($value) {
                        return trim($value, "'`");
                    }, $values);

                    if (count($columns) === count($values)) {
                        $rowData = array_combine($columns, $values);
                        $this->fileHandler->writeContent(json_encode($rowData, JSON_UNESCAPED_UNICODE));
                    } else {
                        echo "Невідповідність між кількістю колонок та значень у рядку: $valueString\n";
                    }
                }
            }

            $this->fileHandler->writeContent("]");
        }
    }
}

