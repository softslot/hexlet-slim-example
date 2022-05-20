<?php

namespace App;

class UserValidator
{
    public static function validate($user)
    {
        $errors = [];
        if (empty($user['nickname'])) {
            $errors['nickname'] = 'The "nickname" field cannot be empty';
        }
        if (empty($user['email'])) {
            $errors['email'] = 'The "email" field cannot be empty';
        }

        return $errors;
    }
}
