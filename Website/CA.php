<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 获取 logement 名称列表
$logementApiUrl = "http://localhost:8000/getlogements/";
$curl = curl_init($logementApiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
$logementData = curl_exec($curl);
if (curl_errno($curl)) {
    die("Erreur : Impossible de se connecter au serveur FastAPI. " . curl_error($curl));
}
curl_close($curl);

$logements = json_decode($logementData, true);
if (!$logements) {
    die("Erreur : Impossible de charger la liste des logements.");
}

// 获取 GET 参数
$selectedLogement = $_GET['logement'] ?? ''; // 所选 logement 名称

// 构造 API 请求 URL
$apiUrl = "http://localhost:8000/getmesureDetail/";
if (!empty($selectedLogement)) {
    $apiUrl .= "?logement=" . urlencode($selectedLogement);
}

// 使用 curl 请求 API
$curl = curl_init($apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
$data = curl_exec($curl);
if (curl_errno($curl)) {
    die("Erreur : Impossible de se connecter au serveur FastAPI. " . curl_error($curl));
}
curl_close($curl);

$capteursData = json_decode($data, true);
if (!$capteursData) {
    echo '<div style="text-align: center;">暂无数据。</div>';
    exit;
}

// 按 LIEU 分组并区分数据类型
$groupedData = [];
foreach ($capteursData as $sensor) {
    $lieu = $sensor['LIEU'] ?? '未知地点'; // 检查 LIEU 是否存在
    $dataType = $sensor['DATA_TYPE'] ?? '未知类型'; // 检查 DATA_TYPE 是否存在

    if (!isset($groupedData[$lieu])) {
        $groupedData[$lieu] = [];
    }
    if (!isset($groupedData[$lieu][$dataType])) {
        $groupedData[$lieu][$dataType] = [];
    }
    $groupedData[$lieu][$dataType][] = $sensor;
}
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
    <form method="GET" action="" style="width: 80%; margin: auto; text-align: center; margin-bottom: 20px;">
        <label for="logement">Sélectionnez un logement:</label>
        <select id="logement" name="logement">
            <option value="">-- Tout logement --</option>
            <?php foreach ($logements as $logement): ?>
                <option value="<?php echo htmlspecialchars($logement['NOM']); ?>" 
                    <?php echo ($selectedLogement === $logement['NOM']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($logement['NOM']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">filtre</button>
    </form>

    <div style="width: 80%; margin: auto;">
        <?php foreach ($groupedData as $lieu => $types): ?>
            <h3>Emplacement de l'équipement: <?php echo htmlspecialchars($lieu); ?></h3>
            <?php foreach ($types as $dataType => $sensors): ?>
                <h4><?php echo htmlspecialchars($dataType); ?></h4>
                <ul style="list-style-type: none; padding: 0;">
                    <?php foreach ($sensors as $sensor): ?>
                        <li style="padding: 10px; border-bottom: 1px solid #ccc;">
                            Capteur ID: <?php echo htmlspecialchars($sensor['CA_ID']); ?> - Valeur: <?php echo htmlspecialchars($sensor['VALEUR']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>