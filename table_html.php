<?php


function arrayToHtmlTable($data, $title = 'Tableau') {
    if (empty($data)) {
        return "<p>Aucune donnée à afficher.</p>";
    }

    $html = "<h2>$title</h2>";
    $html .= "<table border='1' cellpadding='5' cellspacing='0'>";
    
    $html .= "<tr>";
    foreach (array_keys($data[0]) as $header) {
        $html .= "<th>" . htmlspecialchars($header) . "</th>";
    }
    $html .= "</tr>";
    
    foreach ($data as $row) {
        $html .= "<tr>";
        foreach ($row as $cell) {
            $html .= "<td>" . htmlspecialchars($cell) . "</td>";
        }
        $html .= "</tr>";
    }
    
    $html .= "</table>";
    
    return $html;
}

?>