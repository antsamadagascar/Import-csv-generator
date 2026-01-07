<!-- application/views/upload_csv.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Importation CSV</title>
</head>
<body>
    <h1>Importer des fichiers CSV</h1>

    <!-- Formulaire pour télécharger les services CSV -->
    <form action="<?= site_url('CsvController/import'); ?>" method="post" enctype="multipart/form-data">
        <h2>Importer les services</h2>
        <input type="file" name="csv_file" accept=".csv" />
        <input type="submit" value="Télécharger les services CSV" />
    </form>

    <hr>

    <!-- Formulaire pour télécharger les données des rendez-vous CSV -->
    <form action="<?= site_url('CsvController/import'); ?>" method="post" enctype="multipart/form-data">
        <h2>Importer les données des rendez-vous</h2>
        <input type="file" name="csv_file" accept=".csv" />
        <input type="submit" value="Télécharger les données des rendez-vous CSV" />
    </form>
</body>
</html>
