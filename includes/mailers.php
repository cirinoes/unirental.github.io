<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function send_mail(string $to, string $subject, string $body): bool {
  if (!SMTP_HOST || !SMTP_USER || !SMTP_PASS) return false;

  // Expect PHPMailer library at /public/lib/PHPMailer
  $base = __DIR__ . '/../htdocs/lib/PHPMailer/src/';
  require_once $base . 'PHPMailer.php';
  require_once $base . 'SMTP.php';
  require_once $base . 'Exception.php';

  $mail = new PHPMailer\PHPMailer\PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom(SMTP_FROM, APP_NAME);
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
    return true;
  } catch (Throwable $e) {
    return false;
  }
}