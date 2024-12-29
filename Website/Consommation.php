<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$type = $_GET['type'] ?? 'all';
$startDate = $_GET['start_date'] ?? null; // 开始日期
$endDate = $_GET['end_date'] ?? null;     // 结束日期

// 构造 API URL
$apiUrl = "http://localhost:8000/getfacture/";
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

if (!empty($queryParams)) {
    $apiUrl .= '?' . http_build_query($queryParams);
}

$data = @file_get_contents($apiUrl);

if ($data === FALSE) {
    die("Erreur : Impossible de se connecter au serveur FastAPI.");
}

$consommationData = json_decode($data, true);
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
    <h2 style="text-align: center;">Consommation: <?php echo htmlspecialchars($type); ?></h2>

    <!-- 时间选择表单 -->
    <form method="GET" action="" style="width: 80%; margin: auto; text-align: center; margin-bottom: 20px;">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
        <label for="start_date">开始日期:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
        <label for="end_date">结束日期:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
        <button type="submit">筛选</button>
    </form>

    <div style="width: 80%; margin: auto;">
        <canvas id="consumptionChart"></canvas>
    </div>

    <script>
        const data = <?php echo json_encode($consommationData); ?>;
        const labels = Object.keys(data);
        const values = Object.values(data);

        new Chart(document.getElementById("consumptionChart"), {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Consommation",
                    data: values,
                    backgroundColor: "rgba(75, 192, 192, 0.6)"
                }]
            }
        });
    </script>
</body>
</html>
