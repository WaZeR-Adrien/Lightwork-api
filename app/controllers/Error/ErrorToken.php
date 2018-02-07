<?php
namespace Controllers\Error;
use Controllers\Controller;

class ErrorToken extends Controller
{
    public static function index()
    {
        self::_render(101, false, 'You need a valid authentication to API access');
    }
}