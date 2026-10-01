<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail_service
{
    protected $mail;
    protected $configured = false;
    protected $configuration_error = 'SMTP email is not configured. Copy .env.example to .env and add your provider SMTP settings.';
    private $environment = null;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        $this->configure();
    }

    /*
    |--------------------------------------------------------------------------
    | Create PHPMailer Instance
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SMTP Configuration
    |--------------------------------------------------------------------------
    */

    private function configure()
    {
        $host = trim((string) $this->setting('SMTP_HOST'));
        $username = trim((string) $this->setting('SMTP_USERNAME'));
        $password = (string) $this->setting('SMTP_PASSWORD');
        $fromEmail = trim((string) $this->setting('SMTP_FROM_EMAIL'));
        $fromName = trim((string) $this->setting('SMTP_FROM_NAME', 'PLLA School Management'));
        $encryption = strtolower(trim((string) $this->setting('SMTP_ENCRYPTION', 'ssl')));
        $port = (int) $this->setting('SMTP_PORT', $encryption === 'tls' ? '587' : '465');

        if ($host === '' || $username === '' || trim($password) === '' || $fromEmail === '') {
            return;
        }
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL) || !in_array($encryption, ['ssl', 'tls'], true) || $port < 1 || $port > 65535) {
            $this->configuration_error = 'SMTP settings are invalid. Check SMTP_FROM_EMAIL, SMTP_ENCRYPTION, and SMTP_PORT in .env.';
            return;
        }

        $this->mail->isSMTP();
        $this->mail->Host = $host;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $username;
        $this->mail->Password = $password;
        $this->mail->SMTPSecure = $encryption === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = $port;
        try {
            $this->mail->setFrom($fromEmail, $fromName !== '' ? $fromName : 'PLLA School Management');
            $this->configured = true;
            $this->configuration_error = '';
        } catch (Exception $e) {
            $this->configuration_error = 'SMTP sender settings are invalid. Check SMTP_FROM_EMAIL.';
            log_message('error', 'SMTP sender configuration rejected: ' . $e->getMessage());
        }
    }

    private function setting($key, $default = '')
    {
        $value = getenv($key);
        if ($value !== false && trim((string) $value) !== '') return $value;

        if ($this->environment === null) {
            $path = FCPATH . '.env';
            $parsed = is_file($path) ? @parse_ini_file($path, false, INI_SCANNER_RAW) : false;
            $this->environment = is_array($parsed) ? $parsed : [];
        }

        return array_key_exists($key, $this->environment) ? $this->environment[$key] : $default;
    }

    private function unavailableMessage()
    {
        return $this->configuration_error !== ''
            ? $this->configuration_error
            : 'SMTP email is not available. Check the SMTP server settings and credentials.';
    }

    /*
    |--------------------------------------------------------------------------
    | Send Email
    |--------------------------------------------------------------------------
    */

    public function send($email, $subject, $body)
    {
        if (!$this->configured) {
            return ['status' => false, 'message' => $this->unavailableMessage()];
        }

        try
        {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
            return [
                'status' => true
            ];
        }
        catch(Exception $e)
        {
            log_message('error', 'SMTP delivery failed: ' . $this->mail->ErrorInfo);
            return ['status' => false, 'message' => 'Email delivery failed. Please try again later.'];
        }
    }

    /** Send a message with an in-memory attachment such as a generated PDF. */
    public function sendWithAttachment($email, $subject, $body, $attachment, $filename, $mime_type = 'application/pdf', array $cc = [])
    {
        if (!$this->configured) {
            return ['status' => false, 'message' => $this->unavailableMessage()];
        }

        try {
            $this->mail->clearAddresses();
            $this->mail->clearCCs();
            $this->mail->clearAttachments();
            $this->mail->addAddress($email);
            foreach ($cc as $copyAddress) {
                if (filter_var($copyAddress, FILTER_VALIDATE_EMAIL)) $this->mail->addCC($copyAddress);
            }
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->AltBody = trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], "\n", $body)));
            $this->mail->addStringAttachment($attachment, $filename, 'base64', $mime_type);
            $this->mail->send();
            $this->mail->clearAttachments();
            return ['status' => true];
        } catch (Exception $e) {
            $this->mail->clearAttachments();
            log_message('error', 'SMTP attachment delivery failed: ' . $this->mail->ErrorInfo);
            return ['status' => false, 'message' => 'Email delivery failed. Please try again later.'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send OTP
    |--------------------------------------------------------------------------
    */

    public function sendOTP(
        $email,
        $otp
    )
    {
        $subject =
            'Password Reset OTP';

        $body =

        "
        <h2>Password Reset</h2>

        <p>Your verification code is</p>

        <h1 style='color:#0d6efd;'>

            {$otp}

        </h1>

        <p>

            This OTP expires in
            <strong>5 minutes.</strong>

        </p>
        ";

        return $this->send(

            $email,

            $subject,

            $body

        );
    }
}
