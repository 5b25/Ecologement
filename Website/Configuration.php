<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Configuration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h2 style="text-align: center;">Configuration</h2>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div style="width: 80%; margin: auto; padding: 10px; border: 1px solid green; background-color: #f0fff0;">
            <h3>Résultat:</h3>
            <p><?php echo htmlspecialchars($responseMessage['message'] ?? 'Erreur lors de la communication avec le serveur.'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="" style="width: 50%; margin: auto;">
        <label>IP:</label>
        <input type="text" name="ip" required><br>
        <label>Commerciale:</label>
        <input type="text" name="commerciale" required><br>
        <label>Lieu:</label>
        <input type="text" name="lieu" required><br>
        <label>Port:</label>
        <input type="number" name="port" required><br>
        <label>PIECE_ID:</label>
        <input type="number" name="piece_id" required><br>
        <label>TYPE_ID:</label>
        <input type="number" name="type_id" required><br>
        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
