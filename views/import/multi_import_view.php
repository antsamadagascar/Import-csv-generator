<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi Import</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input[type="file"] {
            display: block;
            margin-top: 5px;
        }
        .form-group input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .info {
            margin-top: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-left: 5px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Multiple CSV Files</h1>
        <?php if (isset($error)) { echo '<p style="color:red;">' . $error . '</p>'; } ?>
        <form action="<?php echo site_url('multi_import/process'); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="non_movement_csv">Upload Non-Movement CSV:</label>
                <input type="file" id="non_movement_csv" name="non_movement_csv" accept=".csv" required>
            </div>
            <div class="form-group">
                <label for="movement_csv">Upload Movement CSV:</label>
                <input type="file" id="movement_csv" name="movement_csv" accept=".csv" required>
            </div>
            <input type="submit" value="Upload Both CSVs">
        </form>
        <div class="info">
            <p>Please upload both CSV files for non-movement and movement data. Each file will be processed accordingly.</p>
        </div>
    </div>
</body>
</html>
