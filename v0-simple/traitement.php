<?php
include 'read_generator.php';
include 'classify_generator.php';
include 'insert_generator.php';
include 'table_html.php';
include 'conn.php';

//$service_csv = $_POST['service_csv'];
$travaux_csv = $_POST['travaux_csv'];

//$preprocessedDataService = read_csv($service_csv, 'UTF-8');
$preprocessedDataTravaux = read_csv($travaux_csv, 'UTF-8');

//echo arrayToHtmlTable($preprocessedDataService, 'Classifiés');
//echo arrayToHtmlTable($preprocessedDataTravaux, 'Classifiés');

/*
$service_mappings = [
    'Services' =>[
        'columns' => [
            'service' => 'service_name',
            'duree' => 'duration',
        ],
    ],
];
*/

$travaux_mappings = [
    'type_maison' => [
        'columns' => [
            'type_maison' => 'type_maison',
            'description' => 'description',
            'surface' => 'surface',
        ],
    ], 

    'code_travaux' => [
        'columns' => [
            'code_travaux' => 'code_travaux',
            'type_travaux' => 'type_travaux',
            'prix_unitaire'=> 'prix_unitaire',
        ],
    ],

    'unite' => [
        'columns' => [
            'unit?' => 'unite',
        ],
    ], 
    
    'Travaux' => [
        'columns' => [
            'type_maison' => 'id_maison',
            'code_travaux' => 'id_travaux',
            'unit?' => 'id_unite',
            'quantite' => 'quantite',
            'duree_travaux' => 'duree',
        ],
        'references' => [
            'id_maison' => 'type_maison',
            'id_travaux' => 'code_travaux',
            'id_unite' => 'unite',
        ],
    ],
    
];
/*
try {
    foreach ($service_mappings as $table => $mapping) {
        $classifiedData = classifyData($preprocessedDataService, $table, $conn, $mapping['columns']);
        
        if (isset($mapping['references'])) {
            $ids = fetchReferences($classifiedData, $mapping['references'], $conn);
            echo "<h3>Données classifiées pour la table $table</h3>";
            echo arrayToHtmlTable($classifiedData, ucfirst($table) . ' Classifiés');
            insertData($classifiedData, $table, $conn, $mapping['references']);
        } else {
            echo "<h3>Données classifiées pour la table $table</h3>";
            echo arrayToHtmlTable($classifiedData, ucfirst($table) . ' Classifiés');
            insertData($classifiedData, $table, $conn);
        }
    }

} catch (Exception $e) {
    echo "<p>Erreur : " . $e->getMessage() . "</p>";
}
*/
try {
    foreach ($travaux_mappings as $table => $mapping) {
        $classifiedData = classifyData($preprocessedDataTravaux, $table, $conn, $mapping['columns']);
     /* 
        echo "<pre>";
        echo "<h3>Données classifiées pour la table $table</h3>";
        print_r($classifiedData);
        echo "</pre>";
      */  
        if (isset($mapping['references'])) {
            $ids = fetchReferences($classifiedData, $mapping['references'], $conn);
            
            /*
            echo "<pre>";
            echo "<h3>Les id referencer dans la table  $table recuperer avec succees</h3>";
            print_r($ids);
            echo "</pre>";
            */
            //echo "<h3>Données classifiées pour la table $table</h3>";
          //  echo arrayToHtmlTable($classifiedData, ucfirst($table) . ' Classifiés');
            insertData($classifiedData, $table, $conn, $mapping['references']);
        } else {
          //  echo "<h3>Données classifiées pour la table $table</h3>";
           // echo arrayToHtmlTable($classifiedData, ucfirst($table) . ' Classifiés');
           insertData($classifiedData, $table, $conn);
        }
    }

} catch (Exception $e) {
    echo "<p>Erreur : " . $e->getMessage() . "</p>";
}

$conn->close();
?>
