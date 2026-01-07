<?php

function read_csv($file_path, $targetEncoding = 'UTF-8') {
    $data = [];

    if (!file_exists($file_path)) {
        throw new Exception("Le fichier n'existe pas.");
    }

    $file = fopen($file_path, 'r');
    if ($file === false) {
        throw new Exception("Impossible d'ouvrir le fichier.");
    }

    $sample = fread($file, 4096);
    fclose($file);

    $delimiters = [',', ';', "\t", '|'];
    $detected_delimiter = ',';

    foreach ($delimiters as $delimiter) {
        $rows = explode("\n", $sample);
        $first_row = str_getcsv($rows[0], $delimiter);
        if (count($first_row) > 1) {
            $detected_delimiter = $delimiter;
            break;
        }
    }

    $file = fopen($file_path, 'r');
    if ($file === false) {
        throw new Exception("Impossible d'ouvrir le fichier.");
    }

    $header = fgetcsv($file, 0, $detected_delimiter);
    if ($header === false) {
        throw new Exception("Impossible de lire l'en-tête du fichier.");
    }

    $header = array_map(function($header) use ($targetEncoding) {
        return mb_convert_encoding($header, $targetEncoding);
    }, $header);

    $data = [];
    while (($row = fgetcsv($file, 0, $detected_delimiter)) !== false) {
        $row = array_map(function($cell) use ($targetEncoding) {
            return mb_convert_encoding($cell, $targetEncoding);
        }, $row);
        $data[] = array_combine($header, $row);
    }
    fclose($file);

    foreach ($data as &$row) {
        foreach ($row as $key => $value) {
            $row[$key] = $value === null ? '' : $value;
        }
    }

    // Conversion des dates
    foreach ($data as &$row) {
        foreach ($row as $key => $value) {
            if (strpos($key, 'date') !== false && $value) {
                $dateTime = DateTime::createFromFormat('Y-m-d', $value);
                if ($dateTime === false) {
                    $dateTime = DateTime::createFromFormat('d/m/Y', $value);
                }
                if ($dateTime === false) {
                    $dateTime = DateTime::createFromFormat('d-m-Y', $value);
                }
                if ($dateTime) {
                    $row[$key] = $dateTime->format('Y-m-d');
                }
            }
        }
    }

    return $data;
}

?>