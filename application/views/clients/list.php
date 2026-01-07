<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>List - Clients</title>
</head>
<body>
<h1>Liste des clientss</h1>
<table border= '1'>
    <tr>
        <th>Id</th>
        <th>Car_number</th>
        <th>Car_type_id</th>
        <th>First_login</th>
        <th>Id</th>
        <th>Car_number</th>
        <th>Car_type_id</th>
        <th>First_login</th>
        <th>Id</th>
        <th>Car_number</th>
        <th>Car_type_id</th>
        <th>First_login</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($clients as $item): ?>
    <tr>
        <td><?php echo $item->id; ?></td>
        <td><?php echo $item->car_number; ?></td>
        <td><?php echo $item->car_type_id; ?></td>
        <td><?php echo $item->first_login; ?></td>
        <td><?php echo $item->id; ?></td>
        <td><?php echo $item->car_number; ?></td>
        <td><?php echo $item->car_type_id; ?></td>
        <td><?php echo $item->first_login; ?></td>
        <td><?php echo $item->id; ?></td>
        <td><?php echo $item->car_number; ?></td>
        <td><?php echo $item->car_type_id; ?></td>
        <td><?php echo $item->first_login; ?></td>
        <td>
            <a href='<?php echo site_url("clientscontroller/edit/" . $item->id); ?>'>Edit</a> |
            <a href='<?php echo site_url("clientscontroller/delete/" . $item->id); ?>'>Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<p><a href='<?php echo site_url("clientscontroller/create/"); ?>'>Ajouter un nouveau  clientss</p>
</body>
</html>
