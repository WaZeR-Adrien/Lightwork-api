<?php
namespace Controllers;

use Models\User;

class Auth extends Controller
{
    /**
     * Login and get token if data matches with User
     */
    public static function check()
    {
        if (User::check($_POST['email'], $_POST['password'])) {
            // create token here
            $token = self::_createToken();
            $auth = new \Models\Auth();
            $auth->token = $token;
            $auth->date  = time();
            $auth->insert();

            self::_render(100, true, 'Successful authentication');
        }

        self::_render(101, false, 'Incorrect connection information');
    }
}