<?php

function getColumns($tableName, $conn) {
    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM `$tableName`");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (strpos($row['Extra'], 'auto_increment') === false) {
                $columns[$row['Field']] = $row['Field'];
            }
        }
        $result->free();
    } else {
        throw new Exception("Erreur lors de la récupération des colonnes de la table $tableName.");
    }

    return $columns;
}

function classifyData($data, $tableName, $conn, $columnMapping = []) {
    $classifiedData = [];
    $columns = getColumns($tableName, $conn);


    $uniqueValues = [];
    foreach ($columns as $dbColumn) {
        $uniqueValues[$dbColumn] = [];
    }

    foreach ($data as $row) {
        foreach ($columns as $dbColumn) {
            $csvColumn = array_search($dbColumn, $columnMapping);
            if ($csvColumn !== false && isset($row[$csvColumn])) {
                $uniqueValues[$dbColumn][$row[$csvColumn]] = true;
            }
        }
    }

    foreach ($data as $row) {
        $filteredRow = [];
        foreach ($columns as $dbColumn) {
            $csvColumn = array_search($dbColumn, $columnMapping);
            $value = $csvColumn !== false && isset($row[$csvColumn]) ? $row[$csvColumn] : '';

            if (isset($uniqueValues[$dbColumn][$value])) {
                $filteredRow[$dbColumn] = $value;
            } else {
                $filteredRow[$dbColumn] = '';
            }
        }
        if (!in_array($filteredRow, $classifiedData)) {
            $classifiedData[] = $filteredRow;
        }
    }

    return $classifiedData;
}

?>