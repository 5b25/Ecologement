<?php
/*
// 输出 OpenSSL 版本信息
echo "OpenSSL 版本: " . OPENSSL_VERSION_TEXT . "<br>";

// 测试随机数生成
$randomBytes = openssl_random_pseudo_bytes(16, $crypto_strong);
if (!$crypto_strong) {
    die("无法生成加密安全的随机数！");
} else {
    echo "随机数生成成功: " . bin2hex($randomBytes) . "<br>";
}

// 测试密钥对生成
$keyConfig = [
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
    "config" => "D:/xampp/apache/conf/openssl.cnf",
];
$privateKeyResource = openssl_pkey_new($keyConfig);

if (!$privateKeyResource) {
    die("无法生成私钥: " . openssl_error_string());
} else {
    echo "RSA 密钥对生成成功！";
}
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './src/Exception.php';
require './src/PHPMailer.php';
require './src/SMTP.php';

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    //服务器配置
    $mail->CharSet ="UTF-8";                     //设定邮件编码
    $mail->SMTPDebug = 0;                        // 调试模式输出
    $mail->isSMTP();                             // 使用SMTP
    $mail->Host = 'smtp.qq.com';                // SMTP服务器
    $mail->SMTPAuth = true;                      // 允许 SMTP 认证
    $mail->Username = '292642120@qq.com';                // SMTP 用户名  即邮箱的用户名
    $mail->Password = 'ltyrlnwcayiubjge';             // SMTP 密码  部分邮箱是授权码(例如163邮箱)
    $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
    $mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

    $mail->setFrom('292642120@qq.com', 'Mailer');  //发件人
    $mail->addAddress('FMT200907@outlook.com', 'Joe');  // 收件人
    //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
    //$mail->addCC('cc@example.com');                    //抄送
    //$mail->addBCC('bcc@example.com');                    //密送

    //发送附件
    // $mail->addAttachment('../xy.zip');         // 添加附件
    // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

    //Content
    $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
    $mail->Subject = '这里是邮件标题' . time();
    $mail->Body    = '<h1>这里是邮件内容</h1>' . date('Y-m-d H:i:s');
    $mail->AltBody = '如果邮件客户端不支持HTML则显示此内容';

    $mail->send();
    echo '邮件发送成功';
} catch (Exception $e) {
    echo '邮件发送失败: ', $mail->ErrorInfo;
}