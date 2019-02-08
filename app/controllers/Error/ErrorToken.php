<?php
namespace Controllers\Error;
use Controllers\Controller;
use Kernel\Http\Response;

class ErrorToken extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function index(Request $request, Response $response)
    {
        return $response->render("E_A003")->toJson();
    }
}