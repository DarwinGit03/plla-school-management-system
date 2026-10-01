<?php

class Password_validator
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('auth');
    }

    public function validate($password)
    {
        $errors = [];
        $password = (string) $password;
        $minimum = (int) $this->CI->config->item('password_min_length');
        $maximum = (int) $this->CI->config->item('password_max_length');

        if (strlen($password) < $minimum) {
            $errors[] = 'at least ' . $minimum . ' characters';
        }
        if (strlen($password) > $maximum) {
            $errors[] = 'no more than ' . $maximum . ' characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'one number';
        }
        if (!preg_match('/[^A-Za-z0-9\s]/', $password)) {
            $errors[] = 'one special character';
        }

        return $errors;
    }

    public function error_message(array $errors)
    {
        return 'Password must contain ' . implode(', ', $errors) . '.';
    }
}
