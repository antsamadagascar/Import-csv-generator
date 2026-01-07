<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Edit - Clients</title>
</head>
<body>
<h1>Modifier un clients</h1>
<form action='<?php echo site_url("clientscontroller/edit/" . $item->id); ?>' method='post'>
    <input type='hidden' name='id' value='<?php echo $item->id; ?>'>
    <label for='car_number'>Car_number:</label>
    <input type='text' id='car_number' name='car_number' value='<?php echo $item->car_number; ?>' required>
    <label for='car_type_id'>Car_type_id:</label>
    <input type='number' id='car_type_id' name='car_type_id' value='<?php echo $item->car_type_id; ?>' required>
    <label for='first_login'>First_login:</label>
    <input type='text' id='first_login' name='first_login' value='<?php echo $item->first_login; ?>' required>
    <label for='id'>Id:</label>
    <input type='number' id='id' name='id' value='<?php echo $item->id; ?>' required>
    <label for='car_number'>Car_number:</label>
    <input type='text' id='car_number' name='car_number' value='<?php echo $item->car_number; ?>' required>
    <label for='car_type_id'>Car_type_id:</label>
    <input type='number' id='car_type_id' name='car_type_id' value='<?php echo $item->car_type_id; ?>' required>
    <label for='first_login'>First_login:</label>
    <input type='text' id='first_login' name='first_login' value='<?php echo $item->first_login; ?>' required>
    <label for='id'>Id:</label>
    <input type='number' id='id' name='id' value='<?php echo $item->id; ?>' required>
    <label for='car_number'>Car_number:</label>
    <input type='text' id='car_number' name='car_number' value='<?php echo $item->car_number; ?>' required>
    <label for='car_type_id'>Car_type_id:</label>
    <input type='number' id='car_type_id' name='car_type_id' value='<?php echo $item->car_type_id; ?>' required>
    <label for='first_login'>First_login:</label>
    <input type='text' id='first_login' name='first_login' value='<?php echo $item->first_login; ?>' required>
    <button type='submit'>Update</button>
</form>
