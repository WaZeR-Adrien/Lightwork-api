<?php
namespace Controllers\Error;
use Controllers\Controller;
use Kernel\Http\Request;
use Kernel\Http\Response;
use Kernel\Tools\Code;
use Kernel\Twig;

class Error404 extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function index(Request $request, Response $response)
    {
        return $response->fromApi("E_A001")->toJson();
    }
}
