<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/SMTP.php';

// default response is an error, set to other codes later
http_response_code(500);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

function send_with_gmail($subject, $htmlfile, $from, $to, $cc, $bcc, $username, $password) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Sender
    $parsedFrom = PHPMailer::parseAddresses($from)[0];
    $mail->setFrom($parsedFrom['address'], $parsedFrom['name']);

    // Recipients
    $to = str_replace("\n", ',', $to);
    foreach (PHPMailer::parseAddresses($to) as $recipient) {
        $mail->addAddress($recipient['address'], $recipient['name']);
    }
    $cc = str_replace("\n", ',', $cc);
    foreach (PHPMailer::parseAddresses($cc) as $recipient) {
        $mail->addCC($recipient['address'], $recipient['name']);
    }
    $bcc = str_replace("\n", ',', $bcc);
    foreach (PHPMailer::parseAddresses($bcc) as $recipient) {
        $mail->addBCC($recipient['address'], $recipient['name']);
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = file_get_contents($htmlfile);

    $mail->send();
    return $mail;
}

try {
    // Check required fields
    if (!isset($_POST['subject'], $_POST['from'], $_POST['username'], $_POST['password'])) {
        http_response_code(400);
        throw new Exception('Missing required fields');
    }

    // Check $_FILES['image']['error'] value.
    switch ($_FILES['htmlfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            http_response_code(400);
            throw new Exception('Missing required field: Template');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            http_response_code(400);
            throw new Exception('Template exceeded filesize limit: ' . ini_get("upload_max_filesize"));
        default:
            throw new Exception('Unknown template upload error.');
    }

    // Set variables
    $subject = $_POST['subject'];
    $htmlfile = $_FILES['htmlfile']['tmp_name'];
    $from = $_POST['from'];
    $to = $_POST['to'];
    $cc = $_POST['cc'];
    $bcc = $_POST['bcc'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Send email
    try {
        $result = send_with_gmail($subject, $htmlfile, $from, $to, $cc, $bcc, $username, $password);
    } catch (Exception $e) {
        http_response_code(400);
        throw $e;
    }
    $success = 'Email sent to ' . count($result->getAllRecipientAddresses()) . ' recipients. ' .
        'To: ' . count($result->getToAddresses()) . ', ' .
        'Cc: ' . count($result->getCcAddresses()) . ', ' .
        'Bcc: ' . count($result->getBccAddresses());

    http_response_code(200);
    echo $success;
} catch (Exception $e) {
    echo '<details><summary>' . $e->getMessage() . '</summary><pre>' . htmlspecialchars($e) . '</pre></details>';
}
