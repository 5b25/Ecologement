<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 获取 logement 名称列表
$logementApiUrl = "http://localhost:8000/getlogements/";
$curl = curl_init($logementApiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置最大请求时间为 10 秒
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
$type = $_GET['type'] ?? 'all';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$selectedLogement = $_GET['logement'] ?? '';

// 检查日期格式
$validDatePattern = '/^\d{4}-\d{2}-\d{2}$/';
if ($startDate && !preg_match($validDatePattern, $startDate)) {
    die("Erreur : La date de début n'est pas valide.");
}
if ($endDate && !preg_match($validDatePattern, $endDate)) {
    die("Erreur : La date de fin n'est pas valide.");
}

// 构造 API URL
$apiUrl = "http://localhost:8000/getfactureDetail/";
$queryParams = [];
if ($type !== 'all') {
    $queryParams['type'] = $type;
}
if ($startDate) {
    $queryParams['start_date'] = $startDate;
}
if ($endDate) {
    $queryParams['end_date'] = $endDate;
}
if ($selectedLogement) {
    $queryParams['logement'] = $selectedLogement;
}

if (!empty($queryParams)) {
    $apiUrl .= '?' . http_build_query($queryParams);
}

// 使用 curl 请求 API
$curl = curl_init($apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置最大请求时间为 10 秒
$data = curl_exec($curl);
if (curl_errno($curl)) {
    die("Erreur : Impossible de se connecter au serveur FastAPI. " . curl_error($curl));
}
curl_close($curl);

$consommationData = json_decode($data, true);
if (!$consommationData) {
    die("Erreur : Données reçues non valides.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Consommation - <?php echo htmlspecialchars($type); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2 style="text-align: center;">Consommation - <?php echo htmlspecialchars($selectedLogement ?: 'Tout logement'); ?></h2>
    <form method="GET" action="" style="width: 80%; margin: auto; text-align: center; margin-bottom: 20px;">
        <label for="logement">Sélectionnez un logement:</label>
        <select id="logement" name="logement">
            <option value="">-- Tout logement --</option>
            <?php foreach ($logements as $logement): ?>
                <option value="<?php echo htmlspecialchars($logement['NOM']); ?>" <?php echo ($selectedLogement === $logement['NOM']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($logement['NOM']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="start_date">date de début:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
        <label for="end_date">Date de fin:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
        <button type="submit">filtre</button>
    </form>

    <div style="width: 80%; margin: auto;">
        <canvas id="consumptionChart"></canvas>
    </div>

    <script>
        const rawData = <?php echo json_encode($consommationData); ?>;
        const labels = Object.keys(rawData);
        const values = Object.values(rawData);

        new Chart(document.getElementById("consumptionChart"), {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Consommation",
                    data: values,
                    backgroundColor: "rgba(75, 192, 192, 0.6)"
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: "Type de Consommation"
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: "Valeur Totale"
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

