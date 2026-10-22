<?php
declare(strict_types=1);
namespace App\Core;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Outgoing email.
 *
 * Wraps PHPMailer so the rest of the application never has to care which
 * transport is in use. Delivery failures are captured rather than thrown, so
 * a mail server outage can never break a request.
 */
class Mailer {
    private string $transport;
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromAddress;
    private string $fromName;

    /** @var string[] Problems from the last send attempt. */
    private array $errors = [];

    public function __construct() {
        $this->transport    = strtolower((string) env('MAIL_TRANSPORT', 'smtp'));
        $this->host         = (string) env('MAIL_HOST', 'localhost');
        $this->port         = (int) env('MAIL_PORT', 587);
        $this->username     = (string) env('MAIL_USERNAME', '');
        $this->password     = (string) env('MAIL_PASSWORD', '');
        $this->encryption   = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));
        $this->fromAddress  = (string) env('MAIL_FROM_ADDRESS', 'no-reply@' . $this->defaultHost());
        $this->fromName     = (string) env('MAIL_FROM_NAME', app_name());
    }

    /** The host used for the From address when none is configured. */
    private function defaultHost(): string {
        return parse_url(app_url(), PHP_URL_HOST) ?: 'localhost';
    }

    /** @return string[] */
    public function errors(): array {
        return $this->errors;
    }

    /**
     * Send a message.
     *
     * @param string $to       Recipient address
     * @param string $subject  Subject line
     * @param string $html     HTML body
     * @param string $text     Optional plain-text alternative
     */
    public function send(string $to, string $subject, string $html, string $text = ''): bool {
        $this->errors = [];

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'The recipient address is not valid.';
            return false;
        }

        if ($this->transport === 'log') {
            return $this->writeToLog($to, $subject, $text !== '' ? $text : strip_tags($html));
        }

        /* Without PHPMailer (composer install not run) fall back to PHP mail(). */
        if (!class_exists(PHPMailer::class)) {
            $this->errors[] = 'PHPMailer is not installed - run "composer install".';
            error_log('Mail delivery failed: ' . $this->errors[0]);
            return $this->sendWithPhpMail($to, $subject, $html, $text);
        }

        try {
            $mail = new PHPMailer(true); // exceptions on
            $mail->CharSet = 'UTF-8';

            if ($this->transport === 'smtp') {
                $mail->isSMTP();
                $mail->Host = $this->host;
                $mail->Port = $this->port;
                if ($this->username !== '') {
                    $mail->SMTPAuth = true;
                    $mail->Username = $this->username;
                    $mail->Password = $this->password;
                }
                if ($this->encryption === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($this->encryption === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPSecure = '';
                }
            } else {
                /* Fall back to PHP's own mail() transport. */
                $mail->isMail();
            }

            $mail->setFrom($this->fromAddress, $this->fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = $text !== '' ? $text : trim(strip_tags($html));

            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            error_log('Mail delivery failed: ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            error_log('Mail delivery failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send the password reset link.
     */
    public function sendPasswordReset(string $to, string $link, int $validMinutes = 60): bool {
        $appName = app_name();
        $subject = 'Reset your ' . $appName . ' password';

        $html = $this->layout(
            'Reset your password',
            '<p>Hello,</p>'
            . '<p>We received a request to reset the password for your <strong>' . e($appName) . '</strong> account.</p>'
            . '<p><a href="' . e($link) . '" class="sm-btn">Choose a new password</a></p>'
            . '<p>Or paste this link into your browser:</p>'
            . '<p style="word-break:break-all;color:#64748b;font-size:13px">' . e($link) . '</p>'
            . '<p style="color:#64748b;font-size:13px">This link is valid for ' . $validMinutes . ' minutes and can only be used once.</p>'
            . '<p style="color:#64748b;font-size:13px">If you did not request a password reset, you can safely ignore this email &mdash; your password will not change.</p>'
        );

        $text = "We received a request to reset the password for your {$appName} account.\n\n"
              . "Choose a new password: {$link}\n\n"
              . "This link is valid for {$validMinutes} minutes and can only be used once.\n"
              . "If you did not request a password reset, ignore this email.\n";

        return $this->send($to, $subject, $html, $text);
    }

    /** Write the message to the mail log instead of sending it. */
    private function writeToLog(string $to, string $subject, string $body): bool {
        $dir = APP_ROOT . '/storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0770, true);
        @file_put_contents(
            $dir . '/mail.log',
            sprintf("%s  TO: %s  SUBJECT: %s%s%s", date('Y-m-d H:i:s'), $to, $subject, PHP_EOL, $body) . PHP_EOL . PHP_EOL,
            FILE_APPEND
        );
        return true;
    }

    /** Last-resort delivery using PHP's own mail() function. */
    private function sendWithPhpMail(string $to, string $subject, string $html, string $text): bool {
        $headers = 'From: ' . $this->fromName . ' <' . $this->fromAddress . '>'
                 . "\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8";
        $sent = @mail($to, $subject, $html, $headers);
        if (!$sent) $this->errors[] = 'PHP mail() could not hand the message to a mail server.';
        return (bool) $sent;
    }

    /** A simple branded HTML wrapper. */
    private function layout(string $title, string $body): string {
        $appName = e(app_name());
        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . e($title) . '</title></head>'
            . '<body style="margin:0;padding:32px 16px;background:#f4f6fc;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#0f172a">'
            . '<div style="max-width:520px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px">'
            . '<div style="font-weight:800;font-size:18px;margin-bottom:16px">✦ ' . $appName . '</div>'
            . '<h1 style="font-size:20px;margin:0 0 16px">' . e($title) . '</h1>'
            . '<div style="font-size:15px;line-height:1.6;color:#334155">' . $body . '</div>'
            . '<div style="margin-top:28px;padding-top:16px;border-top:1px solid #e2e8f0;color:#94a3b8;font-size:12px">'
            . 'Sent by ' . $appName . '. If you did not expect this email you can safely ignore it.'
            . '</div></div></body></html>';
    }
}
