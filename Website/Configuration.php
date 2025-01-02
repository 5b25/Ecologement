<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_sensor') {
        $ip = $_POST['ip'];
        $commerciale = $_POST['commerciale'];
        $lieu = $_POST['lieu'];
        $port = intval($_POST['port']);
        $piece_id = intval($_POST['piece_id']);
        $type_id = intval($_POST['type_id']);

        // 发送请求到 FastAPI 服务器
        $url = "http://localhost:8000/addcapture/$ip/$commerciale/$lieu/$port/$piece_id/$type_id";
        $response = file_get_contents($url);
        $responseMessage = json_decode($response, true);
    }

    if ($action === 'add_sensor_data') {
        $sensor_id = intval($_POST['sensor_id']);
        $value = $_POST['value'];
        $date_creation = $_POST['date_creation'];

        // 发送请求到 FastAPI 服务器
        $url = "http://localhost:8000/addsensordata/$sensor_id/$value/$date_creation";
        $response = file_get_contents($url);
        $dataResponseMessage = json_decode($response, true);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Configuration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            height: 100vh;
        }

        .section {
            width: 80%;
            margin: auto;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        form label, form input, form button {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Configuration</h2>
    <div class="container">
        <!-- 第一部分：添加传感器 -->
        <div class="section">
            <h3>Ajouter un capteur</h3>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_sensor'): ?>
                <div style="padding: 10px; border: 1px solid green; background-color: #f0fff0;">
                    <h4>Résultat:</h4>
                    <p><?php echo htmlspecialchars($responseMessage['message'] ?? 'Erreur lors de la communication avec le serveur.'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <input type="hidden" name="action" value="add_sensor">
                <label>IP:</label>
                <input type="text" name="ip" required>
                <label>Commerciale:</label>
                <input type="text" name="commerciale" required>
                <label>Lieu:</label>
                <input type="text" name="lieu" required>
                <label>Port:</label>
                <input type="number" name="port" required>
                <label>PIECE_ID:</label>
                <input type="number" name="piece_id" required>
                <label>TYPE_ID:</label>
                <input type="number" name="type_id" required>
                <button type="submit">Ajouter</button>
            </form>
        </div>

        <!-- 第二部分：添加传感器数据 -->
        <div class="section">
            <h3>Ajouter des données de capteur</h3>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_sensor_data'): ?>
                <div style="padding: 10px; border: 1px solid green; background-color: #f0fff0;">
                    <h4>Résultat:</h4>
                    <p><?php echo htmlspecialchars($dataResponseMessage['message'] ?? 'Erreur lors de la communication avec le serveur.'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <input type="hidden" name="action" value="add_sensor_data">
                <label>Capteur ID:</label>
                <input type="number" name="sensor_id" required>
                <label>Valeur:</label>
                <input type="text" name="value" required>
                <label>Date de création:</label>
                <input type="datetime-local" name="date_creation" required>
                <button type="submit">Ajouter</button>
            </form>
        </div>
    </div>
</body>
</html>
