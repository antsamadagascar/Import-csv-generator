<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contrôleur pour l'importation simple de fichiers CSV
 *
 * Ce contrôleur gère l'importation séparée de fichiers CSV pour les données non-mouvement et de mouvement.
 * Lors de l'utilisation de ce contrôleur, voici les éléments que vous devez adapter :
 * 
 * 1. **Configuration de téléchargement :**
 *    Modifiez le chemin du répertoire de téléchargement et les paramètres de taille maximale dans la méthode `get_upload_config` si nécessaire.
 * 
 * 2. **Mappings des tables :**
 *    Adaptez les mappings des colonnes et des références dans la méthode `get_table_mappings` pour correspondre aux colonnes de vos fichiers CSV et à la structure de votre base de données.
 * 
 * 3. **Gestion des types de données :**
 *    Vérifiez les noms des types de données (`non_movement` et `movement`) utilisés dans les méthodes `upload_non_movement` et `upload_movement`. Assurez-vous qu'ils correspondent aux types définis dans la méthode `get_table_mappings`.
 * 
 * 4. **Affichage des données classifiées :**
 *    Assurez-vous que la vue `classify_data` est correctement configurée pour afficher les données classifiées après l'insertion.
 *
 * Created by Aina Ny Antsa Ratovonandrasana.
 */

class Simple_import extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['form', 'url']);  // Charge les helpers pour les formulaires et les URL
        $this->load->library('CsvLibrary');  // Charge la bibliothèque personnalisée pour la gestion des CSV
        $this->load->model('ImportModel');  // Charge le modèle pour manipuler les données importées
    }

    /**
     * Affiche la vue principale pour l'importation simple
     */
    public function index()
    {
        $this->load->view('import/simple_import_view');  // Charge la vue pour l'interface utilisateur d'importation
    }

    /**
     * Traite l'importation des fichiers CSV pour les données non-mouvement
     */
    public function upload_non_movement()
    {
        $this->process_upload('non_movement_csv', 'non_movement');  // Appelle la méthode pour traiter le fichier CSV non-mouvement
    }

    /**
     * Traite l'importation des fichiers CSV pour les données de mouvement
     */
    public function upload_movement()
    {
        $this->process_upload('movement_csv', 'movement');  // Appelle la méthode pour traiter le fichier CSV de mouvement
    }

    /**
     * Gère le processus de téléchargement et de traitement des fichiers CSV
     *
     * @param string $file_input_name Le nom de l'input de fichier dans le formulaire
     * @param string $type Le type de données (non_movement ou movement)
     */
    private function process_upload($file_input_name, $type) {

        $config = $this->get_upload_config();  // Récupère la configuration pour le téléchargement des fichiers
        $this->load->library('upload', $config);  // Charge la bibliothèque d'upload avec cette configuration
        
        $errors = [];
        $successes = [];

        // Vérifie si le fichier a été téléchargé avec succès
        if ($this->upload->do_upload($file_input_name)) {
            $data = $this->upload->data();  // Récupère les informations du fichier téléchargé
            $file_path = $data['full_path'];  // Chemin complet vers le fichier téléchargé

            try {
                $preprocessedData = $this->csvlibrary->read_csv($file_path);  // Lecture du fichier CSV

                // Obtient les mappings des tables en fonction du type de données
                $tableMappings = $this->get_table_mappings($type);

                // Boucle à travers chaque table définie dans le mapping pour insérer les données
                foreach ($tableMappings as $table => $mapping) {
                    $classifiedData = $this->ImportModel->classifyData($preprocessedData, $table, $mapping['columns']);
                    
                    // Insère les données en tenant compte des éventuelles références
                    if (isset($mapping['references'])) {
                        $this->ImportModel->insertData($classifiedData, $table, $mapping['references']);
                    } else {
                        $this->ImportModel->insertData($classifiedData, $table);
                    }

                    $successes[] = "Data successfully inserted into table: $table";

                    // Prépare les données pour l'affichage dans la vue
                    $data['classifiedData'] = $classifiedData;
                    $data['tableName'] = $table;
                    $this->load->view('classifyData/classify_data', $data);  // Affiche les données classifiées
                }

            } catch (Exception $e) {
                $errors[] = "Error processing $type CSV: " . $e->getMessage();  // Capture et affiche les erreurs
            }
        } else {
            $errors[] = "Error uploading $type CSV: " . $this->upload->display_errors();  // Capture les erreurs de téléchargement
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
     * Obtient les mappings des tables en fonction du type de données
     * 
     * @param string $type Le type de données (non_movement ou movement)
     * @return array Mappings des colonnes et références de la base de données
     */
    private function get_table_mappings($type) {
        // Définissez ici vos mappings spécifiques pour chaque type de données(this is an example basique to test)
        $mappings = [
            'non_movement' => [  // Mappings pour les données non-mouvement
                'services' => [
                    'columns' => [
                        'service' => 'service',
                        'duree' => 'duree'
                    ],
                ]
            ],
            'movement' => [  // Mappings pour les données de mouvement
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

        return $mappings[$type];  // Retourne le mapping correspondant au type de données
    }
}
