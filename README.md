# Import-csv-generator
README: Importation Générique de Fichiers CSV dans MySQL
1. Configuration
Installation du Framework

    Installez CodeIgniter 3.

Configuration de l'URL de Base

    Modifiez le fichier application/config/config.php :
    $config['base_url'] = 'your_url';
Configuration de la Base de Données

    Modifiez le fichier application/config/database.php :
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'testImport',
    'dbdriver' => 'mysqli',

    $route['default_controller'] = 'import';
    
Configuration de l'Autoload

    Modifiez le fichier application/config/autoload.php :
        Ajoutez ou décommentez (si déjà existant) :
        $autoload['helper'] = array('url');
2. Installation des Fichiers
Copiez les Fichiers de Contrôleurs

    Copiez les fichiers suivants dans application/controllers :
        Import.php
        Multi_import.php
        Simple_import.php

Copiez la Bibliothèque CSV

    Copiez CsvLibrary.php dans application/libraries.

Copiez le Modèle

    Copiez ImportModel.php dans application/models.

Copiez les Vues

    Copiez les dossiers classifyData, errors (si absent dans views), import, et le fichier index.php dans application/views.

Copiez les Dossiers Supplémentaires

    Copiez les dossiers uploads et sql en dehors du dossier application.

3. Utilisation
Accès à l'Interface

    Lorsque vous accédez à votre projet CodeIgniter, deux liens doivent apparaître :
        Simple Import :
        Permet d'importer un seul fichier CSV, qu'il s'agisse de données de mouvement ou non.
            Exemple : Importation du fichier service.csv dans une table non mouvement et du fichier travaux.csv dans une table mouvement, avec insertion possible dans d'autres tables non mouvement.
        Multi Import :
        Permet d'importer plusieurs fichiers CSV simultanément, qu'ils soient de type mouvement ou non mouvement.
        Après le traitement (upload), les données seront classifiées et insérées dans les tables respectives.

Mapping des CSV vers les Tables

    Dans chacun des contrôleurs Simple_import et Multi_import, il existe une fonction appelée get_table_mappings.
    Vous pouvez définir les correspondances (mappings) entre vos fichiers CSV et les tables de la base de données :
    $mappings = [
    'non_movement_csv' => [  // Mappings pour le fichier non_movement_csv
        'your_table' => [
            'columns' => [
                'col1_csv' => 'col1_table',
                'col2_csv' => 'col2_table',
                // etc.
            ],
        ]
    ],
    'movement_csv' => [  // Mappings pour le fichier movement_csv
        'your_table' => [
            'columns' => [
                'col1_csv' => 'col1_table',
                'col2_csv' => 'col2_table',
                // etc.
            ],
            'references' => [
                'col1_table' => 'your_table_reference',
                'col2_table' => 'your_table_reference',
                // etc.
            ],
        ]
    ]
];

