<?php
namespace Controllers;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Utils;
use Models\Auth;

class Example extends Controller
{

    /**
     * @name Get slug and id
     * @token
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function index(Request $request, Response $response)
    {
        $lastAuth = Auth::getLast()->toArray();

        $response->getBody()
            ->add($request->getArgs()->get("slug"), "slug")
            ->add($request->getArgs()->get("id"), "id")
            ->add($lastAuth, "auth");

        return $response->toJson();
    }

    /**
     * @name Get all auths
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function getAll(Request $request, Response $response)
    {
        $response->setBody(new \AdrienM\Collection\Collection(
            Auth::getAll( $request->getPage() )->map(function (Auth $auth) {
                return $auth->fetch(true)->toArray();
            })
        ));

        return $response->toJson( $request->getPage() );
    }

    /**
     * @name Edit an auth
     * @token
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function update(Request $request, Response $response)
    {
        $auth = new Auth($request->getArgs()->get("id"));

        $auth->setUser(User::getById( $request->getBody()->get("user_id") ));
        $auth->store();

        return $response->toJson();
    }

    /**
     * @name Add a new auth
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function add(Request $request, Response $response)
    {
        $auth = new Auth();

        $auth->setToken(Utils::createToken());
        $auth->setUser(User::getById( $request->getBody()->get("user_id") ));
        $auth->setDate(time());

        /*
         * If your are too many values which is sent and you do not need treatment, you can use :
         * Utils::setValuesInObject($auth, $request->getBody()->getAll());
         *
         * In this example, this allow to set all values $request->getBody()->getAll() in the $auth object
         */

        $auth->store();

        return $response->toJson();
    }

    /**
     * @name Remove an auth
     * @render json
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function delete(Request $request, Response $response)
    {
        $auth = new Auth($request->getArgs()->get("id"));
        $auth->delete();

        return $response->toJson();
    }
}
