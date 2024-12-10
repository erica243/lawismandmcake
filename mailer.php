<?php

use PHPMailer\PHPMailer\PHPMailer;


require __DIR__ . "/vendor/autoload.php";

$mail = new PHPMailer(true);

// $mail->SMTPDebug = SMTP::DEBUG_SERVER;

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = "smtp.gmail.com";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->Username = 'johnchristianfariola@gmail.com';// SMTP username
$mail->Password = 'ptot fzif iiwm xbqf';// SMTP password

$mail->isHtml(true);

return $mail;