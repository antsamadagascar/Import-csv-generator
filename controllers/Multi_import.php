<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contrôleur pour l'importation de plusieurs fichiers CSV
 *
 * Ce contrôleur gère l'importation simultanée de fichiers CSV pour les données de mouvement et non-mouvement.
 * Lors de l'utilisation de ce contrôleur, voici les éléments que vous devez adapter :
 * 
 * 1. **Configuration de téléchargement :** 
 *    Modifiez le chemin du répertoire de téléchargement et les paramètres de taille maximale dans la méthode `get_upload_config` si nécessaire.
 * 
 * 2. **Mappings des tables :** 
 *    Ajustez les mappings des colonnes et des références dans la méthode `get_table_mappings` pour correspondre aux colonnes de vos fichiers CSV et à la structure de votre base de données.
 * 
 * 3. **Gestion des fichiers CSV :**
 *    Vérifiez les noms des fichiers CSV (`non_movement_csv` et `movement_csv`) dans le formulaire et assurez-vous qu'ils correspondent aux noms des champs d'input dans votre formulaire d'importation.
 * 
 * 4. **Affichage des données classifiées :**
 *    Assurez-vous que la vue `classify_data` est correctement configurée pour afficher les données classifiées.
 *
 * Created by Aina Ny Antsa Ratovonandrasana@misaina.
 */

class Multi_import extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library('CsvLibrary'); 
        $this->load->model('ImportModel');  // Charge le modèle pour manipuler les données importées
    }

    /**
     * Affiche la vue principale pour l'importation multiple
     */
    public function index()
    {
        $this->load->view('import/multi_import_view');  // Charge la vue pour l'interface utilisateur d'importation
    }

    /**
     * Traite les fichiers CSV téléchargés
     */
    public function process() {
        $config = $this->get_upload_config();  // Récupère la configuration pour le téléchargement des fichiers
        $this->load->library('upload', $config);  // Charge la bibliothèque d'upload avec cette configuration
        
        $csv_files = ['non_movement_csv', 'movement_csv'];  // Liste des types de fichiers CSV à gérer
        $errors = [];
        $successes = [];

        // Boucle à travers chaque fichier CSV à traiter
        foreach ($csv_files as $file) {
            if ($this->upload->do_upload($file)) {  // Vérifie si le fichier a été téléchargé avec succès
                $uploadedData = $this->upload->data();  // Récupère les informations du fichier téléchargé
                $filePath = $uploadedData['full_path'];  // Chemin complet vers le fichier téléchargé
                
                try {
                    $preprocessedData = $this->csvlibrary->read_csv($filePath);  // Lecture du fichier CSV

                    // Obtenir dynamiquement le mapping des tables pour ce fichier spécifique
                    $tableMappings = $this->get_table_mappings($file);  
                    $successes[] = "Processing file: $file";

                    // Boucle à travers chaque table définie dans le mapping pour insérer les données
                    foreach ($tableMappings as $table => $mapping) {
                        $classifiedData = $this->ImportModel->classifyData($preprocessedData, $table, $mapping['columns']);
                        
                        // Si des références sont définies, elles sont utilisées lors de l'insertion des données
                        if (isset($mapping['references'])) {
                            $this->ImportModel->insertData($classifiedData, $table, $mapping['references']);
                        } else {
                            $this->ImportModel->insertData($classifiedData, $table);
                        }

                        $successes[] = "Data successfully inserted into table: $table";
                        
                        // Prépare les données pour l'affichage dans la vue
                        $data['classifiedData'] = $classifiedData;
                        $data['tableName'] = $table;

                        // Affichage des données classifiées
                        $this->load->view('classifyData/classify_data', $data);
                    }

                } catch (Exception $e) {
                    $errors[] = "Error processing $file CSV: " . $e->getMessage();  // Capture et affiche les erreurs
                }
            } else {
                $errors[] = "Error uploading $file CSV: " . $this->upload->display_errors();  // Capture les erreurs de téléchargement
            }
        }

        // Affichage des messages de succès
        if (!empty($successes)) {
            foreach ($successes as $success) {
                echo "<p style='color:green;'>$success</p>";
            }
        }

        // Affichage des messages d'erreur
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p style='color:red;'>$error</p>";
            }
        }

        // Message si aucun succès ou erreur n'a été enregistré
        if (empty($errors) && empty($successes)) {
            echo "<p style='color:green;'>Both CSV files processed successfully.</p>";
        }
    }

    /**
     * Configuration pour le téléchargement des fichiers CSV
     * 
     * @return array Configuration d'upload pour CodeIgniter
     */
    private function get_upload_config() {
        return [
            'upload_path'   => './uploads/',  // Chemin où les fichiers téléchargés seront stockés
            'allowed_types' => 'csv',  // Types de fichiers autorisés
            'max_size'      => 2048  // Taille maximale des fichiers (2 MB)
        ];
    }

    /**
     * Obtenir les mappings des tables en fonction du type de fichier CSV
     * 
     * @param string $file_type Le type de fichier CSV (non_movement_csv ou movement_csv)
     * @return array Mappings des colonnes et références de la base de données
     */
    private function get_table_mappings($file_type) {
        // Définissez ici vos mappings spécifiques pour chaque type de fichier CSV
        $mappings = [
            'non_movement_csv' => [  // Mappings pour le fichier non_movement_csv
                'services' => [
                    'columns' => [
                        'service' => 'service',
                        'duree' => 'duree'
                    ],
                ]
            ],
            'movement_csv' => [  // Mappings pour le fichier movement_csv
                'type_voiture' => [
                    'columns' => [
                        'type voiture' => 'type_voiture',
                    ],
                ],
                'clients' => [
                    'columns' => [
                        'voiture' => 'nom_Voiture',
                        'type voiture' => 'type_voiture_id',
                    ],
                    'references' => [
                        'type_voiture_id' => 'type_voiture'
                    ],
                ],
                'mouvement_service' => [
                    'columns' => [
                        'voiture' => 'id_client',
                        'date rdv' => 'date_rdv',
                        'heure rdv' => 'heure_rdv',
                        'type service' => 'id_service',
                        'montant' => 'montant',
                        'date paiement' => 'date_paiement',
                    ],
                    'references' => [
                        'id_client' => 'clients',
                        'id_service' => 'services',
                    ],
                ]
            ]
        ];

        return $mappings[$file_type];  // Retourne le mapping correspondant au type de fichier
    }
}
