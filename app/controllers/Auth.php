<?php
namespace Controllers;

use Kernel\Api\ApiErrorCode;
use Kernel\Api\ApiError;
use Kernel\Api\ApiException;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Utils;
use Models\Dao\UserDAO;
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
        $user = UserDAO::check($request->getBody()->get("email"), $request->getBody()->get("password"));
        if ($user) {
            // create token here
            $token = Utils::createToken();
            $auth = new \Models\Auth();
            $auth->setUser($user);
            $auth->setToken($token);
            $auth->setDate(time());
            $auth->store();

            $response->getBody()
                ->add($token, "token");

            return $response->toJson();
        }

        throw new ApiException(ApiErrorCode::A001);
    }
}
