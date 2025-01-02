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
            background-image: url('photos/background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.5;
        }

        /* 导航栏部分 */
        .dashboard-container {
            margin: 20px;
        }
        .nav-container {
            margin-bottom: 20px;
        }
        .navbar-right {
            margin-right: 20px;
        }


        /* 标题独立全屏显示 */
        .page-title-section {
            height: 100vh; /* 占据整个视窗高度 */
            display: flex;
            justify-content: center;
            align-items: center;
            scroll-snap-align: start; /* 滚动到页面顶部时对齐 */
        }
        .page-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold; /* 加粗 */
            font-size: 4em;    /* 调整字号 */
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 50vh;
            width: 80%;
        }

        /* 下方框图的样式 */
        /* 框图部分全屏显示，居中内容 */
        .row-centered-section {
            height: 100vh; /* 占据整个视窗高度 */
            display: flex;
            justify-content: center;
            align-items: center;
            scroll-snap-align: center; /* 滚动到该区域时居中 */
            background-color: #f9f9f9; /* 背景颜色 */
            padding: 20px; /* 添加内边距以避免内容贴边 */
        }

        /* 单个内容框样式 */
        .thumbnail {
            width: 300px; /* 设置固定宽度 */
            height: auto; /* 高度根据内容自动调整 */
            padding: 20px; /* 添加内边距 */
            text-align: center;
            background-color: #fff; /* 设置背景颜色 */
            border-radius: 10px; /* 圆角 */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* 添加阴影 */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* 鼠标悬停时的过渡效果 */
        }

        .thumbnail:hover {
            transform: scale(1.05); /* 鼠标悬停时放大 */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); /* 增强阴影效果 */
        }

        /* 图片样式 */
        .thumbnail img {
            height: 200px; /* 固定图片高度 */
            width: auto; /* 宽度自适应 */
            border-radius: 5px; /* 图片边框圆角 */
            margin-bottom: 10px; /* 图片与标题之间的间距 */
        }

        /* 行样式 */
        .row-centered {
            display: flex;
            justify-content: space-around; /* 在每列之间均匀分布 */
            align-items: center;
            flex-wrap: wrap; /* 在小屏幕上自动换行 */
            gap: 50px; /* 列之间的间距 */
        }

        /* 列样式 */
        .col-centered {
            display: flex;
            flex-direction: column; /* 纵向排列 */
            align-items: center;
            justify-content: center;
        }

        /* 按钮样式 */
        .btn-primary {
            background-color: #007bff; /* 主按钮颜色 */
            color: #fff; /* 按钮文字颜色 */
            padding: 10px 20px; /* 按钮内边距 */
            text-decoration: none; /* 移除下划线 */
            border-radius: 5px; /* 按钮圆角 */
            transition: background-color 0.3s ease, transform 0.3s ease; /* 鼠标悬停效果 */
        }

        .btn-primary:hover {
            background-color: #0056b3; /* 鼠标悬停时颜色 */
            transform: translateY(-3px); /* 鼠标悬停时微微上移 */
        }

        /* 响应式支持 */
        @media (max-width: 768px) {
            .thumbnail {
                width: 250px; /* 小屏幕上缩小显示框宽度 */
            }

            .thumbnail img {
                height: 150px; /* 小屏幕上缩小图片高度 */
            }

            .row-centered {
                gap: 30px; /* 小屏幕上减小间距 */
            }
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

        <!-- 标题部分 -->
        <div class="page-title-section">
            <h2 class="page-title" id="pageTitle">Dashboard - Écologement</h2>
        </div>

        <!-- 框图内容部分 -->
        <div class="row row-centered">
            <!-- Consommation -->
            <div class="col-sm-6 col-md-3 col-centered">
                <div class="thumbnail">
                    <img src="photos/Consommation.jpg" alt="Consommation">
                    <div class="caption">
                        <h3>Consommation</h3>
                        <p>Vous pouvez consulter la consommation ici</p>
                        <p>
                            <a href="Consommation.php" class="btn btn-primary" role="button">
                                Consommation
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Capteurs/Actionneurs -->
            <div class="col-sm-6 col-md-3 col-centered">
                <div class="thumbnail">
                    <img src="photos/CA.jpg" alt="Capteurs/Actionneurs">
                    <div class="caption">
                        <h3>Capteurs/Actionneurs</h3>
                        <p>Vous pouvez voir l'état du capteur/actionneurs ici</p>
                        <p>
                            <a href="CA.php" class="btn btn-primary" role="button">
                                Capteurs/Actionneurs
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Configuration -->
            <div class="col-sm-6 col-md-3 col-centered">
                <div class="thumbnail">
                    <img src="photos/Configuration.jpeg" alt="Configuration">
                    <div class="caption">
                        <h3>Configuration</h3>
                        <p>Vous pouvez ajouter des données ici</p>
                        <p>
                            <a href="Configuration.php" class="btn btn-primary" role="button">
                                Configuration
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Météo -->
            <div class="col-sm-6 col-md-3 col-centered">
                <div class="thumbnail">
                    <img src="photos/météo.jpeg" alt="Météo">
                    <div class="caption">
                        <h3>Météo</h3>
                        <p>Vous pouvez consulter la météo ici</p>
                        <p>
                            <a href="meteo.php" class="btn btn-primary" role="button">
                                Météo
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
            alert("Veuillez vous connecter et réessayer !");
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