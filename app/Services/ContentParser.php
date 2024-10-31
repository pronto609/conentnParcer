<?php

namespace App\Services;
use iamcal\SQLParser;
use marcocesarato\sqlparser\LightSQLParser;
use Illuminate\Support\Facades\File;
use PHPSQLParser\PHPSQLParser;
class ContentParser
{
    public function parse($databasePaths)
    {
        foreach ($databasePaths as $dbPath) {
            $this->parseInsertToJson($dbPath);

        }
    }

    private function parseInsertToJson(string $dbPath)
    {
        $inputHandle = fopen($dbPath, 'r');
        if (!$inputHandle) {
            die("Не вдалося відкрити файл для читання.");
        }

        $outputFile = storage_path('dumps/tmpdumps') . '/' . File::name($dbPath) . '.json';
        $outputHandle = fopen($outputFile, 'w');
        if (!$outputHandle) {
            fclose($inputHandle);
            die("Не вдалося відкрити JSON файл для запису.");
        }

        fwrite($outputHandle, "{\n");

        $currentQuery = '';
        $isInInsert = false;
        $firstTableEntry = true;

        while (($line = fgets($inputHandle)) !== false) {
            if (stripos($line, 'INSERT INTO') !== false) {
                $isInInsert = true;
            }

            if ($isInInsert) {
                $currentQuery .= $line;

                if (preg_match('/\);\s*$/', trim($line))) {
                    if (!$firstTableEntry) {
                        fwrite($outputHandle, ",\n");
                    }
                    $firstTableEntry = false;

                    $this->processInsertQuery($currentQuery, $outputHandle);
                    $currentQuery = '';
                    $isInInsert = false;
                }
            }
        }

        fwrite($outputHandle, "\n}");

        fclose($inputHandle);
        fclose($outputHandle);

        echo "Дані успішно збережені у JSON файл: $outputFile";
        die();

    }

    private function processInsertQuery(string $query, $outputHandle)
    {
        if (preg_match('/^INSERT INTO `([^`]+)` \(([^)]+)\) VALUES/i', $query, $matches)) {
            $tableName = $matches[1];
            $columns = explode(', ', str_replace('`', '', $matches[2])); // Видаляємо лапки з колонок

            fwrite($outputHandle, '"' . $tableName . '": [');

            $firstRow = true;

            preg_match_all('/(?<=VALUES).*?(?<=;$)/s', $query, $valueMatches);

            foreach ($valueMatches[0] as $valueString) {
                $rows = preg_split('/\)(\\n|\\t)?,(\\n|\\t)?\(/', $valueString);
                foreach ($rows as $row) {
                    if (!$firstRow) {
                        fwrite($outputHandle, ",");
                    }
                    $firstRow = false;

                    $values = preg_split('/,\t/', $row);
                    $values = array_map(function ($value) {
                        return trim($value, "'`"); // Прибираємо лапки з кожного значення
                    }, $values);

                    if (count($columns) === count($values)) {
                        $rowData = array_combine($columns, $values);
                        fwrite($outputHandle, json_encode($rowData, JSON_UNESCAPED_UNICODE));
                    } else {
                        echo "Невідповідність між кількістю колонок та значень у рядку: $valueString\n";
                    }
                }
            }

            fwrite($outputHandle, "]");
        }
    }


}
