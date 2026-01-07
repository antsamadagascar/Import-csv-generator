<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
div {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f0f0f0;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

form {
    margin-bottom: 20px;
}

input[type="file"] {
    display: block;
    margin-bottom: 10px;
}

input[type="hidden"] {
    display: none;
}

input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

input[type="submit"]:hover {
    background-color: #45a049;
}

p {
    margin-top: 20px;
    color: #333;
}

    </style>
<body>
<div>
    <form  action="<?php echo site_url('CsvController/import'); ?>" method="POST" >
    <h1>Import Des Donnees</h1>
        <input type="file" name="csv_file" required />
        <input type="submit" value="Import" />
    </form>
</div>
    
</body>
</html>

