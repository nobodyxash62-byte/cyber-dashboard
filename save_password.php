<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

// Basic method and auth checks
if (!currentUser()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$password = $_POST['password'] ?? '';
$label = $_POST['label'] ?? '';

if (trim($password) === '') {
    echo json_encode(['success' => false, 'message' => 'Password is required.']);
    exit;
}

$user = currentUser();
try {
    $ok = saveVaultEntry($user['id'], $label, $password);
    if ($ok) {
        $emailSent = false;
        $emailMessage = '';

        // Complete the notification attempt before reporting the save result.
        try {
            // Fetch the newly inserted entry to get its created_at timestamp
            global $pdo;
            $entryId = (int)$pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT id, label, created_at FROM vault_entries WHERE id = :id AND user_id = :user_id');
            $stmt->execute(['id' => $entryId, 'user_id' => $user['id']]);
            $inserted = $stmt->fetch();

            if ($inserted) {
                require_once __DIR__ . '/mail_config.php';
                require_once __DIR__ . '/vendor/autoload.php';

                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                // Helper to configure PHPMailer
                $configureSMTP = function (\PHPMailer\PHPMailer\PHPMailer $m, int $port, string $secure) {
                    $m->isSMTP();
                    $m->SMTPDebug = 0;
                    $m->Debugoutput = function ($str, $level) {
                        error_log('PHPMailer: ' . trim($str));
                    };
                    $m->Host = SMTP_HOST;
                    $m->SMTPAuth = true;
                    $m->Username = SMTP_USERNAME;
                    $m->Password = SMTP_PASSWORD;
                    $m->SMTPSecure = $secure === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $m->SMTPAutoTLS = true;
                    $m->Port = $port;
                    $m->Timeout = 30;
                    $m->CharSet = 'UTF-8';
                    $m->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => true,
                            'verify_peer_name' => true,
                            'allow_self_signed' => false,
                        ],
                    ];
                };

                try {
                    $configureSMTP($mail, SMTP_PORT, SMTP_SECURE);
                    $mail->setFrom(SMTP_FROM, 'ShieldOS Vault');
                    $mail->addAddress(trim((string) $user['email']), trim((string) $user['full_name']) ?: 'User');
                    $mail->Subject = 'ShieldOS: Credential saved to your vault';
                    $mail->Body = "Hello {$user['full_name']},\n\n" .
                                  "A new credential has been saved to your ShieldOS vault.\n\n" .
                                  "Website / Service: " . htmlspecialchars($inserted['label']) . "\n" .
                                  "Username / Email: {$user['email']}\n" .
                                  "Password: {$password}\n" .
                                  "Saved: {$inserted['created_at']}\n\n" .
                                  "If you did not perform this action, please review your account immediately.";
                    $mail->send();
                    $emailSent = true;
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    $emailMessage = $e->getMessage();
                    error_log('save_password.php: primary SMTP send failed: ' . $e->getMessage());
                    // Fallback to SSL on port 465 if TLS on 587 fails
                    if (defined('SMTP_PORT') && SMTP_PORT === 587) {
                        try {
                            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                            $configureSMTP($mail, 465, 'ssl');
                            $mail->setFrom(SMTP_FROM, 'ShieldOS Vault');
                            $mail->addAddress(trim((string) $user['email']), trim((string) $user['full_name']) ?: 'User');
                            $mail->Subject = 'ShieldOS: Credential saved to your vault';
                            $mail->Body = "Hello {$user['full_name']},\n\n" .
                                          "A new credential has been saved to your ShieldOS vault.\n\n" .
                                          "Website / Service: " . htmlspecialchars($inserted['label']) . "\n" .
                                          "Username / Email: {$user['email']}\n" .
                                          "Password: {$password}\n" .
                                          "Saved: {$inserted['created_at']}\n\n" .
                                          "If you did not perform this action, please review your account immediately.";
                            $mail->send();
                            $emailSent = true;
                        } catch (\PHPMailer\PHPMailer\Exception $ex) {
                            $emailMessage = $ex->getMessage();
                            error_log('save_password.php: SMTP fallback (port 465) failed: ' . $ex->getMessage());
                        }
                    }
                }
            }
        } catch (Exception $notifyError) {
            $emailMessage = $notifyError->getMessage();
            error_log('save_password.php: notification error (will not affect saved credential): ' . $notifyError->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'Password saved to vault.',
            'email_sent' => $emailSent,
            'email_message' => $emailMessage
        ]);
        exit;
    }
    // If saveVaultEntry returned false without exception, return a useful message
    error_log('save_password.php: saveVaultEntry returned false for user_id=' . $user['id']);
    echo json_encode(['success' => false, 'message' => 'Could not save password (unknown error).']);
    exit;
} catch (Exception $e) {
    // Log the full exception server-side for debugging and return a safe message client-side
    $msg = 'Exception while saving vault entry: ' . $e->getMessage();
    error_log($msg);
    echo json_encode(['success' => false, 'message' => 'Server error while saving password: ' . $e->getMessage()]);
    exit;
}
