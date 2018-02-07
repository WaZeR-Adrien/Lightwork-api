<?php
namespace Controllers\Error;
use Controllers\Controller;
use Kernel\Tools\Code;
use Kernel\Twig;

class Error404 extends Controller
{
    public static function index()
    {
        self::_render(404, false,"The URI {$_SERVER['REQUEST_URI']} in {$_SERVER['REQUEST_METHOD']} method does not registered");
    }
}