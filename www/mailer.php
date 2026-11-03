<?php
// 1. Manually include the core source classes downloaded from GitHub
require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendSystemMail($toEmail, $toName, $subject, $htmlBody) {
    // 2. Parse the .env variables mapped into the Docker environment
    $smtpHost = getenv('SMTP_HOST') ?: 'sandbox.smtp.mailtrap.io';
    $smtpPort = getenv('SMTP_PORT') ?: 2525;
    $smtpUser = getenv('SMTP_USER') ?: '';
    $smtpPass = getenv('SMTP_PASSWORD') ?: '';

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->Port = $smtpPort;

        // Recipients
        $mail->setFrom('noreply@dockerlab.local', 'Docker Lab Infrastructure');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log errors internally if the network handshake breaks
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>