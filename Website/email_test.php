<?php

require 'vendor/autoload.php'; // 引入 PHPMailer 的自动加载文件
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 写日志函数
function writeDebugLog($message) {
    $timestamp = date('[Y-m-d H:i:s] ');
    $logFilePath = __DIR__ . '/logs/email_task_debug.log';
    file_put_contents($logFilePath, $timestamp . $message . PHP_EOL, FILE_APPEND);
}

// 清空任务列表函数
function clearTaskQueue() {
    $taskQueueFile = __DIR__ . '/email_task_queue.json';
    file_put_contents($taskQueueFile, json_encode([], JSON_PRETTY_PRINT)); // 将任务列表清空
    writeDebugLog("Task queue has been cleared.");
}

// 发送邮件任务的函数
function sendVerificationCodeTask($email, $code) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 4; // 启用最详细的调试信息
        $mail->Debugoutput = function($str, $level) {
            writeDebugLog("SMTP Debug Level $level: $str");
        };
        
        $mail->CharSet ="UTF-8";                     //设定邮件编码
        $mail->isSMTP();
        $mail->Host = 'smtp.qq.com';
        $mail->SMTPAuth = true;
        $mail->Username = '@qq.com'; // 替换为您的 QQ 邮箱地址
        $mail->Password = ''; // 替换为您的 SMTP 授权码
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 使用 SSL 加密
        $mail->Port = 465;
        $mail->Timeout = 30; // 设置超时时间

        $mail->setFrom('@qq.com', 'Ecologement'); // 替换为发件人邮箱和名称
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Test de code de vérification';
        $mail->Body = "Bonjour,<br><br>Votre code de vérification est : <strong>$code</strong><br>Ce code est valide pour 6 minutes.";

        $mail->send();
        writeDebugLog("Mail sent successfully to $email.");
        return true;
    } catch (Exception $e) {
        writeDebugLog("Mail sending failed to $email: " . $mail->ErrorInfo);
        return false;
    }
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
            writeDebugLog("Task for email $email already exists in the queue.");
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
        $success = sendVerificationCodeTask($task['email'], $task['code']);
        if ($success) {
            writeDebugLog("Task for {$task['email']} completed successfully.");
        } else {
            writeDebugLog("Task for {$task['email']} failed.");
            $remainingTasks[] = $task;
        }
    }

    file_put_contents($taskQueueFile, json_encode($remainingTasks, JSON_PRETTY_PRINT));


    // 在所有任务处理完成后，清空任务队列（无论成功还是失败）
    clearTaskQueue();
}

// 模拟浏览器刷新触发任务
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $testEmail = ''; // 替换为测试邮箱
    addTaskToQueue($testEmail);
    processTaskQueue();
}

echo "Email task processing completed. Check logs for details.";

?>
