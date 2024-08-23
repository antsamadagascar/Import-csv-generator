<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * ImportModel
 * 
 * Ce modèle gère l'importation des données à partir de fichiers CSV dans des tables spécifiques de la base de données.
 * Il inclut des méthodes pour récupérer les colonnes des tables, vérifier la correspondance des colonnes, préparer des requêtes SQL,
 * insérer des données avec gestion des références, et générer des fichiers SQL pour les insertions.
 * 
 * Créé par : Aina Ny Antsa Ratovonandrasana @misaina
 */

class ImportModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * getColumns
     * Récupère les colonnes d'une table de la base de données, en excluant les colonnes auto-incrémentées
     * et celles avec des valeurs par défaut automatiques (ex: CURRENT_TIMESTAMP).
     * 
     * @param string $tableName Le nom de la table
     * @return array Les colonnes de la table
     * @throws Exception Si une erreur survient lors de la récupération des colonnes
     */
    public function getColumns($tableName) {
        $columns = [];
        $result = $this->db->query("SHOW COLUMNS FROM `$tableName`");
    
        if ($result) {
            foreach ($result->result_array() as $row) {
                $fieldType = $row['Type'];
                $isAutoIncrement = strpos($row['Extra'], 'auto_increment') !== false;
                $hasDefault = $row['Default'] !== null;
                $default = $row['Default'];
                
                // Exclure les colonnes auto_increment ou avec des valeurs par défaut automatiques
                if (!$isAutoIncrement && (!$hasDefault || !preg_match('/^(CURRENT_TIMESTAMP|NOW\(\)|CURRENT_DATE|CURRENT_TIME|UUID\(\))$/i', $default))) {
                    // Vérifier si le type de colonne est TIMESTAMP ou DATE, et si elle a une valeur par défaut automatique
                    if (!preg_match('/timestamp|date|datetime/i', $fieldType) || !preg_match('/^CURRENT_TIMESTAMP|NOW\(\)|CURRENT_DATE|CURRENT_TIME|UUID\(\)$/i', $default)) {
                        $columns[$row['Field']] = $row['Field'];
                    }
                }
            }
        } else {
            throw new Exception("Erreur lors de la récupération des colonnes de la table $tableName.");
        }
    
        return $columns;
    }

    /**
     * Vérifie si le nombre de colonnes dans la table correspond au nombre de colonnes définies dans le mappage
     *
     * @param string $table Le nom de la table
     * @param array $mappingColumns Les colonnes définies dans le mappage
     * @throws Exception Si le nombre de colonnes ne correspond pas
     */
    public function verifyColumnCount($table, $mappingColumns) {
        // Get the actual columns from the database
        $actualColumns = $this->getColumns($table);
        $actualColumnCount = count($actualColumns);
    
        // Get the number of columns defined in the mapping
        $mappingColumnCount = count($mappingColumns);
    
        // Check if the number of columns match
        if ($actualColumnCount !== $mappingColumnCount) {
            throw new Exception("Column count mismatch for table `$table`: Expected $actualColumnCount, got $mappingColumnCount in the mapping.");
        }
    }
    
    /**
     * prepareStatement
     * Prépare une requête SQL pour l'exécution.
     * 
     * @param string $sql La requête SQL à préparer
     * @return mysqli_stmt La requête préparée
     * @throws Exception Si une erreur survient lors de la préparation
     */

    public function prepareStatement($sql) {
        $stmt = $this->db->conn_id->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erreur lors de la préparation de la requête : " . $this->db->conn_id->error);
        }
        return $stmt;
    }

    /**
     * fetchReferences
     * Récupère les identifiants des références à partir d'autres tables.
     * 
     * @param array $data Les données à insérer
     * @param array $references Les colonnes de références et les tables associées
     * @param mysqli $conn La connexion à la base de données
     * @return array Les identifiants des références
     */
    
     public function fetchReferences($data, $references, $conn) {
        $ids = [];
        /*
        echo "<pre>";
        print_r($ids);
        echo "</pre>"
        */
        
        foreach ($data as $row) {
            foreach ($references as $refColumn => $refTable) {
                if (isset($row[$refColumn])) {
                    $value = $row[$refColumn];
                    /*
                    // Afficher les détails pour le debug
                    echo "<pre>Valeur recherchée: $value</pre>";
                    echo "<pre>Colonne de référence: $refColumn</pre>";
                    echo "<pre>Données actuelles: " . print_r($row, true) . "</pre>";
                    */
                    
                    if (!isset($ids[$refTable][$value])) {
                        $columns = $this->getColumns($refTable);
                        $columnName = array_keys($columns)[0];
        
                        try {
                            $sql = "SELECT id FROM `$refTable` WHERE `$columnName` = ?";
                            $stmt = $this->prepareStatement($sql);
                            $stmt->bind_param('s', $value);
                            $stmt->execute();
        
                            $result = $stmt->get_result()->fetch_assoc();
        
                            if ($result) {
                                $ids[$refTable][$value] = $result['id'];
                            } else {
                                $ids[$refTable][$value] = null;
                                //echo "<pre>$refTable -> '$value' ID non trouvé</pre>";
                            }
                        } catch (Exception $e) {
                            // echo "<pre>Erreur SQL: " . $e->getMessage() . "</pre>";
                            $ids[$refTable][$value] = null;
                        }
                    }
                }
            }
        }
        
        return $ids;
    }

     /**
     * generateAndWriteInsertSql
     * Génère une requête SQL d'insertion et l'écrit dans un fichier.
     * 
     * @param string $table Le nom de la table
     * @param array $columnNames Les noms des colonnes
     * @param array $values Les valeurs à insérer
     * @param string $filePath Le chemin du fichier où écrire la requête (par défaut: 'data.sql')
     * @return string La requête SQL générée
     * @throws Exception Si une erreur survient lors de l'écriture dans le fichier
     */
    
    public function generateAndWriteInsertSql($table, $columnNames, $values, $filePath = 'data.sql') {
        // Formatage des valeurs pour SQL
        $formattedValues = array_map(function($value) {
            return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
        }, $values);
    
        // Générer la requête SQL pour les insertions
        $sqlForFile = "INSERT INTO `$table` (" . implode(',', $columnNames) . ") VALUES (" . implode(',', $formattedValues) . ");\n";
    
        // Ouvrir le fichier data.sql pour ajouter les données
        $file = fopen($filePath, 'a');
        if ($file === false) {
            throw new Exception("Impossible d'ouvrir le fichier pour l'écriture.");
        }
    
        // Vérifier si c'est la première insertion pour cette table dans le fichier
        static $tablesWritten = [];
        if (!isset($tablesWritten[$table])) {
            fwrite($file, "\n--\n-- Données pour la table `$table`\n--\n\n");
            $tablesWritten[$table] = true;
        }
    
        // Écrire la requête d'insertion dans le fichier
        fwrite($file, $sqlForFile);
        fclose($file);
    
        return $sqlForFile;
    }
    
    /**
     * insertData
     * Insère les données dans une table spécifiée, en gérant les références aux autres tables si nécessaire.
     * 
     * @param array $data Les données à insérer
     * @param string $table Le nom de la table
     * @param array $references Les colonnes de références (facultatif)
     * @throws Exception Si une erreur survient lors de l'insertion
     */
    public function insertData($data, $table, $references = []) {
        $columns = $this->getColumns($table);
        $columnNames = array_keys($columns);
        $filePath = $this->generateFileName($table);

        $sql = "INSERT INTO `$table` (" . implode(',', $columnNames) . ") VALUES (" . implode(',', array_fill(0, count($columnNames), '?')) . ")";
        $stmt = $this->prepareStatement($sql);

        $ids = !empty($references) ? $this->fetchReferences($data, $references, $table) : [];
        $errors = [];

        foreach ($data as $row) {
            $values = [];
            $types = '';

            foreach ($columnNames as $column) {
                if (isset($references[$column])) {
                    $refTable = $references[$column];
                    $refValue = $row[$column] ?? '';
                    $values[] = $ids[$refTable][$refValue] ?? null;
                } else {
                    $values[] = !empty($row[$column]) ? $row[$column] : null;
                }
                $types .= $this->getColumnType($columns[$column]);
            }

            $this->generateAndWriteInsertSql($table, $columnNames, $values, $filePath);

            $stmt->bind_param($types, ...$values);
            if (!$stmt->execute()) {
                $errors[] = "Erreur d'insertion pour la ligne : " . json_encode($row) . " - " . $stmt->error;
            }
        }

        if (!empty($errors)) {
            throw new Exception("Des erreurs d'insertion sont survenues : " . implode(', ', $errors));
        }
    }

    /**
     * Génère un nom de fichier pour stocker les requêtes SQL
     *
     * @param string $table Le nom de la table
     * @param string $prefix Le préfixe du fichier
     * @param string $suffix Le suffixe du fichier
     * @return string Le chemin complet du fichier généré
     */
    public function generateFileName($table, $prefix = 'data_', $suffix = '.sql') {
        $directory = 'sql'; 
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);  
        }
        return "{$directory}/data.sql"; 
    }
    
    /**
     * getColumnType
     * Renvoie le type de données attendu pour le bind_param basé sur le type de la colonne.
     * 
     * @param string $columnType Le type de la colonne
     * @return string Le type de données attendu pour bind_param
     */
    public function getColumnType($columnType) {
        if (strpos($columnType, 'int') !== false) {
            return 'i';
        } elseif (strpos($columnType, 'float') !== false || strpos($columnType, 'double') !== false || strpos($columnType, 'decimal') !== false) {
            return 'd';
        } else {
            return 's';
        }
    }

    /**
 * Classifie les données en fonction des colonnes de la base de données et du mappage de colonnes CSV.
 * 
 * @param array $data Les données provenant du fichier CSV.
 * @param string $tableName Le nom de la table dans la base de données.
 * @param array $columnMapping Le mappage entre les colonnes du CSV et les colonnes de la base de données.
 * @return array Les données classées après vérification et traitement.
 * @throws Exception Si le nombre de colonnes ne correspond pas.
 */
public function classifyData($data, $tableName, $columnMapping = []) {
    // Vérifier que le nombre de colonnes dans le CSV correspond à celui de la base de données
    $this->verifyColumnCount($tableName, $columnMapping);

    // Tableau pour stocker les données classées
    $classifiedData = [];

    // Récupérer les colonnes de la table
    $columns = $this->getColumns($tableName);

    // Tableau pour stocker les valeurs uniques de chaque colonne
    $uniqueValues = [];
    foreach ($columns as $dbColumn) {
        $uniqueValues[$dbColumn] = [];
    }

    // Parcourir les données pour identifier les valeurs uniques dans chaque colonne
    foreach ($data as $row) {
        foreach ($columns as $dbColumn) {
            // Rechercher la colonne correspondante dans le CSV via le mappage
            $csvColumn = array_search($dbColumn, $columnMapping);
            if ($csvColumn !== false && isset($row[$csvColumn])) {
                $uniqueValues[$dbColumn][$row[$csvColumn]] = true;
            }
        }
    }

    // Filtrer les données en fonction des valeurs uniques détectées
    foreach ($data as $row) {
        $filteredRow = [];
        foreach ($columns as $dbColumn) {
            $csvColumn = array_search($dbColumn, $columnMapping);
            $value = $csvColumn !== false && isset($row[$csvColumn]) ? $row[$csvColumn] : '';

            // Si la valeur est unique, on l'ajoute à la ligne filtrée
            if (isset($uniqueValues[$dbColumn][$value])) {
                $filteredRow[$dbColumn] = $value;
            } else {
                // Sinon, on laisse la valeur vide
                $filteredRow[$dbColumn] = '';
            }
        }

        // Ajouter la ligne filtrée aux données classées si elle n'est pas déjà présente
        if (!in_array($filteredRow, $classifiedData)) {
            $classifiedData[] = $filteredRow;
        }
    }

    // Retourner les données classées
    return $classifiedData;
}


}
?>
