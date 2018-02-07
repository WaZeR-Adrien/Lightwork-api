<?php
namespace Controllers;

use Models\User;

class Auth extends Controller
{
    /**
     * Login and get token if data matches with User
     * Example of Auth
     * You can edit this to match with your own database
     */
    public static function check()
    {
        $user = User::check($_POST['email'], $_POST['password']);
        if ($user) {
            // create token here
            $token = self::_createToken();
            $auth = new \Models\Auth();
            $auth->user_id = $user->id;
            $auth->token   = $token;
            $auth->date    = time();
            $auth->insert();

            self::_render(100, true, 'Successful authentication', ['token' => $token]);
        }

        self::_render(101, false, 'Incorrect connection information');
    }
}