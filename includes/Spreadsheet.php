<?php
/**
 * Minimal spreadsheet reader — CSV and basic .xlsx (shared/inline strings).
 * Returns rows as arrays of strings; first row is treated as headers by callers.
 */
class Spreadsheet {

    /** Parse uploaded file into rows. Returns [rows, error] */
    public static function parseFile(array $file): array {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return [[], 'No file uploaded.'];
        $name = strtolower($file['name'] ?? '');
        $tmp = $file['tmp_name'];
        if (str_ends_with($name, '.xlsx') || str_ends_with($name, '.xlsm')) {
            return self::parseXlsx($tmp);
        }
        if (str_ends_with($name, '.csv')) {
            return [self::parseCsv($tmp), ''];
        }
        return [[], 'Only .csv or .xlsx files are supported.'];
    }

    /** CSV (handles quotes, BOM) */
    public static function parseCsv(string $path): array {
        $rows = [];
        $fh = fopen($path, 'r');
        if (!$fh) return [];
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            $rows[] = array_map(fn($c) => trim((string)$c), $row);
        }
        fclose($fh);
        if ($rows && str_starts_with($rows[0][0] ?? '', "\xEF\xBB\xBF")) $rows[0][0] = substr($rows[0][0], 3);
        return $rows;
    }

    /** Minimal XLSX: reads first worksheet, shared strings + inline strings, skips empty rows */
    public static function parseXlsx(string $path): array {
        if (!class_exists('ZipArchive')) return [[], 'ZipArchive is required to read .xlsx files.'];
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return [[], 'Cannot open .xlsx file.'];
        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            preg_match_all('/<si>(.*?)<\/si>/s', $xml, $m);
            foreach ($m[1] as $si) {
                if (preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $si, $t)) {
                    $shared[] = html_entity_decode(implode('', $t[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
                } else {
                    $shared[] = '';
                }
            }
        }
        // first sheet
        $sheetName = null;
        if (($xml = $zip->getFromName('xl/workbook.xml')) !== false && preg_match('/<sheet[^>]*name="([^"]+)"/', $xml, $m)) {
            $sheetName = $m[1];
        }
        $target = 'xl/worksheets/sheet1.xml';
        if ($sheetName !== null) {
            $rId = null;
            if (preg_match('/<sheet[^>]*name="' . preg_quote($sheetName, '/') . '"[^>]*r:id="([^"]+)"/', $zip->getFromName('xl/workbook.xml'), $m)) $rId = $m[1];
            if ($rId && preg_match('/<relationship[^>]*Id="' . preg_quote($rId, '/') . '"[^>]*Target="([^"]+)"/', $zip->getFromName('xl/_rels/workbook.xml.rels'), $m)) {
                $target = 'xl/' . ltrim($m[1], '/');
            }
        }
        $xml = $zip->getFromName($target);
        $zip->close();
        if ($xml === false) return [[], 'Worksheet not found in .xlsx.'];
        $rows = [];
        if (preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $xml, $rm)) {
            foreach ($rm[1] as $rowXml) {
                $row = [];
                preg_match_all('/<c[^>]*?(?:\s+t="([^"]*)")?[^>]*>(.*?)<\/c>|<c[^>]*?(?:\s+t="([^"]*)")?[^>]*\/>/s', $rowXml, $cm, PREG_SET_ORDER);
                foreach ($cm as $cell) {
                    $t = $cell[1] ?: $cell[3];
                    $v = $cell[2] ?? '';
                    if ($v === '') { $row[] = ''; continue; }
                    if ($t === 's' && preg_match('/<v>(.*?)<\/v>/s', $v, $mv)) {
                        $row[] = $shared[(int)$mv[1]] ?? '';
                    } elseif (preg_match('/<is><t[^>]*>(.*?)<\/t><\/is>/s', $v, $mv)) {
                        $row[] = html_entity_decode($mv[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
                    } elseif (preg_match('/<v>(.*?)<\/v>/s', $v, $mv)) {
                        $row[] = html_entity_decode($mv[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
                    } else {
                        $row[] = '';
                    }
                }
                if (implode('', $row) !== '') $rows[] = array_map('trim', $row);
            }
        }
        return [$rows, ''];
    }

    /** Normalize header names: lower-case, remove spaces */
    public static function headerIndex(array $headers): array {
        $map = [];
        foreach ($headers as $i => $h) {
            $key = mb_strtolower(preg_replace('/[^a-z0-9]/i', '', $h));
            if ($key !== '') $map[$key] = $i;
        }
        return $map;
    }
}
