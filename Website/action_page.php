<?php

session_start(); // 启动会话

require_once __DIR__ . '/vendor/autoload.php'; // 引入必要的外部库
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

header('Content-Type: application/json; charset=utf-8'); // 确保返回 JSON 格式，支持 UTF-8 编码

// 加载 .env 文件
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 调试输出 .env 内容
writeDebugLog("Loaded ENV: " . json_encode($_ENV));

// 写错误日志
function writeDebugLog($message) {
    $timestamp = date('[Y-m-d H:i:s] '); // 获取当前时间戳
    $logDir = __DIR__ . '/logs'; // 日志目录

    // 检查并创建日志目录
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0777, true) && !is_dir($logDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $logDir));
        }
    }
    // 日志文件路径
    $logFilePath = $logDir . '/ActionPage_debug.log';

    // 写入日志
    file_put_contents($logFilePath, $timestamp . $message . PHP_EOL, FILE_APPEND);
}

// 清空任务列表函数
function clearTaskQueue() {
    $taskQueueFile = __DIR__ . '/email_task_queue.json';
    file_put_contents($taskQueueFile, json_encode([], JSON_PRETTY_PRINT)); // 将任务列表清空
    writeDebugLog("Task queue has been cleared.");
}

// 捕获所有错误并返回 JSON 格式
ini_set('display_errors', 0); // 禁止直接显示错误
ini_set('log_errors', 1); // 记录错误日志
error_reporting(E_ALL); // 报告所有错误

// 设置自定义错误处理程序
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    http_response_code(500); // 设置 HTTP 状态码为 500
    echo json_encode(["success" => false, "message" => "Server error: $errstr in $errfile on line $errline"]);
    exit();
});

// 捕获未处理的异常
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit();
});

// 配置 JWT 密钥
$jwtPrivateKey = file_get_contents(__DIR__ . '/private.key'); // 私钥
$jwtPublicKey = file_get_contents(__DIR__ . '/public.key'); // 公钥

// 生成 JWT
function generateJWT($email) {
    global $jwtPrivateKey;
    $payload = [
        "iss" => "ecologement.local", // 签发者
        "aud" => "ecologement.local", // 接收者
        "iat" => time(), // 签发时间
        "exp" => time() + 3600, // 过期时间（1小时）
        "sub" => $email, // 用户标识
    ];

    return JWT::encode($payload, $jwtPrivateKey, 'RS256'); // 使用私钥和 RS256 算法生成令牌
}

// 验证 JWT
function verifyJWT($token) {
    global $jwtPublicKey;

    try {
        $decoded = JWT::decode($token, new Key($jwtPublicKey, 'RS256')); // 修正为使用 Key 对象
        return $decoded;
    } catch (Exception $e) {
        writeDebugLog("JWT verification failed: " . $e->getMessage());
        return false;
    }
}

// JWT 认证中间件
function authenticateRequest() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $jwt = $matches[1];
        $decoded = verifyJWT($jwt);
        if ($decoded) {
            return $decoded;
        }
    }

    http_response_code(401); // 未授权
    echo json_encode(["success" => false, "message" => "Unauthorized. Please provide a valid token."]);
    exit();
}

// 开启输出缓冲区
ob_start();

ini_set('display_errors', 1); // 显示错误信息，用于调试
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // 设置错误报告级别

/******************************* 密钥部分 *******************************/
// RSA 密钥文件路径
$privateKeyPath = __DIR__ . '/private.key'; // 私钥文件路径
$publicKeyPath = __DIR__ . '/public.key';  // 公钥文件路径

// 如果密钥文件不存在，则生成新密钥
if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
    writeDebugLog("Key files are missing. Generating new RSA key pair...");

    $keyConfig = [
        "private_key_bits" => 2048, // 设置 RSA 密钥长度
        "private_key_type" => OPENSSL_KEYTYPE_RSA, // 指定密钥类型
        "config" => "D:/xampp/apache/conf/openssl.cnf", // 配置文件路径
        "default_random_file" => "D:/xampp/apache/conf/.rnd", // 熵池路径
    ];

    $privateKeyResource = openssl_pkey_new($keyConfig); // 生成新的私钥
    // openssl_pkey_export($privateKeyResource, $privateKey); // 导出私钥

    // 检查是否生成密钥成功
    if ($privateKeyResource === false) {
        $error = openssl_error_string();
        writeDebugLog("Failed to generate private key: " . $error);
        die("Failed to generate private key: " . $error);
    }

    // 尝试导出私钥
    if (!openssl_pkey_export($privateKeyResource, $privateKey)) {
        $error = openssl_error_string();
        writeDebugLog("Failed to export private key: " . $error);
        die("Failed to export private key: " . $error);
    }

    // 获取公钥详细信息
    $publicKeyDetails = openssl_pkey_get_details($privateKeyResource); // 获取公钥详情
    if ($publicKeyDetails === false) {
        $error = openssl_error_string();
        writeDebugLog("Failed to get public key details: " . $error);
        die("Failed to get public key details: " . openssl_error_string());
    }

    $publicKey = $publicKeyDetails['key']; // 获取公钥

    // 将密钥保存到文件中
    file_put_contents($privateKeyPath, $privateKey);
    file_put_contents($publicKeyPath, $publicKey);

    writeDebugLog("Generated Public Key: " . $publicKey);
    writeDebugLog("Public and private keys have been successfully saved.");

}

// 读取密钥
$privateKey = file_get_contents($privateKeyPath); // 读取私钥
$publicKey = file_get_contents($publicKeyPath); // 读取公钥

// 计算公钥指纹（SHA-256 摘要）
$publicKeyFingerprint = base64_encode(hash('sha256', $publicKey, true)); // 计算指纹
writeDebugLog("Generated Fingerprint: " . $publicKeyFingerprint);

// 提供公钥和指纹的 API
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getPublicKey') {
    // 从 .env 中读取API_KEY
    $apiKey = $_GET['apiKey'] ?? null;  // 获取请求中的 apiKey
    $secureApiKey = $_ENV['API_KEY'];   // 验证 API Key 是否匹配
    
    // 打印调试信息
    writeDebugLog("Received API Key from request: " . $apiKey);
    writeDebugLog("Received API_KEY: " . $secureApiKey );

    // 比较是否匹配
    if ($apiKey !== $secureApiKey) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized."]);
        exit();
    }

    // 如果验证通过，返回公钥和指纹
    writeDebugLog("Public Key API Triggered\n");
    echo json_encode([
        "success" => true,
        "publicKey" => $publicKey, // 返回公钥
        "fingerprint" => $publicKeyFingerprint, // 返回公钥指纹
    ]);
    exit();
}

/******************************* 数据库部分 *******************************/
$data = json_decode(file_get_contents("php://input"), true); // 从请求体中获取 JSON 数据
$action = $data['action'] ?? null; // 获取请求的操作类型

// 数据库配置文件
$servername = $_ENV['SQL_HOST']; // 从 .env 文件中获取数据库主机
$username = $_ENV['SQL_USERNAME']; // 从 .env 文件中获取数据库用户名
$password = $_ENV['SQL_PASSWORD']; // 从 .env 文件中获取数据库密码
$dbname = $_ENV['SQL_DATABASE']; // 从 .env 文件中获取数据库名称

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) { // 检查连接是否出错
    echo json_encode(["success" => false, "message" => "Erreur de connexion à MySQL."]);
    exit();
}

// 检查并创建 verification_codes 表
function ensureVerificationTableExists($conn) {
    $tableCheckQuery = "SHOW TABLES LIKE 'verification_codes'";
    $result = $conn->query($tableCheckQuery);

    if ($result->num_rows === 0) {
        // 表不存在，创建表
        $createTableQuery = "CREATE TABLE verification_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            code VARCHAR(6) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if (!$conn->query($createTableQuery)) {
            die(json_encode(["success" => false, "message" => "Erreur lors de la création de la table: " . $conn->error]));
        }
    }
}

// 调用函数确保表存在
ensureVerificationTableExists($conn);

/******************************* 验证码邮件部分 *******************************/
// 将验证码保存到数据库中
function saveVerificationCode($email, $code) {
    global $conn;

    // 计算验证码的过期时间
    $expiresAt = date('Y-m-d H:i:s', time() + 360); // 6 分钟后过期

    // 删除该用户之前的验证码
    $stmt = $conn->prepare("DELETE FROM verification_codes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // 插入新的验证码
    $stmt = $conn->prepare("INSERT INTO verification_codes (email, code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $code, $expiresAt);

    if (!$stmt->execute()) {
        writeDebugLog("Failed to save verification code for $email: " . $stmt->error);
        return false;
    }

    return true;
}

// 在发送验证码时调用
if ($action === "sendVerificationCode") {
    $email = strtolower(trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL))); // 清理并验证邮箱
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Adresse e-mail invalide."]);
        exit();
    }

    $code = random_int(100000, 999999);

    // 保存验证码到数据库
    if (!saveVerificationCode($email, $code)) {
        echo json_encode(["success" => false, "message" => "Erreur lors de la sauvegarde du code de vérification."]);
        exit();
    }

    // 发送验证码
    addTaskToQueue($email);
    processTaskQueue();

    echo json_encode(["success" => true, "message" => "Le code de vérification a été envoyé à votre e-mail."]);
    exit();
}


// 添加任务到队列
function addTaskToQueue($email) {
    $taskQueueFile = __DIR__ . '/email_task_queue.json';

    $taskQueue = [];
    if (file_exists($taskQueueFile)) {
        $taskQueue = json_decode(file_get_contents($taskQueueFile), true) ?: [];
    }

    foreach ($taskQueue as $task) {
        if ($task['email'] === $email) {
            writeDebugLog("Sending verification code email to $email with code: $code");
            return;
        }
    }

    $newTask = [
        'email' => $email,
        'code' => random_int(100000, 999999),
        'timestamp' => time(),
    ];
    $taskQueue[] = $newTask;
    file_put_contents($taskQueueFile, json_encode($taskQueue, JSON_PRETTY_PRINT));
    writeDebugLog("New task added to queue for email: $email");
}

// 执行任务队列
function processTaskQueue() {
    $taskQueueFile = __DIR__ . '/email_task_queue.json';

    if (!file_exists($taskQueueFile)) {
        writeDebugLog("No task queue found.");
        return;
    }

    $taskQueue = json_decode(file_get_contents($taskQueueFile), true) ?: [];
    $remainingTasks = [];

    foreach ($taskQueue as $task) {
        $email = $task['email'];
        $code = $_SESSION['verification_code']['code'] ?? $task['code']; // 优先使用会话中的验证码
        $success = sendVerificationCodeTask($email, $code);
        
        if ($success) {
            writeDebugLog("Task for {$task['email']} completed successfully.");
        } else {
            writeDebugLog("Task for {$task['email']} failed.");
            $remainingTasks[] = $task;
        }
    }

    // 清空任务列表
    clearTaskQueue();
}

// 发送验证码邮件
function sendVerificationCodeTask($email, $code) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 4;
        $mail->Debugoutput = function($str, $level) {
            writeDebugLog("SMTP Debug Level $level: $str");
        };

        $mail->CharSet = "UTF-8";
        $mail->isSMTP();
        $mail->Host = 'smtp.qq.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'SMTP_USERNAME'; // 替换为您的邮箱
        $mail->Password = 'SMTP_PASSWORD'; // 替换为您的授权码
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('SMTP_USERNAME', 'Ecologement');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Code de vérification';
        $mail->Body = "Bonjour,<br><br>Votre code est : <strong>$code</strong><br>Valable pour 6 minutes.";
        writeDebugLog("Sending email to $email with code: $code");

        $mail->send();
        writeDebugLog("Mail sent successfully to $email.");
        return true;
    } catch (Exception $e) {
        writeDebugLog("Mail sending failed to $email: " . $mail->ErrorInfo);
        return false;
    }
}

/*
// 验证验证码
function verifyCode($email, $code) {

    $email = strtolower(trim($email)); // 标准化邮箱
    if (!isset($_SESSION['verification_code'])) {
        writeDebugLog("Verification failed: No verification code found in session.");
        return false;
    }

    $storedCode = $_SESSION['verification_code'];
    writeDebugLog("Verifying code. Stored: " . json_encode($storedCode) . ", Provided Email: $email, Provided Code: $code");

    if ($storedCode['email'] === $email && $storedCode['code'] == $code && time() < $storedCode['expires_at']) {
        unset($_SESSION['verification_code']); // 验证通过后清除验证码
        return true;
    }
    writeDebugLog("Verification failed. Stored Code: " . json_encode($storedCode) . ", Provided Email: $email, Provided Code: $code");
    return false;
}
*/
// 验证验证码
function verifyCode($email, $code) {
    global $conn;

    // 查询数据库中的验证码
    $stmt = $conn->prepare("SELECT code, expires_at FROM verification_codes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($storedCode, $expiresAt);
    $stmt->fetch();
    $stmt->close();

    // 检查验证码是否匹配并未过期
    if (!$storedCode || $storedCode !== $code || strtotime($expiresAt) < time()) {
        writeDebugLog("Verification failed for $email. Code: $code, Stored: $storedCode, Expires At: $expiresAt");
        return false;
    }

    // 删除已验证的验证码
    $stmt = $conn->prepare("DELETE FROM verification_codes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    return true;
}


// 处理发送验证码的请求（生成验证码）
if ($action === "sendVerificationCode") {
    $email = strtolower(trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL))); // 清理并验证邮箱
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Adresse e-mail invalide."]);
        exit();
    }

    // 检查是否已有有效的验证码
    if (isset($_SESSION['verification_code']) && time() < $_SESSION['verification_code']['expires_at']) {
        writeDebugLog("A valid code already exists for email: $email");
        echo json_encode(["success" => false, "message" => "Un code de vérification est déjà valide."]);
        exit();
    }

    // 生成新的验证码
    $code = random_int(100000, 999999);

    // 存储验证码到会话
    $_SESSION['verification_code'] = [
        'code' => $code,
        'email' => $email,
        'expires_at' => time() + 360 // 验证码有效期 6 分钟
    ];
    writeDebugLog("Stored verification code: " . json_encode($_SESSION['verification_code']));

    // 添加到任务队列并发送验证码
    addTaskToQueue($email);
    processTaskQueue();

    echo json_encode(["success" => true, "message" => "Le code de vérification a été envoyé à votre e-mail."]);
    exit();
}

/******************************* 网页功能部分 *******************************/
// 提取密码并使用私钥解密
function decryptPassword($encryptedPassword) {
    global $privateKey;
    if (!openssl_private_decrypt(base64_decode($encryptedPassword), $decryptedPassword, $privateKey)) {
        writeDebugLog("Password decryption failed.");
        return false;
    }
    return $decryptedPassword;
}

// 登录逻辑
if ($action === "login") {
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL); // 清理邮箱
    $encryptedPassword = $data['password']; // 获取加密后的密码

    // 使用私钥解密密码
    $decryptedPassword = decryptPassword($encryptedPassword);
    if (!$decryptedPassword) {
        echo json_encode(["success" => false, "message" => "无法解密密码！"]);
        exit();
    }

    // 查询数据库验证用户
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ?"); // 查询用户密码
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($passwordHash);
    $stmt->fetch();
    $stmt->close();

    if (!$passwordHash) {
        echo json_encode(["success" => false, "error" => "user_not_found", "message" => "该用户未注册或邮箱输入有误"]);
        exit();
    }

    if (!password_verify($decryptedPassword, $passwordHash)) {
        echo json_encode(["success" => false, "error" => "incorrect_password", "message" => "密码输入错误"]);
        exit();
    }

    // 登录成功，生成 JWT 并返回
    $jwt = generateJWT($email);
    echo json_encode(["success" => true, "message" => "Connexion réussie !", "redirect" => "dashboard.php", "token" => $jwt]);
    exit();
}

// 注册逻辑
if ($action === "register") {
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

    // 获取加密的密码和验证码
    $encryptedPassword = $data['password'];
    $code = $data['code'];

    // 尝试解密密码
    $decryptedPassword = decryptPassword($encryptedPassword);
    if (!$decryptedPassword) {
        echo json_encode(["success" => false, "message" => "Erreur lors du déchiffrement du mot de passe."]);
        exit();
    }

    // 验证验证码
    if (!verifyCode($email, $code)) {
        echo json_encode(["success" => false, "message" => "Code de vérification incorrect ou expiré."]);
        exit();
    }

    // 验证密码复杂性
    if (strlen($decryptedPassword) < 8 || 
        !preg_match('/[A-Z]/', $decryptedPassword) || 
        !preg_match('/[a-z]/', $decryptedPassword) || 
        !preg_match('/\d/', $decryptedPassword) || 
        !preg_match('/[\W_]/', $decryptedPassword)) {
        echo json_encode(["success" => false, "message" => "Le mot de passe doit contenir au moins 8 caractères, avec des majuscules, des minuscules, un chiffre et un caractère spécial."]);
        exit();
    }

    // 生成哈希密码
    $passwordHash = password_hash($decryptedPassword, PASSWORD_BCRYPT); // 加密密码

    // 插入用户数据到数据库
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $passwordHash);

    if ($stmt->execute()) {
        $jwt = generateJWT($email);
        echo json_encode(["success" => true, "message" => "Inscription réussie !"]);
    } else {
        writeDebugLog("SQL Error: " . $stmt->error); // 捕获具体错误并记录日志
        $error = $conn->error;
        if (strpos($error, 'Duplicate entry') !== false) {
            echo json_encode(["success" => false, "message" => "L'adresse e-mail est déjà utilisée."]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de l'inscription."]);
        }
    }

    $stmt->close();
    exit();
}

// 重置密码逻辑
if ($action === "resetPassword") {
    // 验证 JWT
    $decoded = authenticateRequest();
    if ($decoded->sub !== $email) {
        echo json_encode(["success" => false, "message" => "L'email ne correspond pas au jeton JWT."]);
        exit();
    }
    
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $encryptedPassword = $data['password'];
    $code = $data['code'];

    openssl_private_decrypt(base64_decode($encryptedPassword), $decryptedPassword, $privateKey); // 解密密码

    if (!verifyCode($email, $code)) {
        echo json_encode(["success" => false, "message" => "Code de vérification incorrect ou expiré."]);
        exit();
    }

    $passwordHash = password_hash($decryptedPassword, PASSWORD_BCRYPT); // 加密密码

    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $passwordHash, $email);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Mot de passe réinitialisé avec succès !"]);
    } else {
        writeDebugLog("SQL Error: " . $stmt->error); // 捕获具体错误并记录日志
        echo json_encode(["success" => false, "message" => "Erreur lors de la réinitialisation du mot de passe."]);
    }
    $stmt->close();
    exit();
}

$conn->close(); // 关闭数据库连接

?>
