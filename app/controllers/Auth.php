<?php
namespace Controllers;

use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Utils;
use Models\User;

class Auth extends Controller
{
    /**
     * Login and get token if data matches with User
     * Example of Auth
     * You can edit this to match with your own database
     * @param $post
     */
    public static function check(Request $request, Response $response)
    {
        $user = User::check($request->getBodies()->email, $request->getBodies()->password);
        if ($user) {
            // create token here
            $token = Utils::createToken();
            $auth = new \Models\Auth();
            $auth->user_id = $user->id;
            $auth->token   = $token;
            $auth->date    = time();
            $auth->store();

            $response->setData(["token" => $token]);

            return $response->render("S_PO001")->toJson();
        }

        return $response->render("E_A004")->toJson();
    }
}