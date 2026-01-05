<?php

// Make sure these paths are correct for your folder structure
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    // $mail->SMTPDebug = 2; // UNCOMMENT THIS LINE IF EMAILS ARE NOT SENDING TO SEE ERRORS
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'sonasharma42003@gmail.com';
    $mail->Password   = 'jcyr spis albp vows'; // REPLACE THIS WITH YOUR NEW PASSWORD
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Better than hardcoding 'tls'
    $mail->Port       = 587;

    $mail->setFrom('sonasharma42003@gmail.com');
    $mail->addAddress('vamsinammi143@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
