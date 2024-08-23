<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CsvLibrary
 * 
 * Cette bibliothèque permet de lire des fichiers CSV, gérer les valeurs nulles, et convertir des dates.
 * Créé par Aina Ny Antsa Ratovonandrasana.
 *
 * Usage:
 * - Vous pouvez configurer l'encodage cible, le remplacement des valeurs nulles, et les formats de date via le constructeur.
 * - Appelez la méthode `read_csv($file_path)` pour lire un fichier CSV et obtenir les données traitées.
 *
 * Configuration possible lors de l'initialisation :
 * - 'encoding' : spécifier l'encodage cible pour le contenu du fichier (par défaut : 'UTF-8').
 * - 'null_replacement' : spécifier la valeur de remplacement pour les cellules nulles (par défaut : chaîne vide).
 * - 'date_formats' : spécifier les formats de date à détecter et à convertir (par défaut : 'Y-m-d', 'd/m/Y', 'd-m-Y').
 */

class CsvLibrary {

    // Options par défaut
    private $targetEncoding = 'UTF-8';  // Encodage cible pour le contenu du fichier CSV
    private $nullReplacement = '';  // Remplacement pour les valeurs nulles dans le CSV
    private $dateFormats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];  // Formats de date acceptés pour la conversion

    /**
     * __construct
     * 
     * Permet de redéfinir les options par défaut via le constructeur.
     *
     * @param array $config  Tableau d'options pour personnaliser l'encodage, la gestion des nulls, et les formats de date.
     */
    public function __construct($config = []) {
        if (!empty($config)) {
            $this->targetEncoding = $config['encoding'] ?? $this->targetEncoding;
            $this->nullReplacement = $config['null_replacement'] ?? $this->nullReplacement;
            $this->dateFormats = $config['date_formats'] ?? $this->dateFormats;
        }
    }

    /**
     * read_csv
     * 
     * Lit le fichier CSV et retourne les données traitées.
     *
     * @param string $file_path  Chemin vers le fichier CSV à lire.
     * @return array  Données du fichier CSV sous forme de tableau associatif.
     * @throws Exception  Si le fichier n'existe pas ou ne peut être ouvert.
     */
    public function read_csv($file_path) {
        $data = [];

        if (!file_exists($file_path)) {
            throw new Exception("Le fichier n'existe pas.");
        }

        // Ouvrir le fichier en mode lecture
        $file = fopen($file_path, 'r');
        if ($file === false) {
            throw new Exception("Impossible d'ouvrir le fichier.");
        }

        // Lire un échantillon pour détecter le délimiteur
        $sample = fread($file, 4096);
        fclose($file);

        // Détection du délimiteur (par exemple, virgule, point-virgule, tabulation)
        $delimiters = [',', ';', "\t", '|'];
        $detected_delimiter = $this->detect_delimiter($sample, $delimiters);

        // Réouvrir le fichier pour lecture complète
        $file = fopen($file_path, 'r');
        if ($file === false) {
            throw new Exception("Impossible d'ouvrir le fichier.");
        }

        // Lire l'en-tête et le convertir en UTF-8
        $header = fgetcsv($file, 0, $detected_delimiter);
        if ($header === false) {
            throw new Exception("Impossible de lire l'en-tête du fichier.");
        }

        // Détection de l'encodage du fichier
        $encoding = mb_detect_encoding(implode('', $header), "UTF-8, ISO-8859-1, ISO-8859-15", true);
        if ($encoding !== $this->targetEncoding) {
            $header = array_map(function($header) use ($encoding) {
                return mb_convert_encoding($header, $this->targetEncoding, $encoding);
            }, $header);
        }

        // Lire les données du fichier
        while (($row = fgetcsv($file, 0, $detected_delimiter)) !== false) {
            $row = array_map(function($cell) use ($encoding) {
                return mb_convert_encoding($cell, $this->targetEncoding, $encoding);
            }, $row);
            $data[] = array_combine($header, $row);
        }

        fclose($file);

        // Traiter les valeurs nulles dans les données
        $data = $this->handle_null_values($data);

        // Conversion des dates dans les colonnes appropriées
        $data = $this->convert_dates($data);

        return $data;
    }

    /**
     * detect_delimiter
     * 
     * Détecte automatiquement le délimiteur dans un échantillon de texte CSV.
     *
     * @param string $sample  Échantillon de texte du fichier CSV.
     * @param array $delimiters  Liste des délimiteurs possibles.
     * @return string  Le délimiteur détecté.
     */
    private function detect_delimiter($sample, $delimiters) {
        foreach ($delimiters as $delimiter) {
            $rows = explode("\n", $sample);
            $first_row = str_getcsv($rows[0], $delimiter);
            if (count($first_row) > 1) {
                return $delimiter;
            }
        }
        return ',';  // Par défaut, on retourne une virgule
    }

    /**
     * handle_null_values
     * 
     * Remplace les valeurs nulles par la valeur définie dans $nullReplacement.
     *
     * @param array $data  Données CSV à traiter.
     * @return array  Données avec valeurs nulles remplacées.
     */
    private function handle_null_values($data) {
        foreach ($data as &$row) {
            foreach ($row as $key => $value) {
                $row[$key] = $value === null ? $this->nullReplacement : $value;
            }
        }
        return $data;
    }

    /**
     * convert_dates
     * 
     * Convertit les chaînes de date dans les colonnes contenant le mot 'date' au format 'Y-m-d'.
     *
     * @param array $data  Données CSV à traiter.
     * @return array  Données avec dates converties.
     */
    private function convert_dates($data) {
        foreach ($data as &$row) {
            foreach ($row as $key => $value) {
                if (strpos($key, 'date') !== false && $value) {
                    $row[$key] = $this->parse_date($value);
                }
            }
        }
        return $data;
    }

    /**
     * parse_date
     * 
     * Tente de convertir une chaîne de date en utilisant les formats spécifiés.
     *
     * @param string $dateString  Chaîne de date à convertir.
     * @return string  Date convertie au format 'Y-m-d' ou la chaîne d'origine si aucun format ne correspond.
     */
    private function parse_date($dateString) {
        foreach ($this->dateFormats as $format) {
            $dateTime = DateTime::createFromFormat($format, $dateString);
            if ($dateTime) {
                return $dateTime->format('Y-m-d');
            }
        }
        return $dateString;  // Retourne la chaîne d'origine si aucun format ne correspond
    }
}
