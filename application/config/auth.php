<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Authentication Configuration
|--------------------------------------------------------------------------
*/

$config['login_max_attempts'] = 5;
$config['lock_minutes'] = 15;
// $config['session_timeout'] = 1800;
$config['session_timeout'] = 600;
$config['otp_expiry'] = 300;
$config['otp_max_attempts'] = 5;
$config['session_token_length'] = 32;
$config['password_min_length'] = 8;

$config['failed_attempt_reset_minutes'] = 10;