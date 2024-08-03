<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Composer autoload

$mail = new PHPMailer(true);
$password = 'kidy yydu aiov glil';

try {
    // सर्वर सेटिंग्स
    $mail->isSMTP();                                            // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                                   // Enable SMTP authentication
    $mail->Username = 'amarjeetmistri41';                 // SMTP username
    $mail->Password = $password;                  // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    // प्राप्तकर्ता
    $mail->setFrom('amarjeetmistri41@gmail.com', 'Amarjeet');
    $mail->addAddress('amarjeetmistri42@gmail.com');     // Add a recipient

    // सामग्री
    $mail->isHTML(true);                                        // Set email format to HTML
    $mail->Subject = 'Simple Hello';
    $mail->Body = ' <h1>Welcome, $name</h1>
                                    <p>Congratulations! You have been added as a co-admin. Your login details are:</p>
                                    <p>Email: $email</p>
                                    <p>Password: $password</p>';
    $mail->AltBody = 'Welcome, $name. Congratulations! You have been added as a co-admin. Your login details are: Email: $email, Password: $password'; // Non-HTML mail clients


    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>