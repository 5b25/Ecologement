<?php
// 显示所有错误信息
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 天气代码对应的法语描述
$weather_codes = [
    0 => "Ciel clair",
    1 => "Principalement clair",
    2 => "Partiellement nuageux",
    3 => "Couvert",
    45 => "Brouillard",
    48 => "Brouillard givrant",
    51 => "Bruine légère",
    53 => "Bruine modérée",
    55 => "Bruine dense",
    56 => "Bruine verglaçante légère",
    57 => "Bruine verglaçante dense",
    61 => "Pluie légère",
    63 => "Pluie modérée",
    65 => "Pluie intense",
    66 => "Pluie verglaçante légère",
    67 => "Pluie verglaçante intense",
    71 => "Chute de neige légère",
    73 => "Chute de neige modérée",
    75 => "Chute de neige intense",
    77 => "Grains de neige",
    80 => "Averses de pluie légère",
    81 => "Averses de pluie modérées",
    82 => "Averses de pluie violentes",
    85 => "Averses de neige légère",
    86 => "Averses de neige intense",
    95 => "Orage léger ou modéré",
    96 => "Orage avec grêle légère",
    99 => "Orage avec grêle intense"
];

// 调用 /meteo/ API 端点
function fetch_weather_data() {
    $url = "http://localhost:8000/meteo/";
    $response = file_get_contents($url);
    if ($response === false) {
        die("Erreur : Impossible de se connecter au serveur météo.");
    }
    return json_decode($response, true);
}

// 获取天气数据
$weather_data = fetch_weather_data();

// 解析数据
$current_temperature = $weather_data["current_temperature"][0]["temperature_2m"] ?? "N/A";
$daily_weather = $weather_data["daily_data"] ?? [];
$hourly_weather = $weather_data["hourly_data"] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Météo</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* 页面设置滚动对齐 */
        body {
            scroll-snap-type: y mandatory; /* 强制滚动到对齐位置 */
        }

        /* 背景图片部分 */
        .slide {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
        }
        .wallpaper {
            width: 100%;
            height: 100%;
            background-image: url('photos/meteobackground.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.5;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            padding: 20px;
        }

        .section {
            width: 40%;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .section h2 {
            margin-top: 0;
        }

        .weather-card {
            margin: 10px 0;
            padding: 15px;
            background-color: #f0f8ff;
            border-radius: 8px;
            text-align: left;
        }

        .weather-card h3 {
            margin: 0;
        }

        .weather-card p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="slide">
        <div class="wallpaper"></div>
    </div>

    <h1 style="text-align: center;">Météo</h1>
    <div class="container">
        <!-- 当前温度 -->
        <div class="section">
            <h2>Température Actuelle</h2>
            <p style="font-size: 24px;">
                <?php echo htmlspecialchars($current_temperature) . " °C"; ?>
            </p>
        </div>

        <!-- 未来天气 -->
        <div class="section">
            <h2>Météo Quotidienne</h2>
            <?php foreach ($daily_weather as $day): ?>
                <div class="weather-card">
                    <h3>Date: <?php echo htmlspecialchars($day["date"]); ?></h3>
                    <p>Code météo: <?php echo htmlspecialchars($day["weather_code"]); ?></p>
                    <p>Description: 
                        <?php echo htmlspecialchars($weather_codes[$day["weather_code"]] ?? "Inconnu"); ?>
                    </p>
                    <p>Température Max: <?php echo htmlspecialchars($day["temperature_2m_max"]) . " °C"; ?></p>
                    <p>Température Min: <?php echo htmlspecialchars($day["temperature_2m_min"]) . " °C"; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
