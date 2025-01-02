<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard - Écologement</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <style>
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
            background-image: url('background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.5;
        }

        .dashboard-container {
            margin: 20px;
        }

        .nav-container {
            margin-bottom: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 50vh;
            width: 80%;
        }

        .thumbnail {
            text-align: center;
        }

        .thumbnail img {
            height: 150px;
            width: auto;
            margin: auto;
        }

        .row-centered {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 120px); /* Adjust height to center thumbnails with padding for navbar */
        }

        .col-centered {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .navbar-right {
            margin-right: 20px;
        }
    </style>
</head>
<body>
    <div class="slide">
        <div class="wallpaper"></div>
    </div>

    <div class="dashboard-container">
        <!-- 导航栏 -->
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="dashboard.php">Dashboard</a>
                </div>
                <ul class="nav navbar-nav navbar-right">
                    <li><a id="userEmail" href="#"></a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <div class="row row-centered">
            <h2 class="page-title" id="pageTitle">Dashboard - Écologement</h2>
        </div>

        <div class="row row-centered">
            <div class="col-sm-6 col-md-3 col-centered">
                <div class="thumbnail">
                    <img src="background.png" alt="Consommation">
                    <div class="caption">
                        <h3>Consommation</h3>
                        <p>在此可以查看消耗情况</p>
                        <p>
                            <a href="Consommation.php?type=électricité" class="btn btn-primary" role="button">
                                électricité
                            </a>
                            <a href="Consommation.php?type=eau" class="btn btn-primary" role="button">
                                eau
                            </a>
                            <a href="Consommation.php?type=déchets" class="btn btn-primary" role="button">
                                déchets
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 col-centered">
                <div class="thumbnail">
                    <img src="background.png" alt="Capteurs/Actionneurs">
                    <div class="caption">
                        <h3>Capteurs/Actionneurs</h3>
                        <p>在此可以查看传感器情况</p>
                        <p>
                            <a href="CA.php" class="btn btn-primary" role="button">
                                Capteurs/Actionneurs
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 col-centered">
                <div class="thumbnail">
                    <img src="background.png" alt="Configuration">
                    <div class="caption">
                        <h3>Configuration</h3>
                        <p>在此可以添加数据</p>
                        <p>
                            <a href="Configuration.php" class="btn btn-primary" role="button">
                                Configuration
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="mainContent">
            <!-- Contenu principal mis à jour dynamiquement -->
        </div>
    </div>

    <script>
        // 从 localStorage 中获取 JWT
        const jwt = localStorage.getItem('jwt');

        // 检查 JWT 是否存在
        if (!jwt) {
            // 如果 JWT 不存在，跳转到登录页面
            alert("请先登录！");
            window.location.href = "login.php";
        } else {
            // 解析 JWT 获取用户邮箱
            const payload = JSON.parse(atob(jwt.split('.')[1])); // 解码 JWT 的 payload
            const userEmail = payload.sub; // 获取用户邮箱

            // 在导航栏中显示用户邮箱
            document.getElementById('userEmail').textContent = userEmail;
        }

        // 监听退出按钮点击事件
        document.querySelector('a[href="logout.php"]').addEventListener('click', (e) => {
            e.preventDefault(); // 阻止默认行为
            localStorage.removeItem('jwt'); // 清除 JWT
            window.location.href = "login.php"; // 跳转到登录页面
        });
    </script>
</body>
</html>