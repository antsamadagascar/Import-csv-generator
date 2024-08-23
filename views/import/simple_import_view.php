<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Import</title>
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
            margin-top: 20px;
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
        <h1>Upload CSV Files</h1>
        
        <!-- Form for non-movement data -->
        <div class="form-group">
            <form action="<?php echo site_url('simple_import/upload_non_movement'); ?>" method="post" enctype="multipart/form-data">
                <label for="non_movement_csv">Upload Non-Movement CSV:</label>
                <input type="file" id="non_movement_csv" name="non_movement_csv" accept=".csv" required>
                <input type="submit" value="Upload Non-Movement CSV">
            </form>
        </div>
        
        <!-- Form for movement data -->
        <div class="form-group">
            <form action="<?php echo site_url('simple_import/upload_movement'); ?>" method="post" enctype="multipart/form-data">
                <label for="movement_csv">Upload Movement CSV:</label>
                <input type="file" id="movement_csv" name="movement_csv" accept=".csv" required>
                <input type="submit" value="Upload Movement CSV">
            </form>
        </div>

        <div class="info">
            <p>Please upload the CSV files for non-movement and movement data separately. Each form handles different types of data.</p>
        </div>
    </div>
</body>
</html>
