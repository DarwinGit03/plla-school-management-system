<?php

class Password_validator
{
    public function validate(
        $password
    )
    {
        $errors = [];

        if(
            strlen(
                $password
            ) < 8
        )
        {
            $errors[] =
                'Minimum 8 characters';
        }

        if(
            !preg_match(
                '/[A-Z]/',
                $password
            )
        )
        {
            $errors[] =
                'Uppercase required';
        }

        if(
            !preg_match(
                '/[a-z]/',
                $password
            )
        )
        {
            $errors[] =
                'Lowercase required';
        }

        if(
            !preg_match(
                '/[0-9]/',
                $password
            )
        )
        {
            $errors[] =
                'Number required';
        }

        if(
            !preg_match(
                '/[\W_]/',
                $password
            )
        )
        {
            $errors[] =
                'Special character required';
        }

        return $errors;
    }
}