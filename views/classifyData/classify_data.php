<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Données Classifiées</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h3>Données pour la table <?php echo ucfirst($tableName); ?></h3>
<table>
    <thead>
        <tr>
            <?php foreach (array_keys($classifiedData[0]) as $column): ?>
                <th><?php echo $column; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($classifiedData as $row): ?>
            <tr>
                <?php foreach ($row as $cell): ?>
                    <td><?php echo $cell; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
