<?php
$data = file_get_contents("http://localhost:8000/getmesure/");
$capteursData = json_decode($data, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Capteurs/Actionneurs</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h2 style="text-align: center;">Capteurs/Actionneurs</h2>
    <ul style="width: 80%; margin: auto; list-style-type: none; padding: 0;">
        <?php foreach ($capteursData as $sensor): ?>
            <li style="padding: 10px; border-bottom: 1px solid #ccc;">
                Capteur ID: <?php echo $sensor['CA_ID']; ?> - Valeur: <?php echo $sensor['VALEUR']; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
