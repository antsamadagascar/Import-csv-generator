<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Options</title>
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
        .links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .link-item {
            text-align: center;
            margin: 10px 0;
        }
        .link-item a {
            text-decoration: none;
            font-size: 18px;
            color: #007bff;
            padding: 10px 20px;
            border: 2px solid #007bff;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .link-item a:hover {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Import Options</h1>
        <div class="links">
            <div class="link-item">
                <a href="<?php echo site_url('simple_import'); ?>">Simple Import</a>
            </div>
            <div class="link-item">
                <a href="<?php echo site_url('multi_import'); ?>">Multi Import</a>
            </div>
        </div>
    </div>
</body>
</html>
