<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!currentUser()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$entryId = (int)($_POST['entry_id'] ?? 0);
if ($entryId <= 0) {
    header('Location: index.php');
    exit;
}

$user = currentUser();
$entry = null;
foreach (getVaultEntries($user['id']) as $row) {
    if ((int)$row['id'] === $entryId) {
        $entry = $row;
        break;
    }
}

if (!$entry) {
    header('Location: index.php');
    exit;
}

$smtpUsername = trim(SMTP_USERNAME);
$smtpFrom = trim(SMTP_FROM);
$smtpPassword = trim(SMTP_PASSWORD);

if (!filter_var($smtpUsername, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['email_status'] = 'placeholder';
    $_SESSION['email_status_detail'] = 'SMTP_USERNAME is not a valid Gmail address.';
    header('Location: index.php');
    exit;
}

if (!filter_var($smtpFrom, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['email_status'] = 'placeholder';
    $_SESSION['email_status_detail'] = 'SMTP_FROM is not a valid Gmail address.';
    header('Location: index.php');
    exit;
}

if ($smtpPassword === '' || strlen($smtpPassword) !== 16) {
    $_SESSION['email_status'] = 'placeholder';
    $_SESSION['email_status_detail'] = 'SMTP_PASSWORD must be a 16-character Gmail App Password.';
    header('Location: index.php');
    exit;
}

$mail = new PHPMailer(true);

try {
        setupSmtpMailer($mail, SMTP_PORT, SMTP_SECURE);
        $mail->setFrom(SMTP_FROM, 'ShieldOS Vault');
        $mail->addAddress($user['email'], $user['full_name']);
        $mail->Subject = 'Your saved password from ShieldOS';
        $mail->Body = "Hello {$user['full_name']},\n\n" .
                      "Here is your saved password:\n\n" .
                      "Website / App: {$entry['label']}\n" .
                      "Username / Email: {$user['email']}\n" .
                      "Password: {$entry['password']}\n\n" .
                      "Saved time: {$entry['created_at']}\n\n" .
                      "This message was sent from your ShieldOS Password Vault.";

        $mail->send();
        $_SESSION['email_status'] = 'sent';
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        error_log('Email send failed: ' . $e->getMessage());

        // If connection to Gmail on port 587 fails, retry with SSL on port 465.
        $fallbackUsed = false;
        $message = $e->getMessage();

        if (SMTP_PORT === 587 && stripos($message, 'Failed to connect to server') !== false) {
            try {
                $mail = new PHPMailer(true);
                setupSmtpMailer($mail, 465, 'ssl');
                $mail->setFrom(SMTP_FROM, 'ShieldOS Vault');
                $mail->addAddress($user['email'], $user['full_name']);
                $mail->Subject = 'Your saved password from ShieldOS';
                $mail->Body = "Hello {$user['full_name']},\n\n" .
                              "Here is your saved password:\n\n" .
                              "Website / App: {$entry['label']}\n" .
                              "Username / Email: {$user['email']}\n" .
                              "Password: {$entry['password']}\n\n" .
                              "Saved time: {$entry['created_at']}\n\n" .
                              "This message was sent from your ShieldOS Password Vault.";
                $mail->send();
                $_SESSION['email_status'] = 'sent';
                header('Location: index.php');
                exit;
            } catch (Exception $fallbackException) {
                $fallbackUsed = true;
                error_log('SMTP fallback failed: ' . $fallbackException->getMessage());
                $message = $fallbackException->getMessage();
            }
        }

        $_SESSION['email_status'] = 'smtp';
        $_SESSION['email_status_detail'] = $message . ($fallbackUsed ? ' (fallback attempted)' : '');
        header('Location: index.php');
        exit;
    }

function setupSmtpMailer(PHPMailer $mail, int $port, string $secureMode): void
{
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Debugoutput = function ($str, $level) {
        error_log('PHPMailer: ' . trim($str));
    };
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = $secureMode === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAutoTLS = true;
    $mail->Port = $port;
    $mail->Timeout = 30;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ];
}
