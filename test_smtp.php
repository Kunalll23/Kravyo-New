<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/smtp.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    // Redirect debug output to a variable/stdout instead of echo so we can capture it cleanly
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG [$level]: $str\n";
    };

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    
    // Masking username and password in output just in case
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = str_replace(' ', '', SMTP_PASSWORD);
    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress('test@example.com', 'Test User');

    $mail->Subject = 'Diagnostic Test';
    $mail->Body    = 'This is a test';

    echo "Attempting to send email...\n";
    $mail->send();
    echo "Message has been sent successfully\n";

} catch (PHPMailerException $e) {
    echo "Message could not be sent. Mailer Error: {$e->getMessage()}\n";
}
