<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login Page</title>
    <meta charset="utf-8"> <!-- 设置网页的字符编码为 UTF-8，支持多语言 -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- 设置视口宽度，保证页面在不同设备上的兼容性 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> <!-- 引入 Bootstrap 样式表 -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> <!-- 引入 jQuery 库 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script> <!-- 引入 Web Crypto API -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsencrypt/3.0.0-beta.1/jsencrypt.min.js"></script>


    <style>
        /* 定义全屏背景幻灯片样式 */
        .slide {
            position: fixed; /* 设置背景固定 */
            top: 0;
            left: 0;
            width: 100%; /* 占满全屏宽度 */
            height: 100vh; /* 占满全屏高度 */
            z-index: -1; /* 设置背景层级低于内容层 */
        }
        /* 定义背景图片样式 */
        .wallpaper {
            width: 100%;
            height: 100%;
            background-image: url('photos/background.png'); /* 背景图片路径 */
            background-size: cover; /* 背景图覆盖整个容器 */
            background-position: center; /* 背景居中显示 */
            background-repeat: no-repeat; /* 不重复背景图 */
            opacity: 0.5; /* 背景透明度 */
        }
        /* 定义中心容器样式 */
        .center-container {
            position: absolute; /* 定位到页面中心 */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* 将容器中心对齐页面中心 */
            background-color: #f8f9fa; /* 背景颜色 */
            padding: 20px; /* 内边距 */
            border: 1px solid #ccc; /* 边框 */
            max-width: 300px; /* 最大宽度 */
            overflow: auto; /* 内容溢出时自动滚动 */
            width: 90%; /* 设置宽度为视口宽度的15% */
            box-sizing: border-box; /* 包括边框和内边距在内 */
        }
        /* 输入框样式 */
        .form-control {
            padding: 5px 10px; /* 输入框内边距 */
            font-size: 14px; /* 字体大小 */
            height: auto; /* 自动高度 */
            width: 100%; /* 输入框占父容器100%宽度 */
            box-sizing: border-box; /* 确保内边距和边框不影响宽度 */
        }
        /* 按钮样式 */
        .btn-primary {
            background-color: #5cb85c; /* 按钮背景颜色 */
            border-color: #4cae4c; /* 按钮边框颜色 */
            margin-top: 10px; /* 顶部外边距 */
            width: 100%; /* 按钮宽度占满父容器 */
        }
        /* 按钮悬停样式 */
        .btn-primary:hover {
            background-color: #4cae4c; /* 悬停时背景颜色 */
            border-color: #398439; /* 悬停时边框颜色 */
        }
        /* 模态框默认隐藏 */
        .modal {
            display: none;
        }
        /* 显示模态框样式 */
        .modal.show {
            display: block;
        }
        /* 消息文本样式 */
        .message {
            color: red; /* 字体颜色为红色 */
            font-size: 12px; /* 字体大小 */
        }
        /* 居中标题样式 */
        .text-center {
            margin-bottom: 30px; /* 增加底部外边距 */
            font-weight: bold; /* 加粗文字 */
        }
        /* 链接文字容器样式 */
        .links-container {
            text-align: center; /* 居中对齐文字 */
            margin-top: 10px; /* 添加顶部外边距 */
        }
        /* 链接文字样式 */
        .links-container a {
            color: #007bff; /* 链接颜色为蓝色 */
            text-decoration: none; /* 去掉下划线 */
        }
        /* 鼠标悬停时链接样式 */
        .links-container a:hover {
            text-decoration: underline; /* 悬停时显示下划线 */
        }

        /* 密码要求变色逻辑 */
        .password-rules {
            list-style: none;
            padding: 0;
            margin: 10px 0 0;
        }
        .password-rules li {
            font-size: 12px;
            color: red; /* 默认颜色为红色（不符合规则） */
        }
        .password-rules li.valid {
            color: green; /* 符合规则时变为绿色 */
        }

    </style>
</head>

<body>
    <!-- 背景图片 -->
    <div class="slide">
        <div class="wallpaper"></div>
    </div>

    <!-- 登录表单 -->
    <div class="center-container">
        <h2 class="text-center">Bienvenue !</h2> <!-- 登录标题 -->
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Adresse e-mail</label> <!-- 邮箱输入框标签 -->
                <input type="email" class="form-control" id="email" placeholder="Saisissez votre adresse e-mail" required> <!-- 邮箱输入框 -->
            </div>
            <div class="form-group">
                <label for="pwd">Mot de passe</label> <!-- 密码输入框标签 -->
                <input type="password" class="form-control" id="pwd" placeholder="Entrez votre mot de passe" required> <!-- 密码输入框 -->
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button> <!-- 登录按钮 -->
        </form>
        <div class="links-container">
            <a href="#" id="registerLink">Créer un compte</a> <!-- 注册链接 -->
        </div>
        <div class="links-container">
            <a href="#" id="resetLink">Mot de passe oublié ?</a> <!-- 重置密码链接 -->
        </div>
    </div>

    <!-- 注册模态框 -->
    <div id="registerModal" class="modal">
        <div class="center-container">
            <h4>Inscription</h4> <!-- 注册标题 -->
            <div class="form-group">
                <label for="registerEmail">Email</label> <!-- 注册邮箱输入框标签 -->
                <input type="email" class="form-control" id="registerEmail" required> <!-- 注册邮箱输入框 -->
            </div>
            <div class="form-group">
                <button type="button" id="sendRegisterCode" class="btn btn-primary">Envoyer le code</button> <!-- 发送验证码按钮 -->
            </div>
            <div class="form-group">
                <label for="registerCode">Code de vérification</label> <!-- 验证码输入框标签 -->
                <input type="text" class="form-control" id="registerCode" required> <!-- 验证码输入框 -->
            </div>
            <div class="form-group">
                <label for="registerPwd">Mot de passe</label> <!-- 注册密码输入框标签 -->
                <input type="password" class="form-control" id="registerPwd" required> <!-- 注册密码输入框 -->
                <ul id="registerPasswordRules" class="password-rules">
                    <li id="lengthRule">Au moins 8 caractères</li>
                    <li id="uppercaseRule">Au moins une majuscule</li>
                    <li id="lowercaseRule">Au moins une minuscule</li>
                    <li id="numberRule">Au moins un chiffre</li>
                    <li id="specialCharRule">Au moins un caractère spécial</li>
                </ul>
            </div>
            <button type="button" id="registerSubmit" class="btn btn-primary">S'inscrire</button> <!-- 注册提交按钮 -->
        </div>
    </div>

    <!-- 重置密码模态框 -->
    <div id="resetModal" class="modal">
        <div class="center-container">
            <h4>Réinitialiser le mot de passe</h4> <!-- 重置密码标题 -->
            <div class="form-group">
                <label for="resetEmail">Email</label> <!-- 重置邮箱输入框标签 -->
                <input type="email" class="form-control" id="resetEmail" required> <!-- 重置邮箱输入框 -->
            </div>
            <div class="form-group">
                <button type="button" id="sendResetCode" class="btn btn-primary">Envoyer le code</button> <!-- 发送验证码按钮 -->
            </div>
            <div class="form-group">
                <label for="resetCode">Code de vérification</label> <!-- 重置验证码输入框标签 -->
                <input type="text" class="form-control" id="resetCode" required> <!-- 重置验证码输入框 -->
            </div>
            <div class="form-group">
                <label for="resetPwd">Nouveau mot de passe</label> <!-- 重置密码输入框标签 -->
                <input type="password" class="form-control" id="resetPwd" required> <!-- 重置密码输入框 -->
                <ul id="resetPasswordRules" class="password-rules">
                    <li id="resetLengthRule">Au moins 8 caractères</li>
                    <li id="resetUppercaseRule">Au moins une majuscule</li>
                    <li id="resetLowercaseRule">Au moins une minuscule</li>
                    <li id="resetNumberRule">Au moins un chiffre</li>
                    <li id="resetSpecialCharRule">Au moins un caractère spécial</li>
                </ul>
            </div>
            <button type="button" id="resetSubmit" class="btn btn-primary">Réinitialiser</button> <!-- 重置密码提交按钮 -->
        </div>
    </div>

    <!-- 脚本逻辑部分 -->
    <script>
        // 后端实际生成的公钥指纹
        const trustedFingerprint = "Hfmw124BTK09zVDm+CkYwJcBAUHgQWCvxqg8Ixr3fDI=";

        // 硬编码的可信公钥指纹，用于验证后端返回的公钥是否可信
        let publicKey = null; // 保存后端返回的公钥

        // 从后端获取公钥并验证其可信性
        async function fetchPublicKey() {
            try {
                const jwt = localStorage.getItem('jwt'); // 从 localStorage 获取 JWT
                const apiKey = "SDkIjUweR5642ClqeJKLsPlD23jws2kg1jfKSJDK="; // API Key
            
                // 使用模板字符串拼接 URL
                const response = await fetch(`action_page.php?action=getPublicKey&apiKey=${apiKey}`, {
                    headers: jwt ? { Authorization: `Bearer ${jwt}` } : {}, // 如果 JWT 存在，添加到请求头
                    cache: "no-store" // 不使用缓存
                });
            
                if (response.status === 401) { // 如果返回 401 Unauthorized
                    throw new Error("Unauthorized. Please check your API Key or JWT.");
                }
            
                const data = await response.json(); // 解析JSON响应
            
                console.log("Received response from server:", data); // 输出JSON响应
            
                if (data.success) { // 如果成功获取
                    // 计算并验证指纹
                    const receivedFingerprint = await calculateFingerprintWithCryptoJS(data.publicKey);
                    console.log("Calculated fingerprint:", receivedFingerprint); // 输出公钥指纹
                
                    if (receivedFingerprint === trustedFingerprint) { // 比较公钥指纹是否匹配
                        publicKey = data.publicKey; // 如果可信，保存公钥
                        console.log("Public key verified successfully.");
                    } else {
                        console.error("Fingerprint does not match trusted fingerprint.");
                        console.log("Expected fingerprint:", trustedFingerprint);
                        console.log("Received fingerprint:", receivedFingerprint);
                        throw new Error("Public key verification failed.");
                    }
                } else {
                    console.error("Failed to fetch public key:", data);
                    throw new Error("Failed to fetch public key."); // 如果获取失败，抛出错误
                }
            } catch (err) {
                console.error("Error fetching public key:", err);
                alert("Erreur lors du chargement de la clé publique."); // 显示错误提示
            }
        }


        // 计算公钥的SHA-256指纹
        /*
        async function calculateFingerprint(publicKey) {
            const encoder = new TextEncoder(); // 创建文本编码器
            const publicKeyBytes = encoder.encode(publicKey); // 将公钥转换为字节数组
            const hashBuffer = await crypto.subtle.digest("SHA-256", publicKeyBytes); // 计算哈希值
            return btoa(String.fromCharCode(...new Uint8Array(hashBuffer))); // 返回Base64编码的指纹
        }
        */
        async function calculateFingerprintWithCryptoJS(publicKey) {
            if (!publicKey) {
                console.error("Public key is undefined or empty.");
                throw new Error("Invalid public key for fingerprint calculation.");
            }
            try {
                // 使用 CryptoJS 计算 SHA-256 指纹
                const hash = CryptoJS.SHA256(publicKey);
                return CryptoJS.enc.Base64.stringify(hash); // 转为 Base64
            } catch (err) {
                console.error("Error calculating fingerprint:", err);
                throw err;
            }
        }

        // 使用公钥对数据进行加密
        function encryptWithPublicKey(data) {
            if (!publicKey) { // 如果公钥未加载
                alert("La clé publique n'est pas chargée."); // 显示错误提示
                return null;
            }
            const encrypt = new JSEncrypt(); // 创建加密实例
            encrypt.setPublicKey(publicKey); // 设置公钥
            return encrypt.encrypt(data); // 返回加密后的数据
        }

        // 打开指定模态框
        function openModal(modalId) {
            document.getElementById(modalId).classList.add("show"); // 为指定模态框添加显示类
        }

        // 关闭指定模态框
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove("show"); // 从指定模态框移除显示类
        }

        // 监听注册链接点击事件
        document.getElementById("registerLink").addEventListener("click", (e) => {
            e.preventDefault(); // 阻止默认行为
            openModal("registerModal"); // 打开注册模态框
        });

        // 监听重置密码链接点击事件
        document.getElementById("resetLink").addEventListener("click", (e) => {
            e.preventDefault(); // 阻止默认行为
            openModal("resetModal"); // 打开重置密码模态框
        });

        // 定义验证规则的正则表达式
        const passwordRules = {
            length: /.{8,}/,
            uppercase: /[A-Z]/,
            lowercase: /[a-z]/,
            number: /\d/,
            specialChar: /[\W_]/ // 非字母数字字符（特殊字符）
        };
        
        // 实时检测密码并更新提示文字颜色
        function validatePasswordInput(password, rulesContainerId) {
            const rulesContainer = document.getElementById(rulesContainerId);

            // 定义规则对象
            let rules;
            if (rulesContainerId == "registerPasswordRules"){
                rules = {
                    lengthRule: passwordRules.length.test(password),
                    uppercaseRule: passwordRules.uppercase.test(password),
                    lowercaseRule: passwordRules.lowercase.test(password),
                    numberRule: passwordRules.number.test(password),
                    specialCharRule: passwordRules.specialChar.test(password)
                };
            } else if (rulesContainerId === "resetPasswordRules") {
                rules = {
                    resetLengthRule: passwordRules.length.test(password),
                    resetUppercaseRule: passwordRules.uppercase.test(password),
                    resetLowercaseRule: passwordRules.lowercase.test(password),
                    resetNumberRule: passwordRules.number.test(password),
                    resetSpecialCharRule: passwordRules.specialChar.test(password)
                };
            } else {
                console.error(`Unknown rulesContainerId: "${rulesContainerId}"`);
                return;
            }
        
            // 遍历规则并更新对应提示的颜色
            for (const [ruleId, isValid] of Object.entries(rules)) {
                const ruleElement = document.getElementById(ruleId);
                if (isValid) {
                    ruleElement.classList.add("valid"); // 添加绿色样式
                } else {
                    ruleElement.classList.remove("valid"); // 移除绿色样式
                }
            }
        }

        // 监听注册密码输入框
        document.getElementById("registerPwd").addEventListener("input", (e) => {
            validatePasswordInput(e.target.value, "registerPasswordRules");
        });

        // 监听重置密码输入框
        document.getElementById("resetPwd").addEventListener("input", (e) => {
            validatePasswordInput(e.target.value, "resetPasswordRules");
        });

        // 验证密码强度
        function validatePassword(password) {
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            return passwordRegex.test(password);
        }

        
        // 登陆逻辑
        document.getElementById("loginForm").addEventListener("submit", async function (e) {
            e.preventDefault(); // 阻止表单默认提交行为

            const email = document.getElementById("email").value; // 获取登录邮箱
            const password = document.getElementById("pwd").value; // 获取登录密码

            // 如果公钥还未加载，先获取公钥
            if (!publicKey) { // 如果公钥未加载
                await fetchPublicKey(); // 加载并验证公钥
                if (!publicKey) { // 如果加载失败
                    alert("Erreur lors du chargement de la clé publique. Impossible de continuer."); // 显示错误提示
                    return;
                }
            }

            // 加密密码
            const encryptedPassword = encryptWithPublicKey(password); // 加密登录密码
            if (!encryptedPassword) { // 如果加密失败
                alert("Erreur de chiffrement du mot de passe !"); // 显示错误提示
                return;
            }

            fetch("action_page.php", { // 调用后端接口
                method: "POST", // POST方法
                headers: { "Content-Type": "application/json" }, // 设置请求头
                body: JSON.stringify({ action: "login", email, password: encryptedPassword }), // 请求体包含登录信息
            })
                .then((response) => response.json()) // 解析JSON响应
                .then((data) => {
                    console.log("Server Response:", data); // 在控制台中输出后端响应
                    if (data.success) { // 如果登录成功
                        // 1. 将后端返回的 token 保存到 localStorage
                        localStorage.setItem('jwt', data.token);
                        // 2. 显示成功提示
                        alert(data.message);
                        // 3. 跳转到后端指定页面
                        window.location.href = data.redirect || "dashboard.php";
                    } else {
                        // 错误处理
                        if (data.error === "user_not_found") { // 如果用户未找到
                            alert("L'utilisateur n'est pas enregistré ou l'adresse e-mail est incorrecte."); // 显示错误提示
                        } else if (data.error === "incorrect_password") { // 如果密码错误
                            alert("Le mot de passe est incorrect."); // 显示错误提示
                        } else {
                            alert(data.message || "Une erreur s'est produite."); // 显示其他错误
                        }
                    }
                })
                .catch((err) => {
                    console.error("Fetch Error:", err); // 控制台记录错误
                    alert("Une erreur s'est produite."); // 显示错误提示
                });
        });

        // 发送注册验证码
        document.getElementById("sendRegisterCode").addEventListener("click", () => {
            const email = document.getElementById("registerEmail").value; // 获取注册邮箱
            if (!email) { // 如果邮箱为空
                alert("Veuillez fournir une adresse e-mail !"); // 显示错误提示
                return;
            }
        
            fetch("action_page.php", { // 调用后端接口
                method: "POST", // POST方法
                headers: { "Content-Type": "application/json" }, // 设置请求头
                body: JSON.stringify({ action: "sendVerificationCode", email }), // 请求体包含邮箱信息
            })
                .then((response) => response.json()) // 解析JSON响应
                .then((data) => {
                    alert(data.message); // 显示后端返回的消息
                });
        });

        // 注册逻辑
        document.getElementById("registerSubmit").addEventListener("click", async () => {
            const email = document.getElementById("registerEmail").value; // 获取注册邮箱
            const password = document.getElementById("registerPwd").value; // 获取注册密码
            const code = document.getElementById("registerCode").value; // 获取验证码

            // 验证密码强度
            if (!validatePassword(password)) {
                alert("Le mot de passe doit contenir au moins 8 caractères, avec des majuscules, des minuscules, un chiffre et un caractère spécial.");
                return;
            }

            if (!publicKey) { // 如果公钥未加载
                await fetchPublicKey(); // 加载并验证公钥
                if (!publicKey) { // 如果加载失败
                    alert("Erreur lors du chargement de la clé publique. Impossible de continuer."); // 显示错误提示
                    return;
                }
            }

            // 加密密码并提交
            const encryptedPassword = encryptWithPublicKey(password); // 加密密码
            if (!encryptedPassword) { // 如果加密失败
                alert("Erreur de chiffrement du mot de passe !"); // 显示错误提示
                return;
            }

            fetch("action_page.php", { // 注册时应调用普通的 fetch
                method: "POST", // POST方法
                headers: { "Content-Type": "application/json" }, // 设置请求头
                body: JSON.stringify({
                    action: "register", // 操作为 "register"
                    email: email,       // 用户输入的邮箱
                    password: encryptedPassword, // 加密后的密码
                    code: code // 用户输入的验证码
                })
            })
                .then((response) => response.json()) // 解析JSON响应
                .then((data) => {
                    console.log("Server Response:", data); // 打印服务器返回结果
                    if (data.success) {
                        alert(data.message); // 提示用户注册成功
                        window.location.href = "login.php"; // 跳转到登录页面
                    } else {
                        alert(data.message); // 显示后端返回的错误消息
                    }
                })
                .catch((err) => {
                    console.error("Fetch Error:", err);
                });
        });

        // 发送重置密码验证码
        document.getElementById("sendResetCode").addEventListener("click", () => {
            const email = document.getElementById("resetEmail").value; // 获取重置密码邮箱
            if (!email) { // 如果邮箱为空
                alert("Veuillez fournir une adresse e-mail !"); // 显示错误提示
                return;
            }
        
            fetch("action_page.php", { // 调用后端接口
                method: "POST", // POST方法
                headers: { "Content-Type": "application/json" }, // 设置请求头
                body: JSON.stringify({
                    action: "sendResetCode", // 新的操作类型
                    email: email // 用户输入的邮箱
                }),
            })
                .then((response) => response.json()) // 解析JSON响应
                .then((data) => {
                    if (data.success) {
                        alert(data.message); // 显示后端返回的成功消息
                    } else {
                        alert(data.message || "Une erreur s'est produite."); // 显示错误提示
                    }
                })
                .catch((err) => {
                    console.error("Fetch Error:", err);
                    alert("Une erreur s'est produite.");
                });
        });


        // 重置密码逻辑
        document.getElementById("resetSubmit").addEventListener("click", async () => {
            const email = document.getElementById("resetEmail").value; // 获取重置密码邮箱
            const password = document.getElementById("resetPwd").value; // 获取新密码
            const code = document.getElementById("resetCode").value; // 获取验证码

            // 验证密码强度
            if (!validatePassword(password)) {
                alert("Le mot de passe doit contenir au moins 8 caractères, avec des majuscules, des minuscules, un chiffre et un caractère spécial.");
                return;
            }

            // 确保已获取公钥（若尚未加载，进行拉取）
            if (!publicKey) { // 如果公钥未加载
                await fetchPublicKey(); // 加载并验证公钥
                if (!publicKey) { // 如果加载失败
                    alert("Erreur lors du chargement de la clé publique. Impossible de continuer."); // 显示错误提示
                    return;
                }
            }
            
            // 加密密码并提交
            const encryptedPassword = encryptWithPublicKey(password); // 加密新密码
            if (!encryptedPassword) { // 如果加密失败
                alert("Erreur de chiffrement du mot de passe !"); // 显示错误提示
                return;
            }

            fetch("action_page.php", { // 调用后端接口
                // POST方法
                method: "POST", 
                // 设置请求头
                headers: {
                    "Content-Type": "application/json",
                }, 
                body: JSON.stringify({ action: "resetPassword", email, password: encryptedPassword, code }), // 请求体包含重置信息
            })
                .then((response) => response.json()) // 解析JSON响应
                .then((data) => {
                    if (data.success) {
                        alert(data.message); // 显示成功提示
                        window.location.href = "login.php"; // 跳转到登录页面
                    } else {
                        alert(data.message || "Une erreur s'est produite."); // 显示后端返回的错误消息
                    }
                })
                .catch((err) => {
                    console.error("Fetch Error:", err);
                    alert("Une erreur s'est produite.");
                });
        });

        // 检查JWT是否过期
        function isJWTExpired(token) {
            try {
                const payload = JSON.parse(atob(token.split('.')[1])); // 解码 JWT 的 payload
                const expirationTime = payload.exp * 1000; // exp 是秒，需要转为毫秒
                return Date.now() >= expirationTime; // 当前时间是否大于过期时间
            } catch (err) {
                console.error("Error parsing JWT:", err);
                return true; // 如果解析失败，视为过期
            }
        }

        // 在请求中包含JWT
        function fetchWithAuth(url, options = {}) {
            const jwt = localStorage.getItem('jwt'); // 从 localStorage 获取 JWT

            // 检查 JWT 是否存在或是否过期
            if (!jwt || isJWTExpired(jwt)) {
                alert("Votre session a expiré. Veuillez vous reconnecter."); // 提示用户重新登录
                localStorage.removeItem('jwt'); // 清除过期的 JWT
                window.location.href = "login.php"; // 跳转到登录页面
                return Promise.reject("JWT expired or not found"); // 返回错误
            }
        
            // 在请求头中添加 Authorization
            options.headers = {
                ...options.headers,
                'Authorization': `Bearer ${jwt}`
            };
        
            // 使用 fetch 发起请求
            return fetch(url, options).then(response => {
                // 如果服务器返回 401 未授权，说明 Token 无效或过期
                if (response.status === 401) {
                    alert("Votre session a expiré. Veuillez vous reconnecter.");
                    localStorage.removeItem('jwt');
                    window.location.href = "login.php";
                    throw new Error("Unauthorized");
                }
                return response;
            });
        }

        // 在页面加载时获取并验证公钥
        window.onload = fetchPublicKey; // 页面加载完成后调用
    </script>
</body>
</html>
