<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;

class Test extends CI_Controller
{
    public function index()
    {
        $mail = new PHPMailer();

        echo "Composer Working";
    }
}