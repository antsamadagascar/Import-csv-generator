<?php

function prepareStatement($conn, $sql) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erreur lors de la préparation de la requête : " . $conn->error);
    }
    return $stmt;
}

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

function fetchReferences($data, $references, $conn) {
    $ids = [];
    
    foreach ($data as $row) {
        foreach ($references as $refColumn => $refTable) {
            if (isset($row[$refColumn])) {
                $value = $row[$refColumn];       
                // Vérifiez si l'ID est déjà en cache
                if (!isset($ids[$refTable][$value])) {
                    $columns = getColumns($refTable, $conn);
                    $columnName = array_keys($columns)[0]; 

                    // Prépare et exécute la requête SQL
                    try {
                        $stmt = prepareStatement($conn, "SELECT id FROM `$refTable` WHERE `$columnName` = ?");
                        $stmt->bind_param('s', $value);
                        $stmt->execute();

                        // Obtenez le résultat et fetch_assoc() pour MySQLi
                        $result = $stmt->get_result()->fetch_assoc();

                        if ($result) {
                            $ids[$refTable][$value] = $result['id'];
                        } else {
                            // Assurez-vous que le cache est réinitialisé
                            $ids[$refTable][$value] = null;
                        }
                    } catch (Exception $e) {
                         // Réinitialisez le cache en cas d'erreur
                        $ids[$refTable][$value] = null;
                    }
                } else {
                    echo "<pre>$refTable -> '$value' ID en cache: " . $ids[$refTable][$value] . "</pre>";
                }
            }
        }
    }
    
    return $ids;
}

function generateAndWriteInsertSql($table, $columnNames, $values, $filePath) {
    $placeholders = implode(',', array_fill(0, count($columnNames), '?'));
    $sql = "INSERT INTO `$table` (" . implode(',', $columnNames) . ") VALUES ($placeholders)";
    
    $formattedValues = array_map(function($value) {
        return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
    }, $values);

    $sqlForFile = "INSERT INTO `$table` (" . implode(',', $columnNames) . ") VALUES (" . implode(',', $formattedValues) . ");\n";

    $file = fopen($filePath, 'a');
    if ($file === false) {
        throw new Exception("Impossible d'ouvrir le fichier pour l'écriture.");
    }
    
    fwrite($file, "--\n-- Données pour la table `$table`\n--\n\n");
    fwrite($file, $sqlForFile);
    fclose($file);

    return $sql;
}

function insertData($data, $table, $conn, $references = []) {
    $columns = getColumns($table, $conn);
    $columnNames = array_keys($columns);
    $filePath = generateFileName($table);

    $sql = "INSERT INTO `$table` (" . implode(',', $columnNames) . ") VALUES (" . implode(',', array_fill(0, count($columnNames), '?')) . ")";
    $stmt = prepareStatement($conn, $sql);

    $ids = !empty($references) ? fetchReferences($data, $references, $conn) : [];
    
    foreach ($data as $row) {
        $values = [];
        $types = '';

        foreach ($columnNames as $column) {
            if (isset($references[$column])) {
                $refTable = $references[$column];
                $refValue = $row[$column] ?? '';
                $values[] = $ids[$refTable][$refValue] ?? null;
            } else {
                $values[] = $row[$column] ?? null;
            }
            $types .= getColumnType($columns[$column]);
        }

        $formattedValues = array_map(function($value) {
            return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
        }, $values);

         $insertSql = "INSERT INTO `$table`\n    (" . implode(', ', $columnNames) . ")\nVALUES\n    (" . implode(', ', $formattedValues) . ");";
 
        $stmt->bind_param($types, ...$values);

        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'insertion dans la table `$table` : " . $stmt->error);
        }
    }

    $stmt->close();
}

function generateFileName($table, $prefix = 'data_', $suffix = '.sql') {
    return "{$prefix}{$table}{$suffix}";
}

function getColumnType($columnType) {
    if (strpos($columnType, 'int') !== false) {
        return 'i';
    } elseif (strpos($columnType, 'float') !== false || strpos($columnType, 'double') !== false || strpos($columnType, 'decimal') !== false) {
        return 'd';
    } else {
        return 's';
    }
}

?>