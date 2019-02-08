<?php
namespace Models;
use Kernel\Database;
use Kernel\Tools\Utils;

class User extends Database
{
    protected static $_table = 'user';

    /**
     * Check if email and password correspond
     * @param $email
     * @param $password
     * @return array|bool
     */
    public static function check($email, $password)
    {
        $user = self::findFirst(['email' => $email]);

        if (password_verify($password, $user->password)) {
            return $user;
        }
        return false;
    }
}