<?php

namespace App\Services;

class ExcelExportService {

    /**
     * Stream and download dynamic CSV file with UTF-8 BOM
     */
    public static function downloadCsv(string $filename, array $headers, array $data): void {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // Write UTF-8 BOM so Excel opens Hindi/Gujarati/Special characters perfectly
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers);

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Download a sample CSV template for bulk data import
     */
    public static function downloadTemplate(string $filename, array $headers, array $sampleRows = []): void {
        self::downloadCsv($filename, $headers, $sampleRows);
    }

    /**
     * Parse an uploaded CSV file safely into associative rows
     */
    public static function parseCsv(string $filePath): array {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Remove BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                return [];
            }

            // Clean header keys
            $cleanHeaders = array_map(function($h) {
                return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $h)));
            }, $headers);

            while (($data = fgetcsv($handle)) !== false) {
                if (empty(array_filter($data))) {
                    continue; // Skip completely empty rows
                }
                $row = [];
                foreach ($cleanHeaders as $index => $headerKey) {
                    $row[$headerKey] = isset($data[$index]) ? trim($data[$index]) : '';
                }
                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }
}
