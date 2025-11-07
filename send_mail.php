<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Load PHPMailer files. Use Composer if possible (comment out manual requires).
// require 'vendor/autoload.php';
require 'path/to/PHPMailer/src/Exception.php';
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: /index.html#contact');
    exit;
}

// Collect and Sanitize Form Data
$full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''));
$email_address = filter_var($_POST['email_address'] ?? '', FILTER_SANITIZE_EMAIL);
$message_subject = htmlspecialchars(trim($_POST['message_subject'] ?? ''));
$message_body = htmlspecialchars(trim($_POST['message_body'] ?? ''));

// Basic validation
if (empty($full_name) || !filter_var($email_address, FILTER_VALIDATE_EMAIL) || empty($message_body)) {
    header('Location: /index.html?status=error#contact');
    exit;
}

// Construct the email body
$body = "Name: " . $full_name . "\n";
$body .= "Email: " . $email_address . "\n";
$body .= "Subject: " . $message_subject . "\n\n";
$body .= "Message:\n" . $message_body;

$mail = new PHPMailer(true);

try {
    // 2. 🚨 CRITICAL: SMTP Server Configuration (Replace ALL CAPS values)
    $mail->isSMTP();
    $mail->Host       = 'YOUR_SMTP_HOST';                       // E.g., mail.yourdomain.com, smtp.sendgrid.net
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = 'YOUR_SMTP_USERNAME';                   // Your full email address used for sending
    $mail->Password   = 'YOUR_SMTP_PASSWORD';                   // The password for the sending email account
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Use ENCRYPTION_SMTPS for port 465
    $mail->Port       = 465;                                    // SMTP port (usually 465 or 587)
    
    // 3. 🚨 RECIPIENTS AND SENDER (Replace ALL CAPS values)
    $mail->setFrom('SENDER_EMAIL@example.com', 'RRM Contact Form'); // Must be an email authorized by your Host/Username
    $mail->addAddress('info@rrmlimited.com', 'RRM Limited');    // The recipient (Your business email)
    $mail->addReplyTo($email_address, $full_name);              // Set reply-to to the sender's email

    // 4. Content
    $mail->isHTML(false);                                       // Plain text email
    $mail->Subject = "NEW WEBSITE INQUIRY: " . $message_subject;
    $mail->Body    = $body;
    $mail->AltBody = $body;

    $mail->send();
    // Success: Redirect back to the index with a status parameter
    header('Location: /index.html?status=success#contact');
    exit;
    
} catch (Exception $e) {
    // Error: Redirect back to the index with an error status
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    header('Location: /index.html?status=error#contact');
    exit;
}
?>