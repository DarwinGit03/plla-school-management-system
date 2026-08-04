<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail_service
{
    protected $mail;

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
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'darwinortozar00@gmail.com';
        $this->mail->Password = 'gmkxxeldocjmwzgs';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = 465;
        $this->mail->setFrom(
            'darwinortozar00@gmail.com',
            'PLLA School Management'
        );
    }

    private function setup()
    {
        // $mail =
        //     new PHPMailer(
        //         true
        //     );

        $mail->isSMTP();

        $mail->Host =
            'smtp.gmail.com';

        $mail->SMTPAuth =
            true;

        $mail->Username =
            'darwinortozar00@gmail.com';

        $mail->Password =
            'gmkxxeldocjmwzgs';

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_SMTPS;

        $mail->Port =
            465;

        $mail->setFrom(
            'darwinortozar00@gmail.com',
            'PLLA School Management'
        );

        return $mail;
    }

    /*
    |--------------------------------------------------------------------------
    | Send Email
    |--------------------------------------------------------------------------
    */

    public function send($email, $subject, $body)
    {
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
            return [
                'status' => false,
                'message' => $this->mail->ErrorInfo
            ];
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